<?php

namespace App\Http\Controllers;

use App\Models\CashAdvance;
use App\Models\CashAdvanceInstallment;
use App\Models\CashAdvancePlan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CashAdvanceController extends Controller
{
    public function __construct()
    {
        // Middleware: Leader tidak boleh akses sama sekali
        $this->middleware(function ($request, $next) {
            if (auth()->user()->role === 'leader') {
                abort(403, 'Akses ditolak. Leader tidak memiliki akses ke menu ini.');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = CashAdvance::with(['user', 'installments']);

        // --- LOGIC VIEW DATA ---
        if (in_array($user->role, ['admin', 'admin_gaji'])) {
            // Admin & Admin Gaji: LIHAT SEMUA DATA (Global)
            // Tidak ada filter where
        } elseif ($user->role === 'audit') {
            // Audit: Hanya lihat cabang yang dipegang
            $branchIds = $user->branches->pluck('id');
            $query->where(function($q) use ($branchIds, $user) {
                $q->whereHas('user', function ($subQ) use ($branchIds) {
                    $subQ->whereIn('branch_id', $branchIds);
                })
                ->orWhere('user_id', $user->id); // Dan punya sendiri
            });
        } else {
            // User Biasa / Security: Hanya lihat punya sendiri
            $query->where('user_id', $user->id);
        }

        // Pisahkan data Aktif dan History
        $activeLoans = (clone $query)->where(function($q) {
            $q->where('status', 'pending')
              ->orWhere(function($sub) {
                  $sub->where('status', 'approved')->whereColumn('total_paid', '<', 'amount');
              });
        })->latest()->get();

        $historyLoans = (clone $query)->where(function($q) {
             $q->where('status', 'rejected')
               ->orWhere(function($sub) {
                   $sub->where('status', 'paid');
               });
        })->latest()->get();

        return view('kasbon.index', compact('activeLoans', 'historyLoans'));
    }

    public function create()
    {
        $user = auth()->user();

        // 1. CEK OVERDUE (Blokir User Biasa jika nunggak)
        // Admin & Admin Gaji BYPASS pengecekan ini agar tetap bisa input buat orang lain
        if (!in_array($user->role, ['admin', 'admin_gaji'])) {
            $overdueCount = CashAdvance::where('user_id', $user->id)
                ->where('status', 'approved')
                ->where('total_paid', '<', DB::raw('amount'))
                ->whereDate('due_date', '<', now())
                ->count();

            if ($overdueCount > 0) {
                return redirect()->route('kasbon.index')
                    ->with('error', 'ANDA DIBLOKIR: Anda memiliki tagihan yang LEWAT JATUH TEMPO. Lunasi dulu sebelum mengajukan baru.');
            }
        }

        // 2. LIST USER UNTUK DROPDOWN
        $users = collect();
        
        if (in_array($user->role, ['admin', 'admin_gaji'])) {
            // Admin & Admin Gaji: Bisa pilih SEMUA orang
            $users = User::orderBy('name', 'asc')->get();
        } elseif ($user->role === 'audit') {
            // Audit: Cabang pegangan + diri sendiri
            $branchIds = $user->branches->pluck('id');
            $users = User::whereIn('branch_id', $branchIds)->orWhere('id', $user->id)->orderBy('name')->get();
        } else {
            // User biasa: Hanya dirinya sendiri
            $users = collect([$user]);
        }

        return view('kasbon.create', compact('users'));
    }

    public function store(Request $request)
    {
        // 1. BERSIHKAN FORMAT RUPIAH (Hilangkan titik)
        if ($request->has('amount')) {
            $request->merge(['amount' => str_replace('.', '', $request->amount)]);
        }

        // 2. FORCE USER ID UNTUK NON-ADMIN
        // Jika bukan admin/admin_gaji, paksa user_id jadi id login (mencegah inspect element)
        if (!in_array(auth()->user()->role, ['admin', 'admin_gaji'])) {
            $request->merge(['user_id' => auth()->id()]);
        }

        // 3. VALIDASI
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:100',
            'amount' => 'required|numeric|min:1000|max:1000000000', // Max 1 Milyar
            'tenor' => 'required|integer|min:1|max:24',
            'start_date' => 'required|date', // Admin boleh input tanggal mundur/maju bebas
            'payment_method' => 'required|in:cash,transfer',
            'payment_details' => 'required_if:payment_method,transfer',
            'description_1' => 'required|string',
            'photo_1' => 'required|image|max:2048', 
            'photo_2' => 'nullable|image|max:2048', 
        ]);

        DB::transaction(function() use ($request) {
            $data = $request->except(['photo_1', 'photo_2', 'start_date']);
            
            $data['created_by'] = auth()->id();
            $data['status'] = 'pending';
            $data['total_paid'] = 0;
            
            // Hitung Due Date Final
            $data['due_date'] = Carbon::parse($request->start_date)->addMonths($request->tenor - 1);

            // Upload Foto
            if ($request->hasFile('photo_1')) {
                $data['photo_1'] = $request->file('photo_1')->store('kasbon', 'public');
            }
            if ($request->hasFile('photo_2')) {
                $data['photo_2'] = $request->file('photo_2')->store('kasbon', 'public');
            }

            // Jika yang input Admin/Admin Gaji, status langsung Approved?
            // Opsional: Untuk saat ini biarkan pending agar bisa dicek ulang, atau ubah logic di sini jika mau auto-approve.
            // $data['status'] = in_array(auth()->user()->role, ['admin', 'admin_gaji']) ? 'approved' : 'pending';

            // Simpan Kasbon Utama
            $kasbon = CashAdvance::create($data);

            // Generate Rencana Cicilan (Plans)
            $amountPerMonth = $kasbon->amount / $kasbon->tenor;
            
            for ($i = 0; $i < $kasbon->tenor; $i++) {
                CashAdvancePlan::create([
                    'cash_advance_id' => $kasbon->id,
                    'installment_order' => $i + 1,
                    'due_date' => Carbon::parse($request->start_date)->addMonths($i),
                    'amount' => $amountPerMonth,
                    'is_paid' => false
                ]);
            }
        });

        return redirect()->route('kasbon.index')->with('success', 'Pengajuan kasbon berhasil dibuat.');
    }

    public function show($id)
    {
        $kasbon = CashAdvance::with(['user', 'installments', 'plans'])->findOrFail($id);
        
        // Cek Hak Akses Show
        $user = auth()->user();
        $isAuthorized = false;

        if (in_array($user->role, ['admin', 'admin_gaji'])) {
            $isAuthorized = true; // Admin & Admin Gaji bebas liat
        } elseif ($user->role === 'audit') {
            // Audit cek cabang
            if ($user->branches->contains('id', $kasbon->user->branch_id) || $kasbon->user_id == $user->id) {
                $isAuthorized = true;
            }
        } elseif ($kasbon->user_id == $user->id) {
            $isAuthorized = true; // User ybs
        }

        if (!$isAuthorized) abort(403);

        return view('kasbon.show', compact('kasbon'));
    }

    // --- FORM EDIT KASBON (Untuk Admin / Admin Gaji) ---
    public function edit($id)
    {
        if (!in_array(auth()->user()->role, ['admin', 'admin_gaji'])) abort(403);
        
        $kasbon = CashAdvance::with('user')->findOrFail($id);
        return view('kasbon.edit', compact('kasbon'));
    }

    public function update(Request $request, $id)
    {
        if (!in_array(auth()->user()->role, ['admin', 'admin_gaji'])) abort(403);

        $kasbon = CashAdvance::findOrFail($id);

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'description_1' => 'required|string',
            'photo_1' => 'nullable|image|max:2048',
            'photo_2' => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['amount', 'description_1', 'description_2']);

        if ($request->hasFile('photo_1')) {
            if($kasbon->photo_1) Storage::disk('public')->delete($kasbon->photo_1);
            $data['photo_1'] = $request->file('photo_1')->store('kasbon', 'public');
        }
        if ($request->hasFile('photo_2')) {
            if($kasbon->photo_2) Storage::disk('public')->delete($kasbon->photo_2);
            $data['photo_2'] = $request->file('photo_2')->store('kasbon', 'public');
        }

        $kasbon->update($data);

        return redirect()->route('kasbon.show', $id)->with('success', 'Data kasbon berhasil diperbarui.');
    }

    // --- FITUR CICILAN USER ---
    public function storeInstallment(Request $request, $id)
    {
        if ($request->has('amount_paid')) {
            $request->merge(['amount_paid' => str_replace('.', '', $request->amount_paid)]);
        }

        $kasbon = CashAdvance::findOrFail($id);
        
        // Validasi input
        $request->validate([
            'amount_paid' => 'required|numeric|min:1000|max:' . ($kasbon->amount - $kasbon->total_paid + 1000), // Toleransi dikit
            'payment_proof' => 'required|image|max:2048',
            'received_by' => 'required|string|max:100',
        ]);

        $path = $request->file('payment_proof')->store('kasbon/installments', 'public');

        CashAdvanceInstallment::create([
            'cash_advance_id' => $kasbon->id,
            'user_id' => auth()->id(), // Yang input (bisa user ybs, atau admin yg inputin)
            'amount_paid' => $request->amount_paid,
            'received_by' => $request->received_by,
            'payment_proof' => $path,
            'status' => 'pending'
        ]);

        return back()->with('success', 'Pembayaran cicilan berhasil dikirim, menunggu verifikasi.');
    }

    // ==========================================
    // ACTION ADMIN & ADMIN GAJI (SUPER ACCESS)
    // ==========================================

    // 1. HAPUS KASBON FULL
    public function destroy($id) 
    {
        if (!in_array(auth()->user()->role, ['admin', 'admin_gaji'])) abort(403);
        
        $kasbon = CashAdvance::findOrFail($id);
        
        // Hapus file
        if($kasbon->photo_1) Storage::disk('public')->delete($kasbon->photo_1);
        if($kasbon->photo_2) Storage::disk('public')->delete($kasbon->photo_2);
        
        $kasbon->delete(); // Installment & Plan ikut terhapus (cascade di database biasanya, atau soft delete)
        
        return redirect()->route('kasbon.index')->with('success', 'Data kasbon berhasil dihapus permanen.');
    }

    // 2. UBAH STATUS KASBON (APPROVE / REJECT)
    public function changeStatus(Request $request, $id) 
    {
        if (!in_array(auth()->user()->role, ['admin', 'admin_gaji'])) abort(403);
        
        $kasbon = CashAdvance::findOrFail($id);
        
        $kasbon->update([
            'status' => $request->status,
            'processed_by' => auth()->id(),
            'approved_date' => $request->status == 'approved' ? now() : null
        ]);
        
        return back()->with('success', 'Status pengajuan berhasil diperbarui.');
    }

    // 3. APPROVE CICILAN MASUK
    public function approveInstallment($installmentId) 
    {
        if (!in_array(auth()->user()->role, ['admin', 'admin_gaji'])) abort(403);
        
        DB::transaction(function() use ($installmentId) {
            $installment = CashAdvanceInstallment::with('cashAdvance')->findOrFail($installmentId);
            
            if ($installment->status == 'approved') return; // Cegah double approve
            
            // Update status cicilan
            $installment->update(['status' => 'approved']);
            
            // Update total bayar di kasbon induk
            $parent = $installment->cashAdvance;
            $parent->total_paid += $installment->amount_paid;
            
            // Cek Lunas
            if ($parent->total_paid >= $parent->amount) {
                $parent->status = 'paid';
                $parent->repayment_date = now();
            }
            
            $parent->save();
        });
        
        return back()->with('success', 'Pembayaran cicilan berhasil diverifikasi.');
    }

    // 4. REJECT CICILAN MASUK
    public function rejectInstallment(Request $request, $installmentId) 
    {
        if (!in_array(auth()->user()->role, ['admin', 'admin_gaji'])) abort(403);
        
        $installment = CashAdvanceInstallment::findOrFail($installmentId);
        $installment->update([
            'status' => 'rejected', 
            'note' => $request->note ?? 'Ditolak oleh Admin'
        ]);
        
        return back()->with('error', 'Pembayaran cicilan ditolak.');
    }

    // 5. EDIT DATA CICILAN (FORM)
    public function editInstallment($id) 
    {
        if (!in_array(auth()->user()->role, ['admin', 'admin_gaji'])) abort(403);
        
        $installment = CashAdvanceInstallment::with('cashAdvance.user')->findOrFail($id);
        return view('kasbon.edit_installment', compact('installment'));
    }

    // 6. UPDATE DATA CICILAN (PROCESS)
    public function updateInstallment(Request $request, $id) 
    {
        if (!in_array(auth()->user()->role, ['admin', 'admin_gaji'])) abort(403);
        
        $request->validate([
            'amount_paid' => 'required|numeric|min:0', 
            'status' => 'required', 
            'note' => 'nullable',
            'received_by' => 'required'
        ]);

        DB::transaction(function() use ($request, $id) {
            $installment = CashAdvanceInstallment::with('cashAdvance')->findOrFail($id);
            $parent = $installment->cashAdvance;
            
            // Rollback saldo lama dulu jika sebelumnya approved
            if ($installment->status == 'approved') {
                $parent->total_paid -= $installment->amount_paid;
            }

            // Update Data Installment
            $installment->amount_paid = $request->amount_paid;
            $installment->received_by = $request->received_by;
            $installment->status = $request->status;
            $installment->note = $request->note;
            $installment->save();

            // Tambah saldo baru jika status approved
            if ($installment->status == 'approved') {
                $parent->total_paid += $installment->amount_paid;
            }

            // Cek Lunas Ulang
            if ($parent->total_paid >= $parent->amount) {
                $parent->status = 'paid';
                $parent->repayment_date = $parent->repayment_date ?? now();
            } else {
                // Jika edit menyebabkan saldo berkurang dari lunas -> balik ke approved
                if($parent->status == 'paid') {
                    $parent->status = 'approved';
                    $parent->repayment_date = null;
                }
            }
            $parent->save();
        });

        return redirect()->route('kasbon.show', CashAdvanceInstallment::find($id)->cash_advance_id)
                         ->with('success', 'Data cicilan berhasil diperbarui.');
    }

    // 7. HAPUS CICILAN
    public function destroyInstallment($id) 
    {
        if (!in_array(auth()->user()->role, ['admin', 'admin_gaji'])) abort(403);
        
        DB::transaction(function() use ($id) {
            $installment = CashAdvanceInstallment::with('cashAdvance')->findOrFail($id);
            $parent = $installment->cashAdvance;
            
            // Kurangi saldo induk jika yang dihapus statusnya approved
            if ($installment->status == 'approved') {
                $parent->total_paid -= $installment->amount_paid;
                
                // Jika sebelumnya lunas, kembalikan status jadi approved (belum lunas)
                if ($parent->total_paid < $parent->amount) {
                    $parent->status = 'approved';
                    $parent->repayment_date = null;
                }
                $parent->save();
            }
            
            if($installment->payment_proof) {
                Storage::disk('public')->delete($installment->payment_proof);
            }
            
            $installment->delete();
        });
        
        return back()->with('success', 'Data cicilan berhasil dihapus.');
    }
}
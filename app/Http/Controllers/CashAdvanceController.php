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
    // Construct tidak lagi memblokir Leader
    public function __construct()
    {
        // Middleware auth standar (biasanya sudah di route, tapi aman ditaruh sini juga)
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = CashAdvance::with(['user', 'installments']);

        // --- LOGIC VIEW DATA ---
        if (in_array($user->role, ['admin', 'admin_gaji'])) {
            // 1. Admin & Admin Gaji: LIHAT SEMUA (Global)
            // Tidak ada filter, ambil semua.
        } elseif ($user->role === 'audit') {
            // 2. Audit: Lihat banyak cabang (sesuai pegangan) + Diri Sendiri
            $branchIds = $user->branches->pluck('id');
            $query->where(function($q) use ($branchIds, $user) {
                $q->whereHas('user', function ($subQ) use ($branchIds) {
                    $subQ->whereIn('branch_id', $branchIds);
                })
                ->orWhere('user_id', $user->id);
            });
        } elseif ($user->role === 'leader') {
            // 3. Leader: Lihat 1 Cabang (Cabang dia) + Diri Sendiri
            $query->where(function($q) use ($user) {
                $q->whereHas('user', function ($subQ) use ($user) {
                    $subQ->where('branch_id', $user->branch_id);
                })
                ->orWhere('user_id', $user->id);
            });
        } else {
            // 4. User Biasa / Security: Hanya lihat punya sendiri
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

        // 1. CEK OVERDUE
        // Admin, Admin Gaji, dan Leader di-bypass pengecekan ini 
        // agar mereka tetap bisa input data untuk orang lain meskipun akun mereka sendiri ada tagihan.
        if (!in_array($user->role, ['admin', 'admin_gaji', 'leader'])) {
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
            // Admin: Semua User
            $users = User::orderBy('name', 'asc')->get();
        
        } elseif ($user->role === 'audit') {
            // Audit: User di cabang pegangan
            $branchIds = $user->branches->pluck('id');
            $users = User::whereIn('branch_id', $branchIds)->orWhere('id', $user->id)->orderBy('name')->get();
        
        } elseif ($user->role === 'leader') {
            // LEADER: User di cabang dia sendiri + Dirinya sendiri
            $users = User::where('branch_id', $user->branch_id)
                         ->orWhere('id', $user->id)
                         ->orderBy('name', 'asc')
                         ->get();
        } else {
            // User Biasa: Cuma diri sendiri
            $users = collect([$user]);
        }

        return view('kasbon.create', compact('users'));
    }

    public function store(Request $request)
    {
        // 1. BERSIHKAN FORMAT RUPIAH
        if ($request->has('amount')) {
            $request->merge(['amount' => str_replace('.', '', $request->amount)]);
        }

        // 2. FORCE USER ID
        // Jika BUKAN (Admin / Gaji / Leader), paksa user_id jadi id login.
        // Artinya: Leader BOLEH input user_id (memilih bawahan).
        if (!in_array(auth()->user()->role, ['admin', 'admin_gaji', 'leader'])) {
            $request->merge(['user_id' => auth()->id()]);
        }

        // 3. VALIDASI
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:100',
            'amount' => 'required|numeric|min:1000|max:1000000000',
            'tenor' => 'required|integer|min:1|max:24',
            'start_date' => 'required|date',
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

            if ($request->hasFile('photo_1')) {
                $data['photo_1'] = $request->file('photo_1')->store('kasbon', 'public');
            }
            if ($request->hasFile('photo_2')) {
                $data['photo_2'] = $request->file('photo_2')->store('kasbon', 'public');
            }

            // Simpan Kasbon Utama
            $kasbon = CashAdvance::create($data);

            // Generate Rencana Cicilan
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
        
        // CEK HAK AKSES SHOW
        $user = auth()->user();
        $isAuthorized = false;

        if (in_array($user->role, ['admin', 'admin_gaji'])) {
            // 1. Admin: Bebas
            $isAuthorized = true;
        } elseif ($user->role === 'audit') {
            // 2. Audit: Cek Cabang Pegangan
            if ($user->branches->contains('id', $kasbon->user->branch_id) || $kasbon->user_id == $user->id) {
                $isAuthorized = true;
            }
        } elseif ($user->role === 'leader') {
            // 3. LEADER: Cek apakah user target satu cabang dengan Leader
            if ($kasbon->user->branch_id == $user->branch_id || $kasbon->user_id == $user->id) {
                $isAuthorized = true;
            }
        } elseif ($kasbon->user_id == $user->id) {
            // 4. User Biasa: Punya sendiri
            $isAuthorized = true;
        }

        if (!$isAuthorized) abort(403, 'Anda tidak memiliki akses melihat data ini.');

        return view('kasbon.show', compact('kasbon'));
    }

    // --- FITUR CICILAN USER ---
    public function storeInstallment(Request $request, $id)
    {
        if ($request->has('amount_paid')) {
            $request->merge(['amount_paid' => str_replace('.', '', $request->amount_paid)]);
        }

        $kasbon = CashAdvance::findOrFail($id);
        
        $request->validate([
            'amount_paid' => 'required|numeric|min:1000|max:' . ($kasbon->amount - $kasbon->total_paid + 1000), 
            'payment_proof' => 'required|image|max:2048',
            'received_by' => 'required|string|max:100',
        ]);

        $path = $request->file('payment_proof')->store('kasbon/installments', 'public');

        CashAdvanceInstallment::create([
            'cash_advance_id' => $kasbon->id,
            'user_id' => auth()->id(), 
            'amount_paid' => $request->amount_paid,
            'received_by' => $request->received_by,
            'payment_proof' => $path,
            'status' => 'pending'
        ]);

        return back()->with('success', 'Pembayaran cicilan berhasil dikirim, menunggu verifikasi.');
    }

    // ==========================================
    // ACTION ADMIN & ADMIN GAJI (SUPER ACCESS)
    // Leader TIDAK BISA Edit/Delete/Approve
    // ==========================================

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

    public function destroy($id) 
    {
        if (!in_array(auth()->user()->role, ['admin', 'admin_gaji'])) abort(403);
        
        $kasbon = CashAdvance::findOrFail($id);
        if($kasbon->photo_1) Storage::disk('public')->delete($kasbon->photo_1);
        if($kasbon->photo_2) Storage::disk('public')->delete($kasbon->photo_2);
        $kasbon->delete();
        
        return redirect()->route('kasbon.index')->with('success', 'Data kasbon berhasil dihapus permanen.');
    }

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

    public function approveInstallment($installmentId) 
    {
        if (!in_array(auth()->user()->role, ['admin', 'admin_gaji'])) abort(403);
        
        DB::transaction(function() use ($installmentId) {
            $installment = CashAdvanceInstallment::with('cashAdvance')->findOrFail($installmentId);
            if ($installment->status == 'approved') return;
            
            $installment->update(['status' => 'approved']);
            
            $parent = $installment->cashAdvance;
            $parent->total_paid += $installment->amount_paid;
            
            if ($parent->total_paid >= $parent->amount) {
                $parent->status = 'paid';
                $parent->repayment_date = now();
            }
            $parent->save();
        });
        return back()->with('success', 'Pembayaran cicilan berhasil diverifikasi.');
    }

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

    public function editInstallment($id) 
    {
        if (!in_array(auth()->user()->role, ['admin', 'admin_gaji'])) abort(403);
        $installment = CashAdvanceInstallment::with('cashAdvance.user')->findOrFail($id);
        return view('kasbon.edit_installment', compact('installment'));
    }

    public function updateInstallment(Request $request, $id) 
    {
        if (!in_array(auth()->user()->role, ['admin', 'admin_gaji'])) abort(403);
        
        $request->validate(['amount_paid' => 'required|numeric|min:0', 'status' => 'required', 'note' => 'nullable', 'received_by' => 'required']);

        DB::transaction(function() use ($request, $id) {
            $installment = CashAdvanceInstallment::with('cashAdvance')->findOrFail($id);
            $parent = $installment->cashAdvance;
            
            if ($installment->status == 'approved') $parent->total_paid -= $installment->amount_paid;

            $installment->amount_paid = $request->amount_paid;
            $installment->received_by = $request->received_by;
            $installment->status = $request->status;
            $installment->note = $request->note;
            $installment->save();

            if ($installment->status == 'approved') $parent->total_paid += $installment->amount_paid;

            if ($parent->total_paid >= $parent->amount) {
                $parent->status = 'paid';
                $parent->repayment_date = $parent->repayment_date ?? now();
            } else {
                if($parent->status == 'paid') {
                    $parent->status = 'approved';
                    $parent->repayment_date = null;
                }
            }
            $parent->save();
        });

        return redirect()->route('kasbon.show', CashAdvanceInstallment::find($id)->cash_advance_id)->with('success', 'Data cicilan berhasil diperbarui.');
    }

    public function destroyInstallment($id) 
    {
        if (!in_array(auth()->user()->role, ['admin', 'admin_gaji'])) abort(403);
        
        DB::transaction(function() use ($id) {
            $installment = CashAdvanceInstallment::with('cashAdvance')->findOrFail($id);
            $parent = $installment->cashAdvance;
            
            if ($installment->status == 'approved') {
                $parent->total_paid -= $installment->amount_paid;
                if ($parent->total_paid < $parent->amount) {
                    $parent->status = 'approved';
                    $parent->repayment_date = null;
                }
                $parent->save();
            }
            if($installment->payment_proof) Storage::disk('public')->delete($installment->payment_proof);
            $installment->delete();
        });
        return back()->with('success', 'Data cicilan berhasil dihapus.');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\CashAdvance;
use App\Models\CashAdvanceInstallment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CashAdvanceController extends Controller
{
    public function __construct()
    {
        // Middleware: Leader tidak boleh akses
        $this->middleware(function ($request, $next) {
            if (auth()->user()->role === 'leader') {
                abort(403, 'Akses ditolak.');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = CashAdvance::with(['user', 'installments']);

        // FILTER LOGIC
        if ($user->role === 'admin') {
            // Admin lihat semua
        } elseif ($user->role === 'audit') {
            $branchIds = $user->branches->pluck('id');
            $query->where(function($q) use ($branchIds, $user) {
                $q->whereHas('user', function ($subQ) use ($branchIds) {
                    $subQ->whereIn('branch_id', $branchIds);
                })
                ->orWhere('user_id', $user->id);
            });
        } else {
            // User biasa hanya lihat milik sendiri
            $query->where('user_id', $user->id);
        }

        // Pisahkan Aktif & History
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

        // CEK OVERDUE (Blokir jika ada tunggakan lewat jatuh tempo)
        $overdueCount = CashAdvance::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('total_paid', '<', DB::raw('amount'))
            ->whereDate('due_date', '<', now())
            ->count();

        if ($overdueCount > 0) {
            return redirect()->route('kasbon.index')
                ->with('error', 'ANDA DIBLOKIR: Anda memiliki tagihan yang LEWAT JATUH TEMPO. Lunasi dulu.');
        }

        // List User
        $users = collect();
        if ($user->role === 'admin') {
            $users = User::where('role', '!=', 'admin')->orderBy('name')->get();
        } elseif ($user->role === 'audit') {
            $branchIds = $user->branches->pluck('id');
            $users = User::whereIn('branch_id', $branchIds)
                         ->orWhere('id', $user->id)
                         ->orderBy('name')->get();
        } else {
            $users = collect([$user]);
        }

        return view('kasbon.create', compact('users'));
    }

    public function store(Request $request)
    {
        // BERSIHKAN RUPIAH (Hapus titik sebelum validasi)
        if ($request->has('amount')) {
            $request->merge(['amount' => str_replace('.', '', $request->amount)]);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:100',
            'amount' => 'required|numeric|min:1000',
            'payment_method' => 'required|in:cash,transfer',
            'payment_details' => 'required_if:payment_method,transfer',
            'due_date' => 'required|date|after:today',
            'description_1' => 'required|string',
            'photo_1' => 'required|image|max:2048',
        ]);

        $data = $request->except(['photo_1', 'photo_2']);
        $data['created_by'] = auth()->id();
        $data['status'] = 'pending';
        $data['total_paid'] = 0;

        if ($request->hasFile('photo_1')) {
            $data['photo_1'] = $request->file('photo_1')->store('kasbon', 'public');
        }
        if ($request->hasFile('photo_2')) {
            $data['photo_2'] = $request->file('photo_2')->store('kasbon', 'public');
        }

        CashAdvance::create($data);

        return redirect()->route('kasbon.index')->with('success', 'Pengajuan kasbon berhasil dibuat.');
    }

    public function show($id)
    {
        $kasbon = CashAdvance::with(['user', 'installments' => function($q) {
            $q->latest();
        }])->findOrFail($id);

        // Cek Akses
        if(auth()->user()->role != 'admin' && auth()->user()->id != $kasbon->user_id && auth()->user()->role != 'audit') {
             abort(403);
        }

        return view('kasbon.show', compact('kasbon'));
    }

    // --- FITUR CICILAN ---

    public function storeInstallment(Request $request, $id)
    {
        // BERSIHKAN RUPIAH (Hapus titik sebelum validasi)
        if ($request->has('amount_paid')) {
            $request->merge(['amount_paid' => str_replace('.', '', $request->amount_paid)]);
        }

        $kasbon = CashAdvance::findOrFail($id);

        $request->validate([
            'amount_paid' => 'required|numeric|min:1000|max:' . ($kasbon->amount - $kasbon->total_paid),
            'payment_proof' => 'required|image|max:2048',
            'received_by' => 'required|string|max:100', // Wajib diisi
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

        return back()->with('success', 'Pembayaran berhasil dikirim. Menunggu verifikasi Admin.');
    }

    // --- ADMIN ONLY ACTIONS ---

    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') abort(403);
        $kasbon = CashAdvance::findOrFail($id);
        $kasbon->delete();
        return redirect()->route('kasbon.index')->with('success', 'Data dihapus.');
    }

    public function changeStatus(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') abort(403);
        $kasbon = CashAdvance::findOrFail($id);
        $kasbon->update([
            'status' => $request->status,
            'processed_by' => auth()->id(),
            'approved_date' => $request->status == 'approved' ? now() : null
        ]);
        return back()->with('success', 'Status diperbarui.');
    }

    public function approveInstallment($installmentId)
    {
        if (auth()->user()->role !== 'admin') abort(403);

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

        return back()->with('success', 'Pembayaran diverifikasi.');
    }

    public function rejectInstallment(Request $request, $installmentId)
    {
        if (auth()->user()->role !== 'admin') abort(403);

        $installment = CashAdvanceInstallment::findOrFail($installmentId);
        $installment->update([
            'status' => 'rejected',
            'note' => $request->note ?? 'Ditolak Admin'
        ]);

        return back()->with('error', 'Pembayaran ditolak.');
    }
    
    // Metode Edit & Update Installment (sama seperti sebelumnya)
    public function editInstallment($id) {
        if (auth()->user()->role !== 'admin') abort(403);
        $installment = CashAdvanceInstallment::with('cashAdvance.user')->findOrFail($id);
        return view('kasbon.edit_installment', compact('installment'));
    }

    public function updateInstallment(Request $request, $id) {
        if (auth()->user()->role !== 'admin') abort(403);
        // ... (Logika update yang menghitung ulang saldo, sama seperti sebelumnya)
         $request->validate([
            'amount_paid' => 'required|numeric|min:0',
            'status' => 'required|in:pending,approved,rejected',
            'note' => 'nullable|string'
        ]);

        DB::transaction(function() use ($request, $id) {
            $installment = CashAdvanceInstallment::with('cashAdvance')->findOrFail($id);
            $parent = $installment->cashAdvance;
            if ($installment->status == 'approved') {
                $parent->total_paid -= $installment->amount_paid;
            }
            $installment->amount_paid = $request->amount_paid;
            $installment->status = $request->status;
            $installment->note = $request->note;
            $installment->save();
            if ($installment->status == 'approved') {
                $parent->total_paid += $installment->amount_paid;
            }
            if ($parent->total_paid >= $parent->amount) {
                $parent->status = 'paid';
                $parent->repayment_date = $parent->repayment_date ?? now();
            } else {
                $parent->status = 'approved'; 
                $parent->repayment_date = null;
            }
            $parent->save();
        });
        return redirect()->route('kasbon.show', CashAdvanceInstallment::find($id)->cash_advance_id)->with('success', 'Data cicilan diperbarui.');
    }

    public function destroyInstallment($id) {
        if (auth()->user()->role !== 'admin') abort(403);
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
        return back()->with('success', 'Data cicilan dihapus.');
    }
}
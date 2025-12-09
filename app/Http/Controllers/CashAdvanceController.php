<?php

namespace App\Http\Controllers;

use App\Models\CashAdvance;
use App\Models\CashAdvanceInstallment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CashAdvanceController extends Controller
{
    public function __construct()
    {
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
        $query = CashAdvance::with(['user', 'installments']); // Load installments

        // FILTER AKSES
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
            // User Biasa / Security lihat punya sendiri
            $query->where('user_id', $user->id);
        }

        // Pisahkan data
        // Active = Pending Approval OR (Approved AND Belum Lunas)
        $activeLoans = (clone $query)->where(function($q) {
            $q->where('status', 'pending')
              ->orWhere(function($sub) {
                  $sub->where('status', 'approved')->whereColumn('total_paid', '<', 'amount');
              });
        })->latest()->get();

        // History = Rejected OR (Approved AND Lunas)
        $historyLoans = (clone $query)->where(function($q) {
             $q->where('status', 'rejected')
               ->orWhere(function($sub) {
                   $sub->where('status', 'paid'); // Atau total_paid >= amount
               });
        })->latest()->get();

        return view('kasbon.index', compact('activeLoans', 'historyLoans'));
    }

    public function create()
    {
        $user = auth()->user();
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
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1000',
            'due_date' => 'required|date|after:today', // Validasi Jatuh Tempo
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

        // Cek akses manual simple
        if(auth()->user()->role != 'admin' && 
           auth()->user()->id != $kasbon->user_id && 
           auth()->user()->role != 'audit') { // Audit logic simplified for brevity
             abort(403);
        }

        return view('kasbon.show', compact('kasbon'));
    }

    // --- FITUR CICILAN (USER & ADMIN) ---

    // 1. User Bayar Cicilan
    public function storeInstallment(Request $request, $id)
    {
        $kasbon = CashAdvance::findOrFail($id);

        // Validasi
        $request->validate([
            'amount_paid' => 'required|numeric|min:1000|max:' . ($kasbon->amount - $kasbon->total_paid),
            'payment_proof' => 'required|image|max:2048'
        ]);

        $path = $request->file('payment_proof')->store('kasbon/installments', 'public');

        CashAdvanceInstallment::create([
            'cash_advance_id' => $kasbon->id,
            'user_id' => auth()->id(),
            'amount_paid' => $request->amount_paid,
            'payment_proof' => $path,
            'status' => 'pending' // Menunggu approval admin
        ]);

        return back()->with('success', 'Pembayaran cicilan dikirim. Menunggu verifikasi Admin.');
    }

    // 2. Admin Approve Cicilan
    public function approveInstallment($installmentId)
    {
        if (auth()->user()->role !== 'admin') abort(403);

        DB::transaction(function() use ($installmentId) {
            $installment = CashAdvanceInstallment::with('cashAdvance')->findOrFail($installmentId);
            
            if ($installment->status == 'approved') return; // Prevent double approve

            // 1. Update status cicilan
            $installment->update(['status' => 'approved']);

            // 2. Update total bayar di tabel induk
            $parent = $installment->cashAdvance;
            $parent->total_paid += $installment->amount_paid;
            
            // 3. Cek Lunas?
            if ($parent->total_paid >= $parent->amount) {
                $parent->status = 'paid';
                $parent->repayment_date = now();
            }
            
            $parent->save();
        });

        return back()->with('success', 'Pembayaran diverifikasi masuk.');
    }

    // 3. Admin Reject Cicilan
    public function rejectInstallment(Request $request, $installmentId)
    {
        if (auth()->user()->role !== 'admin') abort(403);

        $installment = CashAdvanceInstallment::findOrFail($installmentId);
        $installment->update([
            'status' => 'rejected',
            'note' => $request->note ?? 'Bukti tidak valid'
        ]);

        return back()->with('error', 'Pembayaran ditolak.');
    }

    // --- ADMIN ONLY (EXISTING) ---
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
}
<?php

namespace App\Http\Controllers;

use App\Models\CashAdvance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CashAdvanceController extends Controller
{
    // Cek akses: Leader dilarang masuk
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->user()->role === 'leader') {
                abort(403, 'Akses ditolak. Leader tidak dapat mengakses menu Kasbon.');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = CashAdvance::with('user');

        // FILTER AKSES
        if ($user->role === 'admin') {
            // Admin lihat semua
        } elseif ($user->role === 'audit') {
            // Audit lihat diri sendiri + User di cabang yg dipegang
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

        // Pisahkan data Belum Lunas (Pending/Approved) dan History (Lunas/Rejected)
        $activeLoans = (clone $query)->whereIn('status', ['pending', 'approved'])->latest()->get();
        $historyLoans = (clone $query)->whereIn('status', ['paid', 'rejected'])->latest()->get();

        return view('kasbon.index', compact('activeLoans', 'historyLoans'));
    }

    public function create()
    {
        $user = auth()->user();
        $users = collect();

        // LOGIKA PEMILIHAN USER DI FORM
        if ($user->role === 'admin') {
            $users = User::where('role', '!=', 'admin')->orderBy('name')->get();
        } elseif ($user->role === 'audit') {
            $branchIds = $user->branches->pluck('id');
            // User cabang + diri sendiri
            $users = User::whereIn('branch_id', $branchIds)
                         ->orWhere('id', $user->id)
                         ->orderBy('name')->get();
        } else {
            // User biasa/Security cuma bisa pilih diri sendiri
            $users = collect([$user]);
        }

        return view('kasbon.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1000',
            'description_1' => 'required|string',
            'description_2' => 'nullable|string',
            'photo_1' => 'required|image|max:2048',
            'photo_2' => 'nullable|image|max:2048',
        ]);

        $data = $request->except(['photo_1', 'photo_2']);
        $data['created_by'] = auth()->id();
        $data['status'] = 'pending';

        if ($request->hasFile('photo_1')) {
            $data['photo_1'] = $request->file('photo_1')->store('kasbon', 'public');
        }
        if ($request->hasFile('photo_2')) {
            $data['photo_2'] = $request->file('photo_2')->store('kasbon', 'public');
        }

        CashAdvance::create($data);

        return redirect()->route('kasbon.index')->with('success', 'Pengajuan kasbon berhasil dibuat.');
    }

    // Invoice / Struk Detail
    public function show($id)
    {
        $kasbon = $this->authorizeView($id);
        return view('kasbon.show', compact('kasbon'));
    }

    // --- HANYA ADMIN ---

    public function edit($id)
    {
        if (auth()->user()->role !== 'admin') abort(403);
        $kasbon = CashAdvance::findOrFail($id);
        return view('kasbon.edit', compact('kasbon'));
    }

    public function update(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') abort(403);
        
        $kasbon = CashAdvance::findOrFail($id);
        $data = $request->except(['photo_1', 'photo_2']);

        if ($request->hasFile('photo_1')) {
            if($kasbon->photo_1) Storage::disk('public')->delete($kasbon->photo_1);
            $data['photo_1'] = $request->file('photo_1')->store('kasbon', 'public');
        }
        if ($request->hasFile('photo_2')) {
            if($kasbon->photo_2) Storage::disk('public')->delete($kasbon->photo_2);
            $data['photo_2'] = $request->file('photo_2')->store('kasbon', 'public');
        }

        $kasbon->update($data);
        return redirect()->route('kasbon.index')->with('success', 'Data kasbon diperbarui.');
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') abort(403);
        $kasbon = CashAdvance::findOrFail($id);
        
        if($kasbon->photo_1) Storage::disk('public')->delete($kasbon->photo_1);
        if($kasbon->photo_2) Storage::disk('public')->delete($kasbon->photo_2);
        if($kasbon->repayment_proof) Storage::disk('public')->delete($kasbon->repayment_proof);
        
        $kasbon->delete();
        return redirect()->route('kasbon.index')->with('success', 'Data dihapus.');
    }

    // Approve / Reject
    public function changeStatus(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') abort(403);

        $kasbon = CashAdvance::findOrFail($id);
        $status = $request->status; // approved / rejected

        $kasbon->update([
            'status' => $status,
            'processed_by' => auth()->id(),
            'approved_date' => $status == 'approved' ? now() : null
        ]);

        return back()->with('success', 'Status kasbon diperbarui: ' . ucfirst($status));
    }

    // Mark as Paid (Lunas)
    public function markAsPaid(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') abort(403);

        $request->validate([
            'repayment_proof' => 'required|image|max:2048',
        ]);

        $kasbon = CashAdvance::findOrFail($id);
        
        $path = $request->file('repayment_proof')->store('kasbon/repayments', 'public');

        $kasbon->update([
            'status' => 'paid',
            'repayment_date' => now(),
            'repayment_proof' => $path
        ]);

        return back()->with('success', 'Kasbon ditandai LUNAS.');
    }

    // Helper Authorization View
    private function authorizeView($id)
    {
        $kasbon = CashAdvance::with('user')->findOrFail($id);
        $user = auth()->user();

        if ($user->role === 'admin') return $kasbon;
        if ($kasbon->user_id === $user->id) return $kasbon;
        
        if ($user->role === 'audit') {
             $branchIds = $user->branches->pluck('id')->toArray();
             if (in_array($kasbon->user->branch_id, $branchIds)) return $kasbon;
        }

        abort(403);
    }
}
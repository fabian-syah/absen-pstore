<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use App\Models\JobTarget; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BranchController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Auth::check() && in_array(Auth::user()->role, ['admin', 'audit', 'leader'])) {
                return $next($request);
            }
            return abort(403, 'Hanya Admin, Audit, atau Leader yang boleh mengakses halaman ini.');
        });
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Branch::query();

        if ($user->role == 'admin' && $user->branch_id != null) {
            $query->where('id', $user->branch_id);
        } elseif (in_array($user->role, ['audit', 'leader'])) {
            $allowedBranchIds = $user->branches->pluck('id')->toArray();
            if ($user->branch_id) {
                $allowedBranchIds[] = $user->branch_id;
            }
            $allowedBranchIds = array_unique($allowedBranchIds);
            $query->whereIn('id', $allowedBranchIds);
        }

        if ($request->has('search') && $request->search != null) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('address', 'LIKE', "%{$search}%")
                    ->orWhere('id', 'LIKE', "%{$search}%");
            });
        }

        $branches = $query->orderBy('is_active', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        return view('branch.branch_index', compact('branches'));
    }

    public function show(Branch $branch)
    {
        $user = Auth::user();

        if ($user->role == 'admin' && $user->branch_id != null) {
            if ($branch->id != $user->branch_id) abort(403, 'Akses Ditolak.');
        } elseif (in_array($user->role, ['audit', 'leader'])) {
            $allowedBranchIds = $user->branches->pluck('id')->toArray();
            if ($user->branch_id) $allowedBranchIds[] = $user->branch_id;

            if (!in_array($branch->id, $allowedBranchIds)) abort(403, 'Akses Ditolak. Cabang ini bukan wilayah Anda.');
        }

        $employees = User::with(['division', 'attendances' => function ($q) {
            $q->whereDate('check_in_time', now());
        }])
            ->where('branch_id', $branch->id)
            ->where('role', '!=', 'admin')
            ->latest()
            ->paginate(10);

        $totalEmployees = User::where('branch_id', $branch->id)->count();

        $assignedAudits = User::where('role', 'audit')
            ->where('is_active', true)
            ->whereHas('branches', function ($q) use ($branch) {
                $q->where('branches.id', $branch->id);
            })
            ->get();

        // --- TAMBAHAN: DATA TARGET & PENCAPAIAN CABANG (TIM) ---

        // 1. Target Tim Aktif (On Going)
        $branchTargets = JobTarget::where('branch_id', $branch->id)
            ->where('type', 'team_target')
            ->where('status', '!=', 'completed')
            ->orderBy('star_level', 'desc')
            ->orderBy('deadline', 'asc')
            ->get();

        // 2. Pencapaian Tim & History Target Selesai
        $branchAchievements = JobTarget::where('branch_id', $branch->id)
            ->where(function($q) {
                $q->where('type', 'team_achievement')
                  ->orWhere(function($subQ) {
                      $subQ->where('type', 'team_target')
                           ->where('status', 'completed');
                  });
            })
            ->orderBy('completed_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('branch.branch_show', compact('branch', 'employees', 'totalEmployees', 'assignedAudits', 'branchTargets', 'branchAchievements'));
    }

    public function create()
    {
        if (Auth::user()->role != 'admin' || Auth::user()->branch_id != null) {
            abort(403, 'Anda tidak memiliki akses untuk menambah cabang.');
        }
        return view('branch.branch_create');
    }

    public function store(Request $request)
    {
        if (Auth::user()->role != 'admin' || Auth::user()->branch_id != null) abort(403);

        $request->validate([
            'name' => 'required|string|max:255|unique:branches',
            'address' => 'nullable|string',
            'timezone' => 'required|string|in:Asia/Jakarta,Asia/Makassar,Asia/Jayapura', // Validasi Timezone
        ]);

        Branch::create($request->all());

        return redirect()->route('branches.index')
            ->with('success', 'Cabang baru berhasil ditambahkan.');
    }

    public function edit(Branch $branch)
    {
        $user = Auth::user();

        if (in_array($user->role, ['audit', 'leader'])) abort(403, 'Anda tidak memiliki akses edit.');

        if ($user->role == 'admin' && $user->branch_id != null) {
            if ($branch->id != $user->branch_id) abort(403);
        }

        return view('branch.branch_edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $user = Auth::user();

        if (in_array($user->role, ['audit', 'leader'])) abort(403);
        if ($user->role == 'admin' && $user->branch_id != null && $branch->id != $user->branch_id) abort(403);

        $request->validate([
            'name' => 'required|string|max:255|unique:branches,name,' . $branch->id,
            'address' => 'nullable|string',
            'timezone' => 'required|string|in:Asia/Jakarta,Asia/Makassar,Asia/Jayapura', // Validasi Timezone
            'is_active' => 'required|boolean',
        ]);

        $branch->update($request->all());

        return redirect()->route('branches.index')
            ->with('success', 'Data cabang berhasil diperbarui.');
    }

    public function destroy(Branch $branch)
    {
        if (Auth::user()->role != 'admin' || Auth::user()->branch_id != null) abort(403);

        try {
            $branch->delete();
            return redirect()->route('branches.index')
                ->with('success', 'Cabang berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('branches.index')
                ->with('error', 'Gagal menghapus cabang. Pastikan tidak ada user yang terhubung.');
        }
    }
}
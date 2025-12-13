<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\JobTarget;
use App\Models\User; // Pastikan import User
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchTargetController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $branchesQuery = Branch::query();

        if ($user->role == 'leader') {
            $branchesQuery->where('id', $user->branch_id);
        }

        $branches = $branchesQuery->orderBy('name', 'asc')->get();

        foreach ($branches as $branch) {
            // Hitung Statistik Target Tim
            $branch->team_daily = JobTarget::where('branch_id', $branch->id)->whereIn('type', ['team_target', 'team_achievement'])->where('period', 'daily')->where('status', '!=', 'completed')->count();
            $branch->team_monthly = JobTarget::where('branch_id', $branch->id)->whereIn('type', ['team_target', 'team_achievement'])->where('period', 'monthly')->where('status', '!=', 'completed')->count();
            $branch->team_yearly = JobTarget::where('branch_id', $branch->id)->whereIn('type', ['team_target', 'team_achievement'])->where('period', 'yearly')->where('status', '!=', 'completed')->count();

            // Hitung Target Personal (Total item aktif milik semua user di cabang)
            $branch->personal_count = JobTarget::whereHas('user', function($q) use ($branch) {
                $q->where('branch_id', $branch->id);
            })->whereIn('type', ['personal_target', 'personal_achievement'])->where('status', '!=', 'completed')->count();
            
            $branch->total_users = User::where('branch_id', $branch->id)->count();
        }

        return view('branch_targets.index', compact('branches'));
    }

    public function show($id)
    {
        $branch = Branch::findOrFail($id);
        $user = Auth::user();

        // Validasi Leader
        if ($user->role == 'leader' && $user->branch_id != $branch->id) {
            return redirect()->route('branch-targets.index')->with('error', 'Akses ditolak.');
        }

        // 1. Ambil Target Global Cabang (Sama seperti sebelumnya)
        $teamData = JobTarget::with(['user'])
            ->whereIn('type', ['team_target', 'team_achievement']) 
            ->where('branch_id', $branch->id)
            ->orderBy('star_level', 'desc')
            ->orderBy('deadline', 'asc')
            ->get();

        // 2. [BARU] Ambil Daftar Karyawan di Cabang Ini
        // Kita load juga jumlah target aktif mereka untuk info tambahan (opsional)
        $branchMembers = User::where('branch_id', $branch->id)
            ->where('is_active', true)
            ->orderBy('role', 'asc') // Urutkan role (misal Admin/Leader dulu) atau nama
            ->orderBy('name', 'asc')
            ->withCount(['jobTargets as active_targets_count' => function($query) {
                $query->where('status', '!=', 'completed')
                      ->whereIn('type', ['personal_target', 'personal_achievement']);
            }])
            ->get();

        return view('branch_targets.show', compact('branch', 'teamData', 'branchMembers'));
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\JobTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchTargetController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Tentukan Cabang mana yang boleh dilihat
        $branchesQuery = Branch::query();

        if ($user->role == 'leader') {
            // Leader hanya lihat cabangnya sendiri
            $branchesQuery->where('id', $user->branch_id);
        }
        // Admin & Audit melihat semua cabang (bisa ditambah logic khusus audit jika perlu)

        $branches = $branchesQuery->orderBy('name', 'asc')->get();

        // 2. Hitung Statistik Target per Cabang (Eager Loading N+1 Prevention)
        // Kita butuh hitung target Team (Cabang) dan Personal (Pribadi user di cabang itu)
        foreach ($branches as $branch) {
            // Hitung Target Cabang (Team)
            $branch->team_daily = JobTarget::where('branch_id', $branch->id)
                ->whereIn('type', ['team_target', 'team_achievement'])
                ->where('period', 'daily')
                ->where('status', '!=', 'completed')
                ->count();

            $branch->team_monthly = JobTarget::where('branch_id', $branch->id)
                ->whereIn('type', ['team_target', 'team_achievement'])
                ->where('period', 'monthly')
                ->where('status', '!=', 'completed')
                ->count();

            $branch->team_yearly = JobTarget::where('branch_id', $branch->id)
                ->whereIn('type', ['team_target', 'team_achievement'])
                ->where('period', 'yearly')
                ->where('status', '!=', 'completed')
                ->count();

            // Hitung Target Pribadi (Semua user di cabang ini)
            // Kita cari JobTarget yang user_id nya ada di dalam list user cabang ini
            $branch->personal_count = JobTarget::whereHas('user', function($q) use ($branch) {
                $q->where('branch_id', $branch->id);
            })
            ->whereIn('type', ['personal_target', 'personal_achievement'])
            ->where('status', '!=', 'completed')
            ->count();
            
            // Hitung Total User
            $branch->total_users = \App\Models\User::where('branch_id', $branch->id)->count();
        }

        return view('branch_targets.index', compact('branches'));
    }

    public function show($id)
    {
        $branch = Branch::findOrFail($id);
        $user = Auth::user();

        // Validasi Akses Leader (Agar tidak intip cabang lain via URL)
        if ($user->role == 'leader' && $user->branch_id != $branch->id) {
            return redirect()->route('branch-targets.index')->with('error', 'Akses ditolak.');
        }

        // 1. Ambil Data Target Cabang (Team)
        $teamData = JobTarget::with(['user'])
            ->whereIn('type', ['team_target', 'team_achievement']) 
            ->where('branch_id', $branch->id)
            ->orderBy('star_level', 'desc')
            ->orderBy('deadline', 'asc')
            ->get();

        // 2. Ambil Data Target Pribadi (Semua Karyawan di Cabang Ini)
        $personalData = JobTarget::with(['user'])
            ->whereIn('type', ['personal_target', 'personal_achievement'])
            ->whereHas('user', function($q) use ($branch) {
                $q->where('branch_id', $branch->id);
            })
            ->orderBy('star_level', 'desc')
            ->orderBy('deadline', 'asc')
            ->get();

        return view('branch_targets.show', compact('branch', 'teamData', 'personalData'));
    }
}
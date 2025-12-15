<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\JobTarget;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchTargetController extends Controller
{
    /**
     * Menampilkan daftar cabang sesuai hak akses user.
     */
    public function index()
    {
        $user = Auth::user();
        $branches = collect(); 

        // 1. ADMIN / ADMIN GAJI
        if (in_array($user->role, ['admin', 'admin_gaji'])) {
            if ($user->branch_id) {
                $branches = Branch::where('id', $user->branch_id)->withCount('users')->get();
            } else {
                $branches = Branch::withCount('users')->get();
            }
        }
        
        // 2. AUDIT / LEADER (MULTI BRANCH)
        elseif (in_array($user->role, ['audit', 'leader'])) {
            $branches = $user->branches()->withCount('users')->get();
            if ($branches->isEmpty() && $user->branch_id) {
                $branches = Branch::where('id', $user->branch_id)->withCount('users')->get();
            }
        }

        // 3. USER BIASA
        else {
            if ($user->branch_id) {
                $branches = Branch::where('id', $user->branch_id)->withCount('users')->get();
            }
        }

        // Hitung statistik ringkas untuk dashboard
        foreach ($branches as $branch) {
            $branch->team_daily = JobTarget::where('branch_id', $branch->id)->where('type', 'team_target')->where('period', 'daily')->count();
            $branch->team_monthly = JobTarget::where('branch_id', $branch->id)->where('type', 'team_target')->where('period', 'monthly')->count();
            $branch->team_yearly = JobTarget::where('branch_id', $branch->id)->where('type', 'team_target')->where('period', 'yearly')->count();
            
            $branch->total_users = $branch->users_count;
            
            $branch->personal_count = JobTarget::where('branch_id', $branch->id)
                ->whereIn('type', ['personal_target'])
                ->where('status', '!=', 'completed')
                ->count();
        }

        return view('branch_targets.index', compact('branches'));
    }

    /**
     * Menampilkan Detail Satu Cabang
     */
    public function show($id)
    {
        $user = Auth::user();
        $hasAccess = false;

        // --- VALIDASI AKSES ---
        if (in_array($user->role, ['admin', 'admin_gaji'])) {
            if ($user->branch_id == null || $user->branch_id == $id) {
                $hasAccess = true;
            }
        } elseif (in_array($user->role, ['audit', 'leader'])) {
            $isInMultiBranch = $user->branches()->where('branches.id', $id)->exists();
            $isHomeBase = ($user->branch_id == $id);
            if ($isInMultiBranch || $isHomeBase) {
                $hasAccess = true;
            }
        } else {
            if ($user->branch_id == $id) {
                $hasAccess = true;
            }
        }

        if (!$hasAccess) {
            return redirect()->route('branch-targets.index')->with('error', 'Akses Ditolak: Anda tidak memiliki akses ke cabang ini.');
        }

        // --- AMBIL DATA ---
        $branch = Branch::withCount('users')->findOrFail($id);

        // Ambil Target Global Tim
        $teamData = JobTarget::where('branch_id', $id)
            ->whereIn('type', ['team_target', 'team_achievement'])
            ->orderBy('star_level', 'desc')
            ->orderBy('deadline', 'asc')
            ->get();

        // Ambil Anggota Tim & Eager Load Target Personal Mereka
        // UPDATE: Menambahkan with('jobTargets')
        $branchMembers = User::where('branch_id', $id)
            ->where('is_active', true)
            ->with(['jobTargets' => function($q) {
                // Urutkan status: Pending -> In Progress -> Completed
                $q->whereIn('type', ['personal_target', 'personal_achievement'])
                  ->orderByRaw("FIELD(status, 'pending', 'in_progress', 'completed') ASC")
                  ->orderBy('deadline', 'asc');
            }])
            ->orderBy('name', 'asc')
            ->get();

        // Hitung target aktif per member (menggunakan data yang sudah di-load)
        foreach($branchMembers as $member) {
            $member->active_targets_count = $member->jobTargets->where('status', '!=', 'completed')->count();
        }

        $canManage = in_array($user->role, ['admin', 'audit', 'leader']);

        return view('branch_targets.show', compact('branch', 'teamData', 'branchMembers', 'canManage'));
    }
}
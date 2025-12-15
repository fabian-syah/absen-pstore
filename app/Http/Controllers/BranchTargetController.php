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
     * Menampilkan daftar semua cabang (Dashboard Monitoring)
     */
    public function index()
    {
        // Ambil semua cabang beserta statistik singkat
        // (Kode ini diasumsikan sudah ada dari request sebelumnya)
        $branches = Branch::withCount('users')->get();
        
        // Perhitungan dummy/real statistik bisa ditaruh sini
        // ...

        return view('branch_targets.index', compact('branches'));
    }

    /**
     * Menampilkan Detail Target Cabang Tertentu
     */
    public function show($id)
    {
        $user = Auth::user();
        
        // 1. CEK HAK AKSES
        // Hanya Admin, Audit, dan Leader yang boleh masuk menu ini
        if (!in_array($user->role, ['admin', 'audit', 'leader'])) {
            return redirect()->back()->with('error', 'Akses ditolak. Anda tidak memiliki izin.');
        }

        // Khusus Leader: Hanya boleh lihat cabangnya sendiri
        if ($user->role == 'leader' && $user->branch_id != $id) {
            return redirect()->back()->with('error', 'Anda hanya dapat mengakses cabang Anda sendiri.');
        }

        // 2. AMBIL DATA CABANG
        $branch = Branch::findOrFail($id);

        // 3. AMBIL DATA TARGET GLOBAL (TEAM)
        // Mengambil target yang ditujukan untuk "Satu Cabang Full"
        $teamData = JobTarget::where('branch_id', $id)
            ->whereIn('type', ['team_target', 'team_achievement'])
            ->orderBy('star_level', 'desc')
            ->orderBy('deadline', 'asc')
            ->get();

        // 4. AMBIL DATA ANGGOTA TIM (MEMBER)
        // Ambil user yang aktif di cabang tersebut
        $branchMembers = User::where('branch_id', $id)
            ->where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();

        // Hitung target aktif per member (untuk badge di tabel)
        foreach($branchMembers as $member) {
            $member->active_targets_count = JobTarget::where('user_id', $member->id)
                ->where('status', '!=', 'completed')
                ->whereIn('type', ['personal_target'])
                ->count();
        }

        // 5. TENTUKAN IZIN MANAJEMEN (SUPER USER DI HALAMAN INI)
        // Admin, Audit, dan Leader Cabang ini -> BOLEH Create/Edit/Update
        $canManage = in_array($user->role, ['admin', 'audit', 'leader']);

        return view('branch_targets.show', compact('branch', 'teamData', 'branchMembers', 'canManage'));
    }
}
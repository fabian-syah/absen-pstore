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
     * Menggunakan logika Multi Branch (Many-to-Many).
     */
    public function index()
    {
        $user = Auth::user();
        $branches = collect(); // Inisialisasi koleksi kosong

        // --- LOGIKA PENGAMBILAN CABANG ---

        // 1. ADMIN / ADMIN GAJI
        // Jika punya branch_id, hanya lihat cabangnya. Jika null (Pusat), lihat semua.
        if (in_array($user->role, ['admin', 'admin_gaji'])) {
            if ($user->branch_id) {
                $branches = Branch::where('id', $user->branch_id)->withCount('users')->get();
            } else {
                $branches = Branch::withCount('users')->get();
            }
        }
        
        // 2. AUDIT / LEADER (MULTI BRANCH FUNCTION)
        // Mengambil data dari relasi 'branches' di Model User (tabel pivot branch_user)
        elseif (in_array($user->role, ['audit', 'leader'])) {
            // Ambil dari relasi Many-to-Many
            $branches = $user->branches()->withCount('users')->get();

            // FALLBACK: Jika di pivot kosong, tapi user punya branch_id utama (Homebase)
            // Maka tampilkan branch utama tersebut.
            if ($branches->isEmpty() && $user->branch_id) {
                $branches = Branch::where('id', $user->branch_id)->withCount('users')->get();
            }
        }

        // 3. USER BIASA / SECURITY
        // Hanya melihat cabang tempat mereka ditugaskan (Homebase)
        else {
            if ($user->branch_id) {
                $branches = Branch::where('id', $user->branch_id)->withCount('users')->get();
            }
        }

        // Hitung statistik ringkas untuk tampilan dashboard (Opsional)
        // Loop ini mengisi data dummy/real time ke object branch agar tidak error di view
        foreach ($branches as $branch) {
            // Contoh hitungan target (sesuaikan dengan kebutuhan real-nya)
            $branch->team_daily = JobTarget::where('branch_id', $branch->id)->where('type', 'team_target')->where('period', 'daily')->count();
            $branch->team_monthly = JobTarget::where('branch_id', $branch->id)->where('type', 'team_target')->where('period', 'monthly')->count();
            $branch->team_yearly = JobTarget::where('branch_id', $branch->id)->where('type', 'team_target')->where('period', 'yearly')->count();
            
            $branch->total_users = $branch->users_count; // Dari withCount
            
            // Hitung target personal di cabang ini
            // Kita perlu join ke user yang ada di cabang ini
            $branch->personal_count = JobTarget::where('branch_id', $branch->id)
                ->whereIn('type', ['personal_target'])
                ->where('status', '!=', 'completed')
                ->count();
        }

        return view('branch_targets.index', compact('branches'));
    }

    /**
     * Menampilkan Detail Satu Cabang
     * Termasuk validasi apakah Audit/Leader punya akses ke cabang ini.
     */
    public function show($id)
    {
        $user = Auth::user();
        
        // --- VALIDASI AKSES MULTI BRANCH ---
        $hasAccess = false;

        // 1. Cek Admin
        if (in_array($user->role, ['admin', 'admin_gaji'])) {
            // Jika admin pusat (branch_id null) boleh semua, jika admin cabang harus match
            if ($user->branch_id == null || $user->branch_id == $id) {
                $hasAccess = true;
            }
        }
        // 2. Cek Audit / Leader (Cek Pivot Table)
        elseif (in_array($user->role, ['audit', 'leader'])) {
            // Cek apakah ID cabang ada di daftar Multi Branch user ini
            $isInMultiBranch = $user->branches()->where('branches.id', $id)->exists();
            
            // Cek apakah ID cabang sama dengan Homebase user
            $isHomeBase = ($user->branch_id == $id);

            if ($isInMultiBranch || $isHomeBase) {
                $hasAccess = true;
            }
        }
        // 3. User Lain
        else {
            if ($user->branch_id == $id) {
                $hasAccess = true;
            }
        }

        if (!$hasAccess) {
            return redirect()->route('branch-targets.index')->with('error', 'Akses Ditolak: Anda tidak memiliki akses ke cabang ini.');
        }

        // --- AMBIL DATA SETELAH VALIDASI SUKSES ---
        $branch = Branch::withCount('users')->findOrFail($id);

        // Ambil Target Global Tim
        $teamData = JobTarget::where('branch_id', $id)
            ->whereIn('type', ['team_target', 'team_achievement'])
            ->orderBy('star_level', 'desc')
            ->orderBy('deadline', 'asc')
            ->get();

        // Ambil Anggota Tim (User yang Homebase-nya di cabang ini)
        $branchMembers = User::where('branch_id', $id)
            ->where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();

        // Hitung target aktif per member
        foreach($branchMembers as $member) {
            $member->active_targets_count = JobTarget::where('user_id', $member->id)
                ->where('status', '!=', 'completed')
                ->whereIn('type', ['personal_target'])
                ->count();
        }

        // Tentukan siapa yang boleh Manage (Create/Edit) di halaman ini
        // Biasanya Admin, Audit, dan Leader yang punya akses
        $canManage = in_array($user->role, ['admin', 'audit', 'leader']);

        return view('branch_targets.show', compact('branch', 'teamData', 'branchMembers', 'canManage'));
    }
}
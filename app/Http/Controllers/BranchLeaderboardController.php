<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BranchLeaderboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $branches = collect();

        if ($user->role === 'admin') {
            $branches = Branch::where('name', '!=', 'Cabang User Non Karyawan')->orderBy('name')->get();
        } elseif ($user->role === 'audit') {
            $branches = $user->branches()->where('name', '!=', 'Cabang User Non Karyawan')->orderBy('name')->get();
        } elseif ($user->role === 'leader') {
            if ($user->branch_id) {
                $branches = Branch::where('id', $user->branch_id)->get();
            }
        } else {
            if ($user->branch_id) {
                $branches = Branch::where('id', $user->branch_id)->get();
            }
        }

        foreach ($branches as $branch) {
            $branch->total_employees = User::where('branch_id', $branch->id)
                ->where('is_active', true)
                ->whereNotIn('role', ['admin', 'super_admin', 'admin_gaji'])
                ->count();

            // Preview Top 3 untuk Index (Logic disamakan)
            $leaderboard = $this->getLeaderboardData($branch->id);
            $branch->top_employees = $leaderboard->take(3);
        }

        return view('branch_leaderboard.index', compact('branches'));
    }

    /**
     * Display the detailed leaderboard for a specific branch.
     */
    public function show($id)
    {
        $branch = Branch::findOrFail($id);
        $user = Auth::user();

        // Security Check
        if ($user->role === 'audit') {
            if (!$user->branches->contains($id))
                abort(403, 'Unauthorized action.');
        } elseif ($user->role === 'leader') {
            if ($user->branch_id != $id)
                abort(403, 'Unauthorized action.');
        }

        // --- LEADERBOARD LOGIC (FIXED & MATCHED WITH DASHBOARD) ---
        $leaderboard = $this->getLeaderboardData($id);

        // Pisahkan Top 3
        $top3 = $leaderboard->take(3);

        // Sisanya (Rank 4 dst)
        $others = $leaderboard->slice(3)->values();

        return view('branch_leaderboard.show', compact('branch', 'top3', 'others'));
    }

    private function getLeaderboardData($branchId)
    {
        $now = Carbon::now();
        $targetDate = $now->copy()->startOfMonth();
        if ($now->day > 25) {
            $targetDate->addMonth();
        }

        return Attendance::select(
            'user_id',
            DB::raw('count(DISTINCT DATE(check_in_time)) as total_attendance'),
            DB::raw('SEC_TO_TIME(AVG(TIME_TO_SEC(TIME(check_in_time)))) as avg_arrival_time'),
            DB::raw('SUM(COALESCE(TIMESTAMPDIFF(SECOND, check_in_time, check_out_time), 0)) as total_work_seconds')
        )
            ->whereBetween('check_in_time', [
                $targetDate->copy()->startOfMonth()->subMonth()->day(26)->startOfDay()->timezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                $targetDate->copy()->startOfMonth()->day(25)->endOfDay()->timezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->where('status', 'verified')
            // [SYNC] Samakan daftar status dengan DashboardController
            ->whereIn('presence_status', [
                'Masuk',
                'Hadir',
                'Tepat Waktu',
                'WFH',
                'Work From Home',
                'WFH / Dinas Luar',
                'Dinas Luar',
                'Kunjungan Rutin',
                'Lembur',
                'Telat',
                'Izin Telat'
            ])
            ->whereHas('user', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                    ->where('is_active', true)
                    ->whereNotIn('role', ['admin', 'super_admin', 'admin_gaji', 'security']); // [FIX] Exclude Admin & Security agar sinkron dengan Dashboard
            })
            ->groupBy('user_id')
            ->orderBy('total_attendance', 'desc')
            ->orderBy('total_work_seconds', 'desc')
            ->orderBy('avg_arrival_time', 'asc')
            ->with(['user', 'user.division'])
            ->get()
            ->map(function ($item) {
                // Tambahkan display time agar formatnya sama dengan Dashboard
                $item->avg_arrival_display = Carbon::parse($item->avg_arrival_time)->format('H:i');
                return $item;
            });
    }
}
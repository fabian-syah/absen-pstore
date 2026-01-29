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
            $branches = Branch::orderBy('name')->get();
        } elseif ($user->role === 'audit') {
            $branches = $user->branches()->orderBy('name')->get();
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
                ->whereNotIn('role', ['admin'])
                ->count();

            // Preview Top 3 untuk Index (Logic disamakan)
            $top3 = Attendance::select(
                'user_id',
                DB::raw('count(*) as total_attendance'),
                DB::raw('SEC_TO_TIME(AVG(TIME_TO_SEC(TIME(check_in_time)))) as avg_arrival_time'),
                DB::raw('SUM(COALESCE(TIMESTAMPDIFF(SECOND, check_in_time, check_out_time), 0)) as total_work_seconds')
            )
                ->whereMonth('check_in_time', Carbon::now()->month)
                ->whereYear('check_in_time', Carbon::now()->year)

                // FILTER KETAT (UPDATED TO MATCH DASHBOARD)
                // ->whereNotNull('check_out_time') // Removed to allow verified but no checkout
                ->where('status', 'verified')
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
                ->where('presence_status', '!=', 'Alpha')
                ->whereTime('check_in_time', '!=', '00:00:00')
                ->whereTime('check_in_time', '!=', '00:00:00')
                // ->whereRaw('TIMESTAMPDIFF(SECOND, check_in_time, check_out_time) > 0') // Removed to allow verified entries without duration

                ->where('branch_id', $branch->id)
                ->whereHas('user', function ($q) {
                    $q->where('is_active', true)->whereNotIn('role', ['admin']);
                })
                ->groupBy('user_id')
                ->orderBy('total_attendance', 'desc')
                ->orderBy('avg_arrival_time', 'asc')
                ->take(3)
                ->with('user')
                ->get();

            $branch->top_employees = $top3;
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
        $leaderboard = Attendance::select(
            'user_id',
            DB::raw('count(*) as total_attendance'),
            DB::raw('SEC_TO_TIME(AVG(TIME_TO_SEC(TIME(check_in_time)))) as avg_arrival_time'),
            // Menghitung Total Jam Kerja
            DB::raw('SUM(COALESCE(TIMESTAMPDIFF(SECOND, check_in_time, check_out_time), 0)) as total_work_seconds')
        )
            ->whereMonth('check_in_time', Carbon::now()->month)
            ->whereYear('check_in_time', Carbon::now()->year)

            // FILTER KETAT (UPDATED TO MATCH DASHBOARD)
            // ->whereNotNull('check_out_time') // Removed
            ->where('status', 'verified')
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
            ->where('presence_status', '!=', 'Alpha')
            ->whereTime('check_in_time', '!=', '00:00:00')
            ->whereTime('check_in_time', '!=', '00:00:00')
            // ->whereRaw('TIMESTAMPDIFF(SECOND, check_in_time, check_out_time) > 0') // Removed to allow verified entries without duration

            ->where('branch_id', $id)
            ->whereHas('user', function ($q) {
                $q->where('is_active', true)
                    ->whereNotIn('role', ['admin']);
            })
            ->groupBy('user_id')
            ->orderBy('total_attendance', 'desc')     // Urutkan berdasarkan total hadir terbanyak
            ->orderBy('avg_arrival_time', 'asc')      // Jika sama, urutkan yang datang lebih pagi
            ->with(['user', 'user.division'])
            ->get();

        // Pisahkan Top 3
        $top3 = $leaderboard->take(3);

        // Sisanya (Rank 4 dst)
        $others = $leaderboard->slice(3)->values();

        return view('branch_leaderboard.show', compact('branch', 'top3', 'others'));
    }
}
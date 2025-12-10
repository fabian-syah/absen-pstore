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
    /**
     * Display a list of branches the user manages.
     */
    public function index()
    {
        $user = Auth::user();
        $branches = collect();

        // 1. Ambil Daftar Cabang Sesuai Role
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

        // 2. Loop setiap cabang untuk ambil data tambahan & TOP 3 LEADERBOARD
        foreach ($branches as $branch) {
            // Hitung Total Karyawan
            $branch->total_employees = User::where('branch_id', $branch->id)
                ->where('is_active', true)
                ->whereNotIn('role', ['admin'])
                ->count();

            // === LOGIKA TOP 3 LEADERBOARD (Untuk Preview di Kartu) ===
            $top3 = Attendance::select(
                    'user_id',
                    DB::raw('count(*) as total_attendance')
                )
                ->whereMonth('check_in_time', Carbon::now()->month)
                ->whereYear('check_in_time', Carbon::now()->year)
                ->whereNotNull('check_out_time')
                ->whereIn('presence_status', ['Masuk', 'WFH', 'WFH / Dinas Luar'])
                ->where('status', 'verified')
                ->where('branch_id', $branch->id) // Filter per cabang
                ->whereHas('user', function($q) {
                    $q->where('is_active', true)->whereNotIn('role', ['admin']);
                })
                ->groupBy('user_id')
                ->orderBy('total_attendance', 'desc')
                ->take(3) // Ambil Top 3 Saja
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
            if (!$user->branches->contains($id)) abort(403, 'Unauthorized action.');
        } elseif ($user->role === 'leader') {
             if ($user->branch_id != $id) abort(403, 'Unauthorized action.');
        }

        // Detail Leaderboard Logic (Full Data)
        $leaderboard = Attendance::select(
                'user_id',
                DB::raw('count(*) as total_attendance'),
                DB::raw('SEC_TO_TIME(AVG(TIME_TO_SEC(TIME(check_in_time)))) as avg_arrival_time')
            )
            ->whereMonth('check_in_time', Carbon::now()->month)
            ->whereYear('check_in_time', Carbon::now()->year)
            ->whereNotNull('check_out_time')
            ->whereIn('presence_status', ['Masuk', 'WFH', 'WFH / Dinas Luar'])
            ->where('status', 'verified')
            ->where('branch_id', $id)
            ->whereHas('user', function($q) {
                $q->where('is_active', true)->whereNotIn('role', ['admin']);
            })
            ->groupBy('user_id')
            ->orderBy('total_attendance', 'desc')
            ->orderBy('avg_arrival_time', 'asc')
            ->with(['user', 'user.division'])
            ->get();

        $top3 = $leaderboard->take(3);
        $others = $leaderboard->slice(3);

        return view('branch_leaderboard.show', compact('branch', 'top3', 'others'));
    }
}
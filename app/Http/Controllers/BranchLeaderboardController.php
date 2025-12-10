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
    // ... method index() biarkan saja (sudah benar) ...
    public function index()
    {
        // (Isi sama seperti sebelumnya)
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

            // Preview Top 3 untuk Index
            $top3 = Attendance::select('user_id', DB::raw('count(*) as total_attendance'))
                ->whereMonth('check_in_time', Carbon::now()->month)
                ->whereYear('check_in_time', Carbon::now()->year)
                ->whereNotNull('check_out_time')
                ->whereIn('presence_status', ['Masuk', 'WFH', 'WFH / Dinas Luar'])
                ->where('status', 'verified')
                ->where('branch_id', $branch->id)
                ->whereHas('user', function($q) {
                    $q->where('is_active', true)->whereNotIn('role', ['admin']);
                })
                ->groupBy('user_id')
                ->orderBy('total_attendance', 'desc')
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
            if (!$user->branches->contains($id)) abort(403, 'Unauthorized action.');
        } elseif ($user->role === 'leader') {
             if ($user->branch_id != $id) abort(403, 'Unauthorized action.');
        }

        // --- LEADERBOARD LOGIC (FIXED) ---
        // Ambil SEMUA data leaderboard yang memenuhi syarat (tanpa limit dulu)
        // Agar kita bisa memisahkan Top 3 dan sisanya secara akurat.
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
                $q->where('is_active', true)
                  ->whereNotIn('role', ['admin']);
            })
            ->groupBy('user_id')
            ->orderBy('total_attendance', 'desc')     // Urutkan berdasarkan total hadir terbanyak
            ->orderBy('avg_arrival_time', 'asc')      // Jika sama, urutkan yang datang lebih pagi
            ->with(['user', 'user.division'])
            ->get(); // Ambil Collection, bukan Query Builder

        // Pisahkan Top 3
        $top3 = $leaderboard->take(3);
        
        // Sisanya (mulai dari indeks ke-3, yaitu Rank 4 dst)
        // Values() digunakan untuk me-reset key array agar foreach di view mulai dari 0 lagi (opsional tapi aman)
        $others = $leaderboard->slice(3)->values(); 

        return view('branch_leaderboard.show', compact('branch', 'top3', 'others'));
    }
}
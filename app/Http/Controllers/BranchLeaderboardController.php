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

        if ($user->role === 'admin') {
            // Admin sees all branches
            $branches = Branch::orderBy('name')->get();
        } elseif ($user->role === 'audit') {
            // Audit sees assigned branches
            $branches = $user->branches()->orderBy('name')->get();
        } elseif ($user->role === 'leader') {
            // Leader usually sees their primary branch or assigned divisions' branches
            // Adjust logic based on your specific Leader-Branch relationship
            // For now, assuming Leader is tied to one branch in `users` table or multiple via `divisions`
            // If Leader uses the same many-to-many as Audit, use $user->branches
            // If Leader is just strictly one branch:
            if ($user->branch_id) {
                $branches = Branch::where('id', $user->branch_id)->get();
            }
        } else {
            // Fallback for other roles (e.g. security view their own branch)
            if ($user->branch_id) {
                $branches = Branch::where('id', $user->branch_id)->get();
            }
        }

        // Calculate summary stats for each branch card (optional, for display "X Employees")
        foreach ($branches as $branch) {
            $branch->total_employees = User::where('branch_id', $branch->id)
                ->where('is_active', true)
                ->whereNotIn('role', ['admin']) // Exclude admin from count
                ->count();
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

        // Security Check: Ensure Audit/Leader can only view their allowed branches
        if ($user->role === 'audit') {
            if (!$user->branches->contains($id)) {
                abort(403, 'Unauthorized action.');
            }
        } elseif ($user->role === 'leader') {
             if ($user->branch_id != $id) {
                 // If leader logic allows multiple, change this check
                 abort(403, 'Unauthorized action.');
             }
        }

        // --- LEADERBOARD LOGIC ---
        // Criteria:
        // 1. Current Month
        // 2. Verified Attendance (Status = verified)
        // 3. Must have Check In AND Check Out (Complete Cycle)
        // 4. Presence Status includes Masuk, WFH
        // 5. Sorted by Count (Most diligent) then by Avg Arrival Time (Earliest)

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
            ->where('branch_id', $id) // Filter by specific branch
            ->whereHas('user', function($q) {
                $q->where('is_active', true)
                  ->whereNotIn('role', ['admin']);
            })
            ->groupBy('user_id')
            ->orderBy('total_attendance', 'desc')
            ->orderBy('avg_arrival_time', 'asc')
            ->with(['user', 'user.division'])
            ->get();

        // Get Top 3 separately for special UI
        $top3 = $leaderboard->take(3);
        
        // The rest (Rank 4 onwards)
        $others = $leaderboard->slice(3);

        return view('branch_leaderboard.show', compact('branch', 'top3', 'others'));
    }
}
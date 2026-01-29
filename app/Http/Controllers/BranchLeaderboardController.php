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
        // 1. Get Attendance Stats
        $attendanceStats = Attendance::select(
            'user_id',
            DB::raw('count(*) as total_attendance'),
            DB::raw('SEC_TO_TIME(AVG(TIME_TO_SEC(TIME(check_in_time)))) as avg_arrival_time'),
            DB::raw('SUM(COALESCE(TIMESTAMPDIFF(SECOND, check_in_time, check_out_time), 0)) as total_work_seconds')
        )
            ->whereMonth('check_in_time', Carbon::now()->month)
            ->whereYear('check_in_time', Carbon::now()->year)
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
            ->where('branch_id', $branchId)
            ->whereHas('user', function ($q) {
                $q->where('is_active', true)->whereNotIn('role', ['admin']);
            })
            ->groupBy('user_id')
            ->with(['user', 'user.division'])
            ->get()
            ->keyBy('user_id');

        // 2. Get Leave Request Stats (Full Collection)
        $leaveRequests = \App\Models\LeaveRequest::with('user')
            ->where('status', 'approved')
            ->whereIn('type', ['wfh', 'dinas', 'kunjungan_rutin', 'izin', 'cuti'])
            ->where(function ($q) {
                $q->whereMonth('start_date', Carbon::now()->month)
                    ->whereYear('start_date', Carbon::now()->year)
                    ->orWhereMonth('end_date', Carbon::now()->month)
                    ->whereYear('end_date', Carbon::now()->year);
            })
            ->whereHas('user', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                    ->where('is_active', true)
                    ->whereNotIn('role', ['admin']);
            })
            ->get();

        // Calculate Days per User
        $leaveCounts = [];
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        foreach ($leaveRequests as $leave) {
            $uid = $leave->user_id;

            $start = Carbon::parse($leave->start_date);
            $end = Carbon::parse($leave->end_date);

            // Adjust to month boundaries
            if ($start->lt($startOfMonth))
                $start = $startOfMonth->copy();
            if ($end->gt($endOfMonth))
                $end = $endOfMonth->copy();

            if ($start->gt($end))
                continue;

            $days = $start->diffInDays($end) + 1;

            if (!isset($leaveCounts[$uid])) {
                $leaveCounts[$uid] = [
                    'count' => 0,
                    'user' => $leave->user
                ];
            }
            $leaveCounts[$uid]['count'] += $days;
        }

        // 3. Merge Data
        $merged = collect();
        $attUserIds = $attendanceStats->keys();
        $leaveUserIds = array_keys($leaveCounts);
        $allUserIds = $attUserIds->merge($leaveUserIds)->unique();

        foreach ($allUserIds as $uid) {
            $att = $attendanceStats->get($uid);
            $leaveData = $leaveCounts[$uid] ?? null;

            $user = $att ? $att->user : ($leaveData ? $leaveData['user'] : User::find($uid));

            // STRICT FILTER: Check if user is REALLY in this branch currently
            if (!$user || $user->branch_id != $branchId)
                continue;
            if (!$user->is_active)
                continue;

            $attCount = $att ? $att->total_attendance : 0;
            $leaveCount = $leaveData ? $leaveData['count'] : 0;
            $totalCount = $attCount + $leaveCount;

            $merged->push((object) [
                'user_id' => $uid,
                'user' => $user,
                'total_attendance' => $totalCount,
                'avg_arrival_time' => $att ? $att->avg_arrival_time : '00:00:00', // Leaves don't affect arrival
                'total_work_seconds' => $att ? $att->total_work_seconds : 0
            ]);
        }

        return $merged->sortByDesc('total_attendance')->values();
    }
}
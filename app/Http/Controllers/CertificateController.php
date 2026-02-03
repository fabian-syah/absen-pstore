<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\LeaderboardHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CertificateController extends Controller
{
    /**
     * Display the attendance certificate for the authenticated user
     */
    public function show(Request $request)
    {
        $user = Auth::user();

        // Get period from request or default to last month
        $periodInput = $request->get('period');
        $rankInput = $request->get('rank');

        if ($periodInput) {
            try {
                $date = Carbon::parse($periodInput);
                $month = $date->month;
                $year = $date->year;
            } catch (\Exception $e) {
                $lastMonth = Carbon::now()->subMonth();
                $month = $lastMonth->month;
                $year = $lastMonth->year;
            }
        } else {
            $lastMonth = Carbon::now()->subMonth();
            $month = $lastMonth->month;
            $year = $lastMonth->year;
        }

        $period = Carbon::create($year, $month, 1)->translatedFormat('F Y');

        // Check if user is in leaderboard history
        $history = LeaderboardHistory::where('user_id', $user->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if (!$history && !$rankInput) {
            // Calculate rank on the fly if no history
            $branchId = $user->branch_id;

            if (!$branchId) {
                return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki cabang terdaftar.');
            }

            $leaderboard = Attendance::select('user_id', DB::raw('count(*) as total_attendance'))
                ->where('branch_id', $branchId)
                ->whereMonth('check_in_time', $month)
                ->whereYear('check_in_time', $year)
                ->whereIn('presence_status', ['Masuk', 'WFH', 'WFH / Dinas Luar', 'Hadir', 'Tepat Waktu'])
                ->where('status', 'verified')
                ->whereHas('user', function ($q) {
                    $q->where('is_active', true)
                        ->whereNotIn('role', ['admin', 'security']);
                })
                ->groupBy('user_id')
                ->orderByDesc('total_attendance')
                ->get();

            $rank = null;
            $totalAttendance = 0;

            foreach ($leaderboard as $index => $entry) {
                if ($entry->user_id == $user->id) {
                    $rank = $index + 1;
                    $totalAttendance = $entry->total_attendance;
                    break;
                }
            }

            if (!$rank || $rank > 3) {
                return redirect()->route('dashboard')->with('error', 'Anda tidak termasuk Top 3 pada periode ini.');
            }

        } else {
            $rank = $history ? $history->rank : (int) $rankInput;
            $totalAttendance = $history ? $history->total_attendance : 0;

            // Recalculate total if from history without count
            if ($totalAttendance == 0) {
                $totalAttendance = Attendance::where('user_id', $user->id)
                    ->whereMonth('check_in_time', $month)
                    ->whereYear('check_in_time', $year)
                    ->whereIn('presence_status', ['Masuk', 'WFH', 'WFH / Dinas Luar', 'Hadir', 'Tepat Waktu'])
                    ->where('status', 'verified')
                    ->count();
            }
        }

        // Get branch info
        $branch = Branch::find($user->branch_id);

        if (!$branch) {
            return redirect()->route('dashboard')->with('error', 'Data cabang tidak ditemukan.');
        }

        // Rank text
        $rankText = match ($rank) {
            1 => 'JUARA 1',
            2 => 'JUARA 2',
            3 => 'JUARA 3',
            default => "Peringkat $rank"
        };

        return view('certificates.attendance', compact(
            'user',
            'rank',
            'rankText',
            'period',
            'totalAttendance',
            'branch'
        ));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // 1. Setup Cabang & Timezone
        $myBranchIds = $user->branches()->pluck('branches.id')->toArray();
        if ($user->branch_id) $myBranchIds[] = $user->branch_id;
        if ($user->role == 'admin' && $user->branch_id == null) $myBranchIds = Branch::pluck('id')->toArray();
        $myBranchIds = array_filter(array_unique($myBranchIds));

        // Default Timezone
        $userTimezone = $user->branch->timezone ?? 'Asia/Jakarta';
        $todayInBranch = Carbon::now($userTimezone)->format('Y-m-d');

        // 2. Query Team
        $query = User::where('users.is_active', true);

        if (empty($myBranchIds)) {
            // User Tanpa Cabang
            if (in_array($user->role, ['user_biasa', 'security'])) {
                $query->where('division_id', $user->division_id); 
            } elseif (in_array($user->role, ['audit', 'leader'])) {
                 $query->where('users.id', $user->id);
            } else {
                $query->where('users.id', 0); 
            }
        } else {
            // User Punya Cabang (Termasuk User Biasa) -> Melihat SEMUA di cabang
            $query->where(function ($q) use ($myBranchIds) {
                $q->whereIn('users.branch_id', $myBranchIds)
                    ->orWhereHas('branches', function ($subQ) use ($myBranchIds) {
                        $subQ->whereIn('branches.id', $myBranchIds);
                    });
            });
        }

        // 3. Eager Load
        $myTeam = $query->with([
            'workSchedule',
            'attendances' => function ($q) {
                $q->latest('check_in_time')->limit(5); 
            },
            'leaveRequests' => function ($q) use ($todayInBranch) {
                $q->where('status', 'approved')
                  ->whereDate('start_date', '<=', $todayInBranch)
                  ->whereDate('end_date', '>=', $todayInBranch);
            },
            'activeLateStatus', 'divisions', 'branch'
        ])
        ->leftJoin('branches', 'users.branch_id', '=', 'branches.id')
        ->orderByRaw("CASE WHEN users.id = {$user->id} THEN 0 ELSE 1 END") 
        ->orderBy('branches.name', 'asc')
        ->orderBy('users.name', 'asc')
        ->select('users.*')
        ->get();

        // 4. Statistik & Logic
        $stats = [
            'total' => $myTeam->count(),
            'hadir' => 0, 'izin_sakit' => 0, 'belum_hadir' => 0, 'lembur' => 0
        ];

        foreach($myTeam as $member) {
            $memberTz = $member->branch->timezone ?? 'Asia/Jakarta';
            $todayDate = Carbon::now($memberTz)->format('Y-m-d');
            $now = Carbon::now($memberTz);

            // Filter Absen Valid
            $validAttendance = $member->attendances->first(function ($att) use ($memberTz, $todayDate, $now) {
                $checkIn = Carbon::parse($att->check_in_time)->setTimezone($memberTz);
                $checkOut = $att->check_out_time ? Carbon::parse($att->check_out_time)->setTimezone($memberTz) : null;
                
                if ($checkIn->format('Y-m-d') === $todayDate) return true;
                if ($checkOut && $checkOut->format('Y-m-d') === $todayDate && $checkIn->format('Y-m-d') < $todayDate) return true;
                if (!$checkOut && $checkIn->diffInHours($now) < 32 && $checkIn->format('Y-m-d') < $todayDate) return true;

                return false;
            });

            $member->setRelation('attendances', $validAttendance ? collect([$validAttendance]) : collect([]));

            // Hitung Stats
            $att = $validAttendance;
            $leave = $member->leaveRequests->first();
            $isWfh = $leave && $leave->type == 'wfh';
            $isOvertime = false;

            if ($att) {
                $cIn = Carbon::parse($att->check_in_time)->setTimezone($memberTz)->format('Y-m-d');
                if ($cIn !== $todayDate) {
                    $isOvertime = true;
                    $stats['lembur']++;
                }
            }

            if ($att || $isWfh || $isOvertime) {
                $stats['hadir']++;
            } elseif ($leave && in_array($leave->type, ['sakit', 'izin', 'cuti'])) {
                $stats['izin_sakit']++;
            }
        }
        
        $stats['belum_hadir'] = $stats['total'] - ($stats['hadir'] + $stats['izin_sakit']);
        if($stats['belum_hadir'] < 0) $stats['belum_hadir'] = 0;

        // Sidebar Data
        $controlledBranches = Branch::whereIn('id', $myBranchIds)
            ->withCount(['users' => function ($q) { $q->where('is_active', true); }])
            ->orderBy('name', 'asc')->get();

        $assignedAudits = collect();
        if (!empty($myBranchIds)) {
            $assignedAudits = User::where('role', 'audit')->where('is_active', true)
                ->whereHas('branches', function($q) use ($myBranchIds) { $q->whereIn('branches.id', $myBranchIds); })->get();
        }

        return view('user_biasa.team', compact('myTeam', 'myBranchIds', 'controlledBranches', 'assignedAudits', 'stats'));
    }
    
    // --- Method Lain Tetap Sama ---
    public function myBranches()
    {
        $user = Auth::user();
        if (!in_array($user->role, ['audit', 'leader', 'admin'])) abort(403);

        $myBranchIds = $user->branches()->pluck('branches.id')->toArray();
        if ($user->branch_id) $myBranchIds[] = $user->branch_id;
        if ($user->role == 'admin' && $user->branch_id == null) $myBranchIds = Branch::pluck('id')->toArray();
        $myBranchIds = array_filter(array_unique($myBranchIds));

        $controlledBranches = Branch::whereIn('id', $myBranchIds)
            ->orderBy('name', 'asc')->get()
            ->map(function ($branch) {
                $tz = $branch->timezone ?? 'Asia/Jakarta';
                $todayInBranch = Carbon::now($tz)->format('Y-m-d');
                $nowInBranch = Carbon::now($tz);

                $users = User::where('branch_id', $branch->id)->where('is_active', true)
                    ->with(['attendances' => function($q) use ($todayInBranch, $nowInBranch) {
                        $q->where(function($sq) use ($todayInBranch) {
                            $sq->whereDate('check_in_time', $todayInBranch);
                        })->orWhere(function($sq) use ($todayInBranch) {
                            $sq->whereDate('check_out_time', $todayInBranch)
                               ->whereDate('check_in_time', '<', $todayInBranch);
                        })->orWhere(function($sq) use ($nowInBranch) {
                            $sq->whereNull('check_out_time')
                               ->where('check_in_time', '>=', $nowInBranch->copy()->subHours(32));
                        });
                    }, 'leaveRequests' => function($q) use ($todayInBranch) {
                        $q->where('status', 'approved')
                          ->whereDate('start_date', '<=', $todayInBranch)
                          ->whereDate('end_date', '>=', $todayInBranch);
                    }])->get();

                $branch->users_count = $users->count(); 
                $hadir = 0; $sakit = 0; $izin_cuti = 0; $alpha = 0; $lembur = 0;

                foreach ($users as $u) {
                    $att = $u->attendances->first();
                    $leave = $u->leaveRequests->first();
                    $isOvertime = false;
                    if ($att) {
                        $checkInDate = \Carbon\Carbon::parse($att->check_in_time)->setTimezone($tz)->format('Y-m-d');
                        $todayDate = Carbon::now($tz)->format('Y-m-d');
                        if ($checkInDate !== $todayDate) { $isOvertime = true; $lembur++; }
                    }

                    if ($att || $isOvertime) $hadir++;
                    elseif ($leave) {
                        if ($leave->type == 'sakit') $sakit++;
                        elseif ($leave->type == 'wfh') $hadir++; 
                        else $izin_cuti++; 
                    } else $alpha++; 
                }

                $branch->stats_today = ['hadir' => $hadir, 'sakit' => $sakit, 'izin' => $izin_cuti, 'alpha' => $alpha, 'lembur' => $lembur];
                return $branch;
            });

        return view('team.my_branches', compact('controlledBranches'));
    }

    public function show(User $user) { return view('team.show', compact('user')); }

    public function attendance(User $user) {
        $attendances = Attendance::where('user_id', $user->id)->latest()->paginate(10);
        return view('team.attendance', compact('user', 'attendances'));
    }

    public function showBranch($id) {
        $user = Auth::user();
        $targetBranch = Branch::findOrFail($id);
        $branchTimezone = $targetBranch->timezone ?? 'Asia/Jakarta';
        $todayInBranch = Carbon::now($branchTimezone)->format('Y-m-d');
        $nowInBranch = Carbon::now($branchTimezone);

        if ($user->role == 'audit') {
            $allowedBranches = $user->branches->pluck('id')->toArray();
            if ($user->branch_id) $allowedBranches[] = $user->branch_id;
            if (!in_array($id, $allowedBranches)) abort(403);
        } elseif ($user->role == 'leader') {
            if ($user->branch_id != $id) {
                $pivotIds = $user->branches->pluck('id')->toArray();
                if(!in_array($id, $pivotIds)) abort(403);
            }
        } elseif ($user->role == 'admin') {
            if ($user->branch_id && $user->branch_id != $id) abort(403);
        }

        $employees = User::where('branch_id', $id)->where('role', '!=', 'admin')->where('is_active', true)
            ->with(['division', 'attendances' => function ($q) use ($todayInBranch, $nowInBranch) { 
                $q->where(function($sq) use ($todayInBranch) {
                    $sq->whereDate('check_in_time', $todayInBranch);
                })->orWhere(function($sq) use ($todayInBranch) {
                    $sq->whereDate('check_out_time', $todayInBranch)->whereDate('check_in_time', '<', $todayInBranch);
                })->orWhere(function($sq) use ($nowInBranch) {
                    $sq->whereNull('check_out_time')->where('check_in_time', '>=', $nowInBranch->copy()->subHours(32));
                });
            }])->get();

        $attendanceGroups = ['Masuk' => [], 'Izin' => [], 'Sakit' => [], 'Cuti' => [], 'WFH / Dinas Luar' => [], 'Alpha / Belum Absen' => [], 'Lembur' => []];

        foreach ($employees as $emp) {
            $todayLeave = LeaveRequest::where('user_id', $emp->id)->where('status', 'approved')->where('is_active', true)
                ->where(function ($q) use ($todayInBranch) {
                    $q->where(function ($sub) use ($todayInBranch) { 
                        $sub->whereIn('type', ['sakit', 'izin', 'cuti', 'wfh'])
                            ->whereDate('start_date', '<=', $todayInBranch)->whereDate('end_date', '>=', $todayInBranch); 
                    })->orWhere(function ($sub) use ($todayInBranch) { 
                        $sub->where('type', 'telat')->whereDate('start_date', $todayInBranch); 
                    });
                })->first();
            $emp->today_leave = $todayLeave;
            $attendance = $emp->attendances->first();

            $isOvertime = false;
            if ($attendance) {
                $checkInDate = \Carbon\Carbon::parse($attendance->check_in_time)->setTimezone($branchTimezone)->format('Y-m-d');
                if ($checkInDate !== $todayInBranch) $isOvertime = true;
            }

            if ($isOvertime) $attendanceGroups['Lembur'][] = $emp;
            elseif ($attendance) $attendanceGroups['Masuk'][] = $emp; 
            elseif ($todayLeave) {
                if ($todayLeave->type == 'sakit') $attendanceGroups['Sakit'][] = $emp;
                elseif ($todayLeave->type == 'izin') $attendanceGroups['Izin'][] = $emp;
                elseif ($todayLeave->type == 'cuti') $attendanceGroups['Cuti'][] = $emp;
                elseif ($todayLeave->type == 'wfh') $attendanceGroups['WFH / Dinas Luar'][] = $emp;
                else $attendanceGroups['Alpha / Belum Absen'][] = $emp;
            } else $attendanceGroups['Alpha / Belum Absen'][] = $emp; 
        }

        $statsCounts = [
            'Masuk' => count($attendanceGroups['Masuk']),
            'Izin' => count($attendanceGroups['Izin']),
            'Sakit' => count($attendanceGroups['Sakit']),
            'Cuti' => count($attendanceGroups['Cuti']),
            'WFH / Dinas Luar' => count($attendanceGroups['WFH / Dinas Luar']),
            'Alpha / Belum Absen' => count($attendanceGroups['Alpha / Belum Absen']),
            'Lembur' => count($attendanceGroups['Lembur']),
        ];

        return view('user_biasa.branch_detail', compact('branch', 'employees', 'attendanceGroups', 'statsCounts'));
    }

    public function showEmployeeHistory(Request $request, $branchId, $employeeId) {
        $employee = User::with(['division', 'branch'])->findOrFail($employeeId);
        $selectedMonth = $request->get('month', date('m'));
        $selectedYear = $request->get('year', date('Y'));
        $data = $this->getHistoryData($employee, $selectedMonth, $selectedYear);
        return view('attendance.history', [
            'history' => $data['history'], 'summary' => $data['summary'],
            'selectedMonth' => $selectedMonth, 'selectedYear' => $selectedYear,
            'employee' => $employee,
            'prevMonth' => Carbon::createFromDate($selectedYear, $selectedMonth, 1)->subMonth()->month,
            'prevYear' => Carbon::createFromDate($selectedYear, $selectedMonth, 1)->subMonth()->year,
            'nextMonth' => Carbon::createFromDate($selectedYear, $selectedMonth, 1)->addMonth()->month,
            'nextYear' => Carbon::createFromDate($selectedYear, $selectedMonth, 1)->addMonth()->year,
        ]);
    }

    private function getHistoryData($user, $selectedMonth, $selectedYear) {
        $attendances = Attendance::with('verifier')->where('user_id', $user->id)->whereYear('check_in_time', $selectedYear)->whereMonth('check_in_time', $selectedMonth)->orderBy('check_in_time', 'desc')->get();
        $leaves = LeaveRequest::where('user_id', $user->id)->where('status', 'approved')->where('is_active', true)->where(function ($q) use ($selectedMonth, $selectedYear) { 
            $q->whereMonth('start_date', $selectedMonth)->whereYear('start_date', $selectedYear)->orWhere(function ($subQ) use ($selectedMonth, $selectedYear) { 
                $subQ->whereMonth('end_date', $selectedMonth)->whereYear('end_date', $selectedYear); 
            }); 
        })->get();
        
        foreach ($attendances as $attendance) {
            $checkInDate = \Carbon\Carbon::parse($attendance->check_in_time)->format('Y-m-d');
            $checkOutDate = $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->format('Y-m-d') : null;
            $attendance->is_overtime = false;
            if ($checkOutDate && $checkInDate !== $checkOutDate) {
                $attendance->is_overtime = true;
                $attendance->overtime_duration = \Carbon\Carbon::parse($attendance->check_in_time)->diff(\Carbon\Carbon::parse($attendance->check_out_time));
            }
        }
        return ['history' => $attendances, 'summary' => []];
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

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
        // Tanggal batas ambil data (H-2 dari hari ini untuk cek lembur)
        $dateLimit = Carbon::now($userTimezone)->subDays(2)->format('Y-m-d 00:00:00');

        // 2. Query Team
        $query = User::where('users.is_active', true);

        if (empty($myBranchIds)) {
            if (in_array($user->role, ['user_biasa', 'security'])) {
                $query->where('division_id', $user->division_id);
            } elseif (in_array($user->role, ['audit', 'leader'])) {
                $query->where('users.id', $user->id);
            } else {
                $query->where('users.id', 0);
            }
        } else {
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
            'attendances' => function ($q) use ($dateLimit) {
                // Ambil semua absen dari 2 hari terakhir.
                $q->where('check_in_time', '>=', $dateLimit)
                    ->orderBy('check_in_time', 'desc');
            },
            'leaveRequests' => function ($q) use ($todayInBranch) {
                $q->where('status', 'approved')
                    ->whereDate('start_date', '<=', $todayInBranch)
                    ->whereDate('end_date', '>=', $todayInBranch);
            },
            'activeLateStatus',
            'divisions',
            'branch'
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
            'present' => 0,
            'izin_sakit' => 0,
            'belum_present' => 0,
            'lembur' => 0
        ];

        foreach ($myTeam as $member) {
            $memberTz = $member->branch->timezone ?? 'Asia/Jakarta';
            $todayDate = Carbon::now($memberTz)->format('Y-m-d');
            $now = Carbon::now($memberTz);

            // Cari absensi yang valid untuk ditampilkan
            $validAttendance = $member->attendances->first(function ($att) use ($memberTz, $todayDate, $now) {
                $checkIn = Carbon::parse($att->check_in_time)->setTimezone($memberTz);
                $checkOut = $att->check_out_time ? Carbon::parse($att->check_out_time)->setTimezone($memberTz) : null;

                // Case 1: Masuk Hari Ini (Normal)
                if ($checkIn->format('Y-m-d') === $todayDate) return true;

                // Case 2: Selesai Lembur Hari Ini (Check in kemarin, checkout hari ini)
                if ($checkOut && $checkOut->format('Y-m-d') === $todayDate && $checkIn->format('Y-m-d') < $todayDate) return true;

                // Case 3: MASIH Lembur (Check in kemarin, belum checkout)
                if (!$checkOut && $checkIn->diffInHours($now) < 32 && $checkIn->format('Y-m-d') < $todayDate) {
                    if ($att->is_extended_shift) {
                        return true;
                    }
                    return false;
                }

                return false;
            });

            $member->setRelation('attendances', $validAttendance ? collect([$validAttendance]) : collect([]));

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

            if ($att || $isWfh || $isOvertime) $stats['present']++;
            elseif ($leave && in_array($leave->type, ['sakit', 'izin', 'cuti'])) $stats['izin_sakit']++;
        }

        $stats['belum_present'] = $stats['total'] - ($stats['present'] + $stats['izin_sakit']);
        if ($stats['belum_present'] < 0) $stats['belum_present'] = 0;

        $controlledBranches = Branch::whereIn('id', $myBranchIds)
            ->withCount(['users' => function ($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('name', 'asc')->get();

        // --- QUERY AUDIT MULTI BRANCH ---
        $assignedAudits = collect();
        if (!empty($myBranchIds)) {
            $assignedAudits = User::where('role', 'audit')
                ->where('is_active', true)
                ->with(['branches', 'branch']) // Eager Load relasi Multi dan Single
                ->where(function($q) use ($myBranchIds) {
                    // Cek Single Branch ID
                    $q->whereIn('branch_id', $myBranchIds)
                    // Cek Multi Branch (Pivot)
                      ->orWhereHas('branches', function ($sq) use ($myBranchIds) {
                          $sq->whereIn('branches.id', $myBranchIds);
                      });
                })
                ->get();
        }
        // --------------------------------

        return view('user_biasa.team', compact('myTeam', 'myBranchIds', 'controlledBranches', 'assignedAudits', 'stats'));
    }

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
                $dateLimit = Carbon::now($tz)->subDays(2)->format('Y-m-d 00:00:00');

                $users = User::where('branch_id', $branch->id)->where('is_active', true)
                    ->with(['attendances' => function ($q) use ($dateLimit) {
                        $q->where('check_in_time', '>=', $dateLimit)
                            ->orderBy('check_in_time', 'desc');
                    }, 'leaveRequests' => function ($q) use ($todayInBranch) {
                        $q->where('status', 'approved')
                            ->whereDate('start_date', '<=', $todayInBranch)
                            ->whereDate('end_date', '>=', $todayInBranch);
                    }])->get();

                $branch->users_count = $users->count();
                $present = 0;
                $sakit = 0;
                $izin_cuti = 0;
                $alpha = 0;
                $lembur = 0;

                foreach ($users as $u) {
                    $validAttendance = $u->attendances->first(function ($att) use ($tz, $todayInBranch, $nowInBranch) {
                        $checkIn = Carbon::parse($att->check_in_time)->setTimezone($tz);
                        $checkOut = $att->check_out_time ? Carbon::parse($att->check_out_time)->setTimezone($tz) : null;

                        if ($checkIn->format('Y-m-d') === $todayInBranch) return true;
                        if ($checkOut && $checkOut->format('Y-m-d') === $todayInBranch && $checkIn->format('Y-m-d') < $todayInBranch) return true;
                        if (!$checkOut && $checkIn->diffInHours($nowInBranch) < 32 && $checkIn->format('Y-m-d') < $todayInBranch) return true;
                        return false;
                    });

                    $u->setRelation('attendances', $validAttendance ? collect([$validAttendance]) : collect([]));
                    $att = $validAttendance;
                    $leave = $u->leaveRequests->first();
                    $isOvertime = false;

                    if ($att) {
                        $checkInDate = Carbon::parse($att->check_in_time)->setTimezone($tz)->format('Y-m-d');
                        if ($checkInDate !== $todayInBranch) {
                            $isOvertime = true;
                            $lembur++;
                        }
                    }

                    if ($att || $isOvertime) $present++;
                    elseif ($leave) {
                        if ($leave->type == 'sakit') $sakit++;
                        elseif ($leave->type == 'wfh') $present++;
                        else $izin_cuti++;
                    } else $alpha++;
                }

                $branch->stats_today = ['present' => $present, 'sakit' => $sakit, 'izin' => $izin_cuti, 'alpha' => $alpha, 'lembur' => $lembur];
                return $branch;
            });

        return view('team.my_branches', compact('controlledBranches'));
    }

    public function show(User $user)
    {
        return view('team.show', compact('user'));
    }

    public function attendance(User $user)
    {
        $attendances = Attendance::where('user_id', $user->id)->latest()->paginate(10);
        return view('team.attendance', compact('user', 'attendances'));
    }

    public function showBranch($id)
    {
        $user = Auth::user();
        $branch = Branch::findOrFail($id);
        $branchTimezone = $branch->timezone ?? 'Asia/Jakarta';

        $todayInBranch = Carbon::now($branchTimezone)->format('Y-m-d');
        $nowInBranch = Carbon::now($branchTimezone);
        $dateLimit = Carbon::now($branchTimezone)->subDays(2)->format('Y-m-d 00:00:00');

        if ($user->role == 'audit') {
            $allowedBranches = $user->branches->pluck('id')->toArray();
            if ($user->branch_id) $allowedBranches[] = $user->branch_id;
            if (!in_array($id, $allowedBranches)) abort(403);
        } elseif ($user->role == 'leader') {
            if ($user->branch_id != $id) {
                $pivotIds = $user->branches->pluck('id')->toArray();
                if (!in_array($id, $pivotIds)) abort(403);
            }
        } elseif ($user->role == 'admin') {
            if ($user->branch_id && $user->branch_id != $id) abort(403);
        }

        $employees = User::where('branch_id', $id)->where('role', '!=', 'admin')->where('is_active', true)
            ->with(['division', 'attendances' => function ($q) use ($dateLimit) {
                $q->where('check_in_time', '>=', $dateLimit)
                    ->orderBy('check_in_time', 'desc');
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

            $validAttendance = $emp->attendances->first(function ($att) use ($branchTimezone, $todayInBranch, $nowInBranch) {
                $checkIn = Carbon::parse($att->check_in_time)->setTimezone($branchTimezone);
                $checkOut = $att->check_out_time ? Carbon::parse($att->check_out_time)->setTimezone($branchTimezone) : null;

                if ($checkIn->format('Y-m-d') === $todayInBranch) return true;
                if ($checkOut && $checkOut->format('Y-m-d') === $todayInBranch && $checkIn->format('Y-m-d') < $todayInBranch) return true;
                if (!$checkOut && $checkIn->diffInHours($nowInBranch) < 32 && $checkIn->format('Y-m-d') < $todayInBranch) return true;
                return false;
            });

            $emp->setRelation('attendances', $validAttendance ? collect([$validAttendance]) : collect([]));
            $attendance = $validAttendance;

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

    public function showEmployeeHistory(Request $request, $branchId, $employeeId)
    {
        $employee = User::with(['division', 'branch'])->findOrFail($employeeId);
        $selectedMonth = $request->get('month', date('m'));
        $selectedYear = $request->get('year', date('Y'));
        $data = $this->getHistoryData($employee, $selectedMonth, $selectedYear);
        return view('attendance.history', [
            'history' => $data['history'],
            'summary' => $data['summary'],
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'employee' => $employee,
            'prevMonth' => Carbon::createFromDate($selectedYear, $selectedMonth, 1)->subMonth()->month,
            'prevYear' => Carbon::createFromDate($selectedYear, $selectedMonth, 1)->subMonth()->year,
            'nextMonth' => Carbon::createFromDate($selectedYear, $selectedMonth, 1)->addMonth()->month,
            'nextYear' => Carbon::createFromDate($selectedYear, $selectedMonth, 1)->addMonth()->year,
        ]);
    }

    private function getHistoryData($user, $selectedMonth, $selectedYear)
    {
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
        $summary = [
            'total' => $attendances->count(),
            'present' => $attendances->count(),
            'sakit' => $leaves->where('type', 'sakit')->count(),
            'izin'  => $leaves->whereIn('type', ['izin', 'cuti'])->count(),
            'alpha' => 0,
            'telat' => 0,
            'pulang_cepat' => 0,
            'pending' => $attendances->where('status', 'pending_verification')->count(),
        ];

        return ['history' => $attendances, 'summary' => $summary];
    }
}
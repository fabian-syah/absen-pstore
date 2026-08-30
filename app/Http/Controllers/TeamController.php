<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\Branch;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class TeamController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. Setup Cabang & Timezone
        $myBranchIds = $user->branches()->pluck('branches.id')->toArray();

        // Jika Audit & Leader: homebase branch (misal 64) dimunculkan agar bisa monitor tim sendiri.
        if ($user->branch_id) {
            $myBranchIds[] = $user->branch_id;
        }

        if ($user->role == 'admin' && $user->branch_id == null) {
            $myBranchIds = Branch::pluck('id')->toArray();
        }
        $myBranchIds = array_filter(array_unique($myBranchIds));

        // Default Timezone
        $userTimezone = $user->branch?->timezone ?? 'Asia/Jakarta';
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
            'alpha' => 0,
            'lembur' => 0
        ];

        foreach ($myTeam as $member) {
            $memberTz = $member->branch?->timezone ?? 'Asia/Jakarta';
            $todayDate = Carbon::now($memberTz)->format('Y-m-d');
            $now = Carbon::now($memberTz);

            // Cari absensi yang valid untuk ditampilkan
            $validAttendance = $member->attendances->first(function ($att) use ($memberTz, $todayDate, $now) {
                $checkIn = Carbon::parse($att->check_in_time)->setTimezone($memberTz);
                $checkOut = $att->check_out_time ? Carbon::parse($att->check_out_time)->setTimezone($memberTz) : null;

                // Case 1: Masuk Hari Ini (Normal)
                if ($checkIn->format('Y-m-d') === $todayDate)
                    return true;

                // Case 2: Selesai Lembur Hari Ini (Check in kemarin, checkout hari ini)
                if ($checkOut && $checkOut->format('Y-m-d') === $todayDate && $checkIn->format('Y-m-d') < $todayDate)
                    return true;

                // Case 3: MASIH Lembur (Check in kemarin, belum checkout)
                if (!$checkOut && $checkIn->diffInHours($now) < 24 && $checkIn->format('Y-m-d') < $todayDate) {
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
            $isWfh = ($leave && $leave->type == 'wfh') || ($att && $att->attendance_type == 'leave' && strtolower($att->presence_status) == 'wfh');
            $isOvertime = false;
            $isRealAttendance = $att && $att->attendance_type !== 'leave';

            if ($att) {
                $cIn = Carbon::parse($att->check_in_time)->setTimezone($memberTz)->format('Y-m-d');
                if ($cIn !== $todayDate) {
                    $isOvertime = true;
                    $stats['lembur']++;
                }
            }

            if ($isRealAttendance || $isWfh || $isOvertime)
                $stats['present']++;
            elseif (($att && $att->attendance_type == 'leave') || ($leave && in_array($leave->type, ['sakit', 'izin', 'cuti', 'libur'])))
                $stats['izin_sakit']++;
        }

        $stats['belum_present'] = $stats['total'] - ($stats['present'] + $stats['izin_sakit']);
        if ($stats['belum_present'] < 0)
            $stats['belum_present'] = 0;

        $controlledBranches = Branch::whereIn('id', $myBranchIds)
            ->withCount([
                'users' => function ($q) {
                    $q->where('is_active', true);
                }
            ])
            ->orderBy('name', 'asc')->get();

        // --- QUERY AUDIT: FILTER STRICT CABANG USER ---
        $assignedAudits = collect();
        if ($user->branch_id) {
            $myBranchId = $user->branch_id;
            $assignedAudits = User::where('role', 'audit')
                ->where('is_active', true)
                ->where(function ($q) use ($myBranchId) {
                    $q->where('branch_id', $myBranchId)
                        ->orWhereHas('branches', function ($sq) use ($myBranchId) {
                            $sq->where('branches.id', $myBranchId);
                        });
                })
                ->get();
        } elseif (!empty($myBranchIds)) {
            // Fallback for Admin / Regional without primary branch
            $assignedAudits = User::where('role', 'audit')
                ->where('is_active', true)
                ->whereIn('branch_id', $myBranchIds)
                ->get();
        }

        return view('user_biasa.team', compact('myTeam', 'myBranchIds', 'controlledBranches', 'assignedAudits', 'stats'));
    }

    public function myBranches()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!in_array($user->role, ['audit', 'leader', 'admin']))
            abort(403);

        $myBranchIds = $user->branches()->pluck('branches.id')->toArray();

        // Jika Audit: homebase (64) tidak otomatis masuk.
        if ($user->role != 'audit' && $user->branch_id) {
            $myBranchIds[] = $user->branch_id;
        }

        if ($user->role == 'admin' && $user->branch_id == null) {
            $myBranchIds = Branch::pluck('id')->toArray();
        }
        $myBranchIds = array_filter(array_unique($myBranchIds));

        $controlledBranches = Branch::whereIn('id', $myBranchIds)
            ->orderBy('name', 'asc')->get()
            ->map(function ($branch) {
                $tz = $branch->timezone ?? 'Asia/Jakarta';
                $todayInBranch = Carbon::now($tz)->format('Y-m-d');
                $nowInBranch = Carbon::now($tz);
                $dateLimit = Carbon::now($tz)->subDays(2)->format('Y-m-d 00:00:00');

                $users = User::where('branch_id', $branch->id)->where('is_active', true)
                    ->with([
                        'attendances' => function ($q) use ($dateLimit) {
                            $q->where('check_in_time', '>=', $dateLimit)
                                ->orderBy('check_in_time', 'desc');
                        },
                        'leaveRequests' => function ($q) use ($todayInBranch) {
                            $q->where('status', 'approved')
                                ->whereDate('start_date', '<=', $todayInBranch)
                                ->whereDate('end_date', '>=', $todayInBranch);
                        }
                    ])->get();

                $branch->users_count = $users->count();
                $present = 0;
                $sakit = 0;
                $izin_cuti = 0;
                $alpha = 0;
                $lembur = 0;
                $libur = 0;

                foreach ($users as $u) {
                    $validAttendance = $u->attendances->first(function ($att) use ($tz, $todayInBranch, $nowInBranch) {
                        $checkIn = Carbon::parse($att->check_in_time)->setTimezone($tz);
                        $checkOut = $att->check_out_time ? Carbon::parse($att->check_out_time)->setTimezone($tz) : null;

                        if ($checkIn->format('Y-m-d') === $todayInBranch)
                            return true;
                        if ($checkOut && $checkOut->format('Y-m-d') === $todayInBranch && $checkIn->format('Y-m-d') < $todayInBranch)
                            return true;
                        if (!$checkOut && $checkIn->diffInHours($nowInBranch) < 24 && $checkIn->format('Y-m-d') < $todayInBranch)
                            return true;
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

                    if ($leave && $leave->type == 'libur') {
                        $libur++;
                    } elseif ($att || $isOvertime) {
                        $present++;
                    } elseif ($leave) {
                        if ($leave->type == 'sakit')
                            $sakit++;
                        elseif ($leave->type == 'wfh')
                            $present++;
                        else
                            $izin_cuti++;
                    } else {
                        $alpha++;
                    }
                }

                $branch->stats_today = ['present' => $present, 'sakit' => $sakit, 'izin' => $izin_cuti, 'alpha' => $alpha, 'lembur' => $lembur, 'libur' => $libur];
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
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $branch = Branch::findOrFail($id);
        $branchTimezone = $branch->timezone ?? 'Asia/Jakarta';

        $todayInBranch = Carbon::now($branchTimezone)->format('Y-m-d');
        $nowInBranch = Carbon::now($branchTimezone);
        $dateLimit = Carbon::now($branchTimezone)->subDays(2)->format('Y-m-d 00:00:00');

        if ($user->role == 'audit' || $user->role == 'leader') {
            $allowedBranches = $user->branches->pluck('id')->toArray();
            if ($user->branch_id) {
                $allowedBranches[] = $user->branch_id;
            }
            
            if (!in_array($id, $allowedBranches)) {
                abort(403);
            }
        } elseif ($user->role == 'admin') {
            if ($user->branch_id && $user->branch_id != $id) {
                abort(403);
            }
        }

        $employees = User::where('branch_id', $id)->where('role', '!=', 'admin')->where('is_active', true)
            ->with([
                'division',
                'attendances' => function ($q) use ($dateLimit) {
                    $q->where('check_in_time', '>=', $dateLimit)
                        ->orderBy('check_in_time', 'desc');
                }
            ])->get();

        $attendanceGroups = ['Masuk' => [], 'Izin' => [], 'Sakit' => [], 'Cuti' => [], 'WFH / Dinas Luar' => [], 'Alpha / Belum Absen' => [], 'Lembur' => []];

        foreach ($employees as $emp) {
            $todayLeave = LeaveRequest::where('user_id', $emp->id)->where('status', 'approved')->where('is_active', true)
                ->where(function ($q) use ($todayInBranch) {
                    $q->where(function ($sub) use ($todayInBranch) {
                        $sub->whereIn('type', ['sakit', 'izin', 'cuti', 'wfh', 'libur'])
                            ->whereDate('start_date', '<=', $todayInBranch)->whereDate('end_date', '>=', $todayInBranch);
                    })->orWhere(function ($sub) use ($todayInBranch) {
                        $sub->where('type', 'telat')->whereDate('start_date', $todayInBranch);
                    });
                })->first();
            $emp->today_leave = $todayLeave;

            $validAttendance = $emp->attendances->first(function ($att) use ($branchTimezone, $todayInBranch, $nowInBranch) {
                $checkIn = Carbon::parse($att->check_in_time)->setTimezone($branchTimezone);
                $checkOut = $att->check_out_time ? Carbon::parse($att->check_out_time)->setTimezone($branchTimezone) : null;

                if ($checkIn->format('Y-m-d') === $todayInBranch)
                    return true;
                if ($checkOut && $checkOut->format('Y-m-d') === $todayInBranch && $checkIn->format('Y-m-d') < $todayInBranch)
                    return true;
                if (!$checkOut && $checkIn->diffInHours($nowInBranch) < 24 && $checkIn->format('Y-m-d') < $todayInBranch)
                    return true;
                return false;
            });

            $emp->setRelation('attendances', $validAttendance ? collect([$validAttendance]) : collect([]));
            $attendance = $validAttendance;

            $isOvertime = false;
            $isRealAttendance = $attendance && $attendance->attendance_type !== 'leave';

            if ($attendance) {
                $checkInDate = \Carbon\Carbon::parse($attendance->check_in_time)->setTimezone($branchTimezone)->format('Y-m-d');
                if ($checkInDate !== $todayInBranch)
                    $isOvertime = true;
            }

            if ($isOvertime)
                $attendanceGroups['Lembur'][] = $emp;
            elseif ($isRealAttendance)
                $attendanceGroups['Masuk'][] = $emp;
            elseif ($attendance && $attendance->attendance_type == 'leave') {
                $type = strtolower($attendance->presence_status);
                if (str_contains($type, 'sakit')) $attendanceGroups['Sakit'][] = $emp;
                elseif (str_contains($type, 'izin')) $attendanceGroups['Izin'][] = $emp;
                elseif (str_contains($type, 'cuti')) $attendanceGroups['Cuti'][] = $emp;
                elseif (str_contains($type, 'libur')) $attendanceGroups['Libur'][] = $emp;
                elseif (str_contains($type, 'wfh')) $attendanceGroups['WFH / Dinas Luar'][] = $emp;
                else $attendanceGroups['Alpha / Belum Absen'][] = $emp;
            } elseif ($todayLeave) {
                if ($todayLeave->type == 'sakit')
                    $attendanceGroups['Sakit'][] = $emp;
                elseif ($todayLeave->type == 'izin')
                    $attendanceGroups['Izin'][] = $emp;
                elseif ($todayLeave->type == 'cuti')
                    $attendanceGroups['Cuti'][] = $emp;
                elseif ($todayLeave->type == 'libur')
                    $attendanceGroups['Libur'][] = $emp;
                elseif ($todayLeave->type == 'wfh')
                    $attendanceGroups['WFH / Dinas Luar'][] = $emp;
                else
                    $attendanceGroups['Alpha / Belum Absen'][] = $emp;
            } else
                $attendanceGroups['Alpha / Belum Absen'][] = $emp;
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
        
        // Default bulan sesuai periode 26-25
        $branchTz = $employee->branch?->timezone ?? 'Asia/Jakarta';
        $now = Carbon::now($branchTz);
        
        $defaultPeriod = $now->copy()->startOfMonth();
        if ($now->day >= 26) {
            $defaultPeriod->addMonth();
        }
        $selectedMonth = $request->get('month', $defaultPeriod->month);
        $selectedYear = $request->get('year', $defaultPeriod->year);

        $data = $this->getHistoryData($employee, $selectedMonth, $selectedYear);

        // Get all employees in this branch for prev/next navigation
        $branchEmployees = User::where('branch_id', $branchId)
            ->where('is_active', true)
            ->where('role', '!=', 'admin')
            ->orderBy('name')
            ->get(['id', 'name']);

        // Find current position and get prev/next
        $employeeIds = $branchEmployees->pluck('id')->toArray();
        $currentIndex = array_search((int) $employeeId, $employeeIds);

        $prevEmployee = $currentIndex > 0 ? $branchEmployees[$currentIndex - 1] : null;
        $nextEmployee = $currentIndex !== false && $currentIndex < count($employeeIds) - 1
            ? $branchEmployees[$currentIndex + 1] : null;

        return view('attendance.history', array_merge($data, [
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'employee' => $employee,
            'branchId' => $branchId,
            'prevMonth' => Carbon::createFromDate($selectedYear, $selectedMonth, 1)->subMonth()->month,
            'prevYear' => Carbon::createFromDate($selectedYear, $selectedMonth, 1)->subMonth()->year,
            'nextMonth' => Carbon::createFromDate($selectedYear, $selectedMonth, 1)->addMonth()->month,
            'nextYear' => Carbon::createFromDate($selectedYear, $selectedMonth, 1)->addMonth()->year,
            'prevEmployee' => $prevEmployee,
            'nextEmployee' => $nextEmployee,
            'employeeCount' => count($employeeIds),
            'currentEmployeeIndex' => $currentIndex !== false ? $currentIndex + 1 : 1,
        ]));
    }

    private function getHistoryData($user, $selectedMonth, $selectedYear)
    {
        $branchTimezone = $user->branch?->timezone ?? 'Asia/Jakarta';

        // 1. Tentukan Range Awal dan Akhir Bulan (26 bulan lalu - 25 bulan ini)
        $startDate = Carbon::createFromDate($selectedYear, $selectedMonth, 26, $branchTimezone)->subMonth()->startOfDay();
        $endDate = Carbon::createFromDate($selectedYear, $selectedMonth, 25, $branchTimezone)->endOfDay();

        // 2. Ambil Absensi Real (Include H-1 untuk lembur lintas hari)
        $attendances = Attendance::with(['verifier', 'scanner', 'scannerOut', 'user'])
            ->where('user_id', $user->id)
            ->whereBetween('check_in_time', [
                $startDate->copy()->subDay()->startOfDay(),
                $endDate->copy()->addDay()->endOfDay()
            ])
            ->get();

        // 3. Ambil Izin yang bersinggungan dengan bulan terpilih
        $leaves = LeaveRequest::with('verifier')
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('is_active', true)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate->format('Y-m-d'))
                    ->where(function ($q) use ($startDate) {
                        $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', $startDate->format('Y-m-d'));
                    });
            })
            ->get();

        // Batasi penampilan sampai hari ini saja (jika melihat bulan berjalan)
        $todayInBranch = Carbon::now($branchTimezone)->startOfDay();

        // Cek apakah hari ini masih dalam periode (26 bulan lalu - 25 bulan ini)
        $isCurrentPeriod = $todayInBranch->between($startDate, $endDate);
        if ($isCurrentPeriod) {
            $hasActivityToday = $attendances->first(fn($a) => Carbon::parse($a->check_in_time)->timezone($branchTimezone)->isToday()) ||
                                $leaves->first(fn($l) => Carbon::parse($l->start_date)->isToday());

            if (!$hasActivityToday && Carbon::now($branchTimezone)->hour < 4) {
                $limitDate = $todayInBranch->copy()->subDay();
            } else {
                $limitDate = $todayInBranch;
            }
        } else {
            // Periode sudah selesai atau belum dimulai
            $limitDate = ($endDate->gt($todayInBranch)) ? $todayInBranch : $endDate;
        }

        $historyCollection = collect();
        $period = CarbonPeriod::create($startDate->copy()->startOfDay(), $limitDate->copy()->startOfDay());

        // 4. Loop Kalender Harian
        foreach ($period as $date) {
            $currentDateStr = $date->format('Y-m-d');

            // === PASS 1: Cari attendance REAL (skip system Alpha) untuk hari ini ===
            $att = $attendances->filter(function ($a) use ($currentDateStr, $branchTimezone) {
                if ($a->attendance_type === 'system' && strtolower($a->presence_status) === 'alpha') return false;
                if ($a->status === 'rejected') return false;
                return Carbon::parse($a->check_in_time)->timezone($branchTimezone)->format('Y-m-d') === $currentDateStr;
            })->sortBy(function($a) {
                return strtolower($a->presence_status) === 'masuk' ? 0 : 1;
            })->first();

            $leave = $leaves->filter(function ($l) use ($date, $branchTimezone, $att) {
                if ($l->type === 'telat' && !$att) {
                    return false;
                }
                $lStart = Carbon::parse($l->start_date, $branchTimezone)->startOfDay();
                $lEnd = Carbon::parse($l->end_date ?? $l->start_date, $branchTimezone)->endOfDay();
                return $date->between($lStart, $lEnd);
            })->first();

            if ($att) {
                $displayAtt = clone $att;
                $displayAtt->check_in_time = Carbon::parse($att->check_in_time)->timezone($branchTimezone);
                if ($att->check_out_time) {
                    $displayAtt->check_out_time = Carbon::parse($att->check_out_time)->timezone($branchTimezone);

                    // Tampilkan info jika ini shift malam
                    $inDate = $displayAtt->check_in_time->format('Y-m-d');
                    $outDate = $displayAtt->check_out_time->format('Y-m-d');
                    if ($inDate !== $outDate) {
                        $displayAtt->notes = ($displayAtt->notes ? $displayAtt->notes . ' | ' : '') . 'Shift Malam (Selesai ' . $displayAtt->check_out_time->format('d/m H:i') . ')';
                    }
                }
                if ($leave) {
                    $displayAtt->setRelation('leaveRequest', $leave);
                }
                $historyCollection->push($displayAtt);

            } elseif ($leave) {
                // Izin / Cuti
                $leaveAtt = new Attendance();
                $leaveAtt->user_id = $user->id;
                $leaveAtt->check_in_time = $date->copy()->startOfDay();

                $presenceStatusMap = [
                    'telat' => 'Izin Telat',
                    'wfh' => 'WFH',
                    'dinas' => 'Dinas Luar',
                    'izin' => 'Izin',
                    'sakit' => 'Sakit',
                    'cuti' => 'Cuti',
                    'libur' => 'Libur',
                ];
                $leaveAtt->presence_status = $presenceStatusMap[$leave->type] ?? ucfirst($leave->type);
                $leaveAtt->attendance_type = 'leave';
                $leaveAtt->notes = $leave->reason;
                $leaveAtt->setRelation('leaveRequest', $leave);
                $leaveAtt->setRelation('verifier', $leave->verifier);

                $historyCollection->push($leaveAtt);

            } else {
                // Alpha — tidak ada scan masuk dan tidak ada izin
                $alphaAtt = new Attendance();
                $alphaAtt->user_id = $user->id;
                $alphaAtt->check_in_time = $date->copy()->startOfDay();
                $alphaAtt->presence_status = 'Alpha';
                $alphaAtt->attendance_type = 'system';
                $alphaAtt->notes = '-';

                $historyCollection->push($alphaAtt);
            }
        }

        $history = $historyCollection->sortByDesc(function ($item) {
            $time = $item->check_in_time ?? $item->check_out_time;
            return $time ? $time->timestamp : 0;
        });

        // 5. HITUNG SUMMARY
        $summary = [
            'total' => $history->count(),
            'present' => $history->filter(function ($item) {
                $s = strtolower($item->presence_status ?? '');
                return in_array($s, ['masuk', 'wfh', 'dinas', 'izin telat', 'telat', 'telat hadir']) || str_contains($s, 'telat') || str_contains($s, 'dinas') ||
                    (empty($s) && in_array($item->attendance_type, ['scan', 'self', 'manual']));
            })->count(),
            'sakit' => $history->filter(fn($i) => strtolower($i->presence_status ?? '') === 'sakit')->count(),
            'izin' => $history->filter(fn($i) => in_array(strtolower($i->presence_status ?? ''), ['izin', 'cuti', 'offday']))->count(),
            'libur' => $history->filter(fn($i) => strtolower($i->presence_status ?? '') === 'libur')->count(),
            'alpha' => $history->filter(fn($i) => strtolower($i->presence_status ?? '') === 'alpha')->count(),
            'telat' => $history->where('is_late_checkin', true)->count(),
            'pulang_cepat' => $history->where('is_early_checkout', true)->count(),
            'pending' => $history->where('status', 'pending_verification')->count(),
        ];

        return ['history' => $history, 'summary' => $summary];
    }
}
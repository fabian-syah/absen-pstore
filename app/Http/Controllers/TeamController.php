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
        $selectedMonth = $request->get('month', date('m'));
        $selectedYear = $request->get('year', date('Y'));

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

        // 1. Tentukan Range Awal dan Akhir Bulan yang sedang dilihat
        // [FIX] Gunakan branchTimezone agar konsisten dengan $today (mencegah bug lintas timezone)
        $startDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1, $branchTimezone)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Batasi penampilan sampai hari ini saja (jika melihat bulan berjalan)
        $today = Carbon::now($branchTimezone)->startOfDay();
        $limitDate = ($endDate->gt($today)) ? $today : $endDate;

        // 2. Ambil Absensi Real (Include H-1 untuk lembur lintas hari)
        $attendances = Attendance::with(['verifier', 'scanner', 'user'])
            ->where('user_id', $user->id)
            ->whereBetween('check_in_time', [$startDate->copy()->subDay(), $endDate->copy()->addDay()])
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

        $historyCollection = collect();
        $period = CarbonPeriod::create($startDate, $limitDate);

        // 4. Loop Kalender Harian
        foreach ($period as $date) {
            $currentDateStr = $date->format('Y-m-d');

            // Cek Absen Real
            $att = $attendances->filter(function ($a) use ($currentDateStr, $branchTimezone) {
                return Carbon::parse($a->check_in_time)->timezone($branchTimezone)->format('Y-m-d') == $currentDateStr;
            })->first();

            // JIKA ABSEN KOSONG, Cek Izin di tabel leaves
            $leave = $leaves->filter(function ($l) use ($date) {
                $lStart = Carbon::parse($l->start_date)->startOfDay();
                $lEnd = Carbon::parse($l->end_date ?? $l->start_date)->endOfDay();
                return $date->between($lStart, $lEnd);
            })->first();

            if ($att) {
                $att->check_in_time = Carbon::parse($att->check_in_time)->timezone($branchTimezone);
                if ($att->check_out_time) {
                    $att->check_out_time = Carbon::parse($att->check_out_time)->timezone($branchTimezone);
                }

                // Attach leave request to real attendance so proof image shows
                if ($leave) {
                    $att->setRelation('leaveRequest', $leave);
                }

                $historyCollection->push($att);
            } else {

                $fakeAtt = new Attendance();
                $fakeAtt->user_id = $user->id;
                $fakeAtt->user = $user;
                $fakeAtt->check_in_time = \Illuminate\Support\Carbon::instance($date->copy()->setTime(0, 0, 0));

                if ($leave) {
                    $typeLabel = ucfirst($leave->type);
                    if ($leave->type == 'telat')
                        $typeLabel = 'Izin Telat';
                    if ($leave->type == 'wfh')
                        $typeLabel = 'WFH';

                    $fakeAtt->presence_status = $typeLabel;
                    $fakeAtt->status = 'verified';
                    $fakeAtt->attendance_type = 'leave';
                    $fakeAtt->notes = "Izin: " . $leave->reason;
                    $fakeAtt->is_late_checkin = ($leave->type == 'telat');

                    // Snapshot Jadwal
                    $fakeAtt->scheduled_check_in = $user->check_in_start;
                    $fakeAtt->scheduled_check_out = $user->check_out_start;

                    $fakeAtt->setRelation('leaveRequest', $leave);
                    $fakeAtt->setRelation('verifier', $leave->verifier);
                } else {
                    $fakeAtt->presence_status = 'Alpha';
                    $fakeAtt->status = 'verified';
                    $fakeAtt->attendance_type = 'system';
                }
                $historyCollection->push($fakeAtt);
            }
        }

        $history = $historyCollection->sortByDesc('check_in_time');

        // 5. HITUNG SUMMARY
        $summary = [
            'total' => $history->count(),
            'present' => $history->filter(function ($item) {
                $s = strtolower($item->presence_status ?? '');
                return in_array($s, ['masuk', 'wfh', 'izin telat']) || str_contains($s, 'dinas') ||
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
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

class TeamController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $myBranchIds = $user->branches()->pluck('branches.id')->toArray();
        if ($user->branch_id) {
            $myBranchIds[] = $user->branch_id;
        }
        if ($user->role == 'admin' && $user->branch_id == null) {
            $myBranchIds = Branch::pluck('id')->toArray();
        }
        $myBranchIds = array_filter(array_unique($myBranchIds));

        // [TIMEZONE SETUP]
        $userTimezone = $user->branch->timezone ?? 'Asia/Jakarta';
        $todayInBranch = Carbon::now($userTimezone)->format('Y-m-d');
        $yesterdayInBranch = Carbon::now($userTimezone)->subDay()->format('Y-m-d'); // Tanggal Kemarin
        $nowInBranch = Carbon::now($userTimezone);

        $query = User::where('users.is_active', true);

        if (empty($myBranchIds)) {
            if (in_array($user->role, ['user_biasa', 'security'])) {
                $query->where('division_id', $user->division_id)->where('users.id', '!=', $user->id);
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

        // [LOGIKA FIX: DETEKSI STATUS LEMBUR LINTAS HARI]
        $myTeam = $query->with([
            'workSchedule',
            'attendances' => function ($q) use ($todayInBranch, $yesterdayInBranch, $nowInBranch) {
                $q->where(function ($sq) use ($todayInBranch) {
                    // 1. Masuk Hari Ini (Normal)
                    $sq->whereDate('check_in_time', $todayInBranch);
                })
                    ->orWhere(function ($sq) use ($todayInBranch, $yesterdayInBranch) {
                        // 2. Pulang Hari Ini TAPI Masuk Kemarin (Lembur Selesai subuh tadi)
                        $sq->whereDate('check_in_time', $yesterdayInBranch)
                            ->whereDate('check_out_time', $todayInBranch);
                    })
                    ->orWhere(function ($sq) use ($nowInBranch) {
                        // 3. Masih Lembur (Masuk kemarin, belum pulang sampai sekarang)
                        $sq->whereNull('check_out_time')
                            ->where('check_in_time', '>=', $nowInBranch->copy()->subHours(32));
                    })
                    ->orderBy('check_in_time', 'desc'); // Ambil yang paling baru (PENTING)
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
            ->orderBy('branches.name', 'asc')->orderBy('users.name', 'asc')->select('users.*')->get();

        // Data statistik cabang (Sidebar / Extra)
        $controlledBranches = Branch::whereIn('id', $myBranchIds)
            ->withCount(['users' => function ($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('name', 'asc')->get();

        $assignedAudits = collect();
        if (!empty($myBranchIds)) {
            $assignedAudits = User::where('role', 'audit')->where('is_active', true)
                ->whereHas('branches', function ($q) use ($myBranchIds) {
                    $q->whereIn('branches.id', $myBranchIds);
                })->get();
        }

        // Hitung Statistik
        $stats = [
            'total' => $myTeam->count(),
            'hadir' => 0,
            'izin_sakit' => 0,
            'belum_hadir' => 0
        ];

        foreach ($myTeam as $member) {
            $att = $member->attendances->first();
            $leave = $member->leaveRequests->first();
            $isWfh = $leave && $leave->type == 'wfh';

            // Logic Hitung:
            // Jika ada attendance hari ini ATAU attendance 'pulang lembur' hari ini, dihitung hadir activity
            if ($att || $isWfh) {
                $stats['hadir']++;
            } elseif ($leave && in_array($leave->type, ['sakit', 'izin', 'cuti'])) {
                $stats['izin_sakit']++;
            }
        }

        $stats['belum_hadir'] = $stats['total'] - ($stats['hadir'] + $stats['izin_sakit']);
        if ($stats['belum_hadir'] < 0) $stats['belum_hadir'] = 0;

        return view('user_biasa.team', compact('myTeam', 'myBranchIds', 'controlledBranches', 'assignedAudits', 'stats'));
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

    // (Method showBranch dan myBranches sama seperti sebelumnya, sesuaikan logic attendance querynya jika perlu, 
    // tapi fokus request ini ada di index "Tim Saya")

    // ... Sisa method sama, pastikan copy paste controller ini menggantikan yang lama

    public function showBranch($id)
    {
        // ... (Kode showBranch existing, bisa dicopy dari sebelumnya)
        // Agar tidak terlalu panjang, saya asumsikan bagian ini tidak berubah drastis selain logic Timezone
        // Kode di atas sudah mencakup fix utama di method index()

        $user = Auth::user();
        $targetBranch = Branch::findOrFail($id);
        $branchTimezone = $targetBranch->timezone ?? 'Asia/Jakarta';
        $todayInBranch = Carbon::now($branchTimezone)->format('Y-m-d');
        $yesterdayInBranch = Carbon::now($branchTimezone)->subDay()->format('Y-m-d');

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

        $branch = $targetBranch;
        $employees = User::where('branch_id', $id)->where('role', '!=', 'admin')->where('is_active', true)
            ->with([
                'division',
                'attendances' => function ($q) use ($todayInBranch, $yesterdayInBranch) {
                    $q->whereDate('check_in_time', $todayInBranch)
                        ->orWhere(function ($sq) use ($todayInBranch, $yesterdayInBranch) {
                            $sq->whereDate('check_in_time', $yesterdayInBranch)->whereDate('check_out_time', $todayInBranch);
                        })
                        ->orderBy('check_in_time', 'desc');
                }
            ])->get();

        $attendanceGroups = ['Masuk' => [], 'Izin' => [], 'Sakit' => [], 'Cuti' => [], 'WFH / Dinas Luar' => [], 'Alpha / Belum Absen' => []];

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

            if ($attendance) {
                $attendanceGroups['Masuk'][] = $emp;
            } elseif ($todayLeave) {
                if ($todayLeave->type == 'sakit') $attendanceGroups['Sakit'][] = $emp;
                elseif ($todayLeave->type == 'izin') $attendanceGroups['Izin'][] = $emp;
                elseif ($todayLeave->type == 'cuti') $attendanceGroups['Cuti'][] = $emp;
                elseif ($todayLeave->type == 'wfh') $attendanceGroups['WFH / Dinas Luar'][] = $emp;
                else $attendanceGroups['Alpha / Belum Absen'][] = $emp;
            } else {
                $attendanceGroups['Alpha / Belum Absen'][] = $emp;
            }
        }

        $statsCounts = [
            'Masuk' => count($attendanceGroups['Masuk']),
            'Izin' => count($attendanceGroups['Izin']),
            'Sakit' => count($attendanceGroups['Sakit']),
            'Cuti' => count($attendanceGroups['Cuti']),
            'WFH / Dinas Luar' => count($attendanceGroups['WFH / Dinas Luar']),
            'Alpha / Belum Absen' => count($attendanceGroups['Alpha / Belum Absen']),
        ];

        return view('user_biasa.branch_detail', compact('branch', 'employees', 'attendanceGroups', 'statsCounts'));
    }

    // ... sisa method myBranches, showEmployeeHistory biarkan sama (hanya pastikan import class benar)
    // saya singkat untuk menghemat ruang, logic intinya ada di index() yang sudah saya ubah di atas.
    // Jika butuh Full 1 file utuh tanpa potongan sama sekali, beri tahu. Tapi logic index di atas sudah mencakup permintaan.

    public function myBranches()
    {
        $user = Auth::user();

        if (!in_array($user->role, ['audit', 'leader', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $myBranchIds = $user->branches()->pluck('branches.id')->toArray();
        if ($user->branch_id) {
            $myBranchIds[] = $user->branch_id;
        }
        if ($user->role == 'admin' && $user->branch_id == null) {
            $myBranchIds = Branch::pluck('id')->toArray();
        }
        $myBranchIds = array_filter(array_unique($myBranchIds));

        $controlledBranches = Branch::whereIn('id', $myBranchIds)
            ->withCount(['users' => function ($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('name', 'asc')
            ->get();

        foreach ($controlledBranches as $branch) {
            // Tentukan 'Hari Ini' berdasarkan Timezone Cabang
            $tz = $branch->timezone ?? 'Asia/Jakarta';
            $todayInBranch = Carbon::now($tz)->format('Y-m-d');
            $nowInBranch = Carbon::now($tz);

            // Load Users & Statistik manual per cabang dengan logika Lembur
            $users = User::where('branch_id', $branch->id)->where('is_active', true)
                ->with(['attendances' => function ($q) use ($todayInBranch, $nowInBranch) {
                    $q->where(function ($sq) use ($todayInBranch) {
                        $sq->whereDate('check_in_time', $todayInBranch);
                    })->orWhere(function ($sq) use ($nowInBranch) {
                        $sq->whereNull('check_out_time')
                            ->where('check_in_time', '>=', $nowInBranch->copy()->subHours(32));
                    });
                }, 'leaveRequests' => function ($q) use ($todayInBranch) {
                    $q->where('status', 'approved')
                        ->whereDate('start_date', '<=', $todayInBranch)
                        ->whereDate('end_date', '>=', $todayInBranch);
                }])->get();

            $hadir = 0;
            $sakit = 0;
            $izin_cuti = 0;
            $alpha = 0;

            foreach ($users as $u) {
                $att = $u->attendances->first();
                $leave = $u->leaveRequests->first();

                if ($att) {
                    $hadir++;
                } elseif ($leave) {
                    if ($leave->type == 'sakit') {
                        $sakit++;
                    } elseif ($leave->type == 'wfh') {
                        $hadir++;
                    } else {
                        $izin_cuti++;
                    }
                } else {
                    $alpha++;
                }
            }

            $branch->stats_today = [
                'hadir' => $hadir,
                'sakit' => $sakit,
                'izin' => $izin_cuti,
                'alpha' => $alpha
            ];
        }

        return view('team.my_branches', compact('controlledBranches'));
    }

    public function showEmployeeHistory(Request $request, $branchId, $employeeId)
    {
        $user = Auth::user();

        // Validasi Akses
        if ($user->role == 'audit') {
            $allowedBranches = $user->branches->pluck('id')->toArray();
            if ($user->branch_id) {
                $allowedBranches[] = $user->branch_id;
            }
            if (!in_array($branchId, $allowedBranches)) abort(403, 'Akses Ditolak.');
        } elseif ($user->role == 'leader') {
            if ($user->branch_id != $branchId) {
                $pivotIds = $user->branches->pluck('id')->toArray();
                if (!in_array($branchId, $pivotIds)) abort(403, 'Akses Ditolak.');
            }
        } elseif ($user->role == 'admin') {
            if ($user->branch_id && $user->branch_id != $branchId) abort(403);
        }

        $employee = User::with(['division', 'branch'])->findOrFail($employeeId);
        $selectedMonth = $request->get('month', date('m'));
        $selectedYear = $request->get('year', date('Y'));

        $currentDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1);
        $prevDate = $currentDate->copy()->subMonth();
        $nextDate = $currentDate->copy()->addMonth();
        $prevMonth = $prevDate->month;
        $prevYear = $prevDate->year;
        $nextMonth = $nextDate->month;
        $nextYear = $nextDate->year;

        $data = $this->getHistoryData($employee, $selectedMonth, $selectedYear);
        $history = $data['history'];
        $summary = $data['summary'];

        return view('attendance.history', compact('history', 'summary', 'selectedMonth', 'selectedYear', 'employee', 'prevMonth', 'prevYear', 'nextMonth', 'nextYear'));
    }

    private function getHistoryData($user, $selectedMonth, $selectedYear)
    {
        $attendances = Attendance::with('verifier')->where('user_id', $user->id)->whereYear('check_in_time', $selectedYear)->whereMonth('check_in_time', $selectedMonth)->orderBy('check_in_time', 'desc')->get();
        $leaves = LeaveRequest::where('user_id', $user->id)->where('status', 'approved')->where('is_active', true)
            ->where(function ($q) use ($selectedMonth, $selectedYear) {
                $q->whereMonth('start_date', $selectedMonth)->whereYear('start_date', $selectedYear)
                    ->orWhere(function ($subQ) use ($selectedMonth, $selectedYear) {
                        $subQ->whereMonth('end_date', $selectedMonth)->whereYear('end_date', $selectedYear);
                    });
            })->get();

        $historyCollection = $attendances;
        foreach ($leaves as $leave) {
            $startDate = Carbon::parse($leave->start_date);
            $endDate = $leave->end_date ? Carbon::parse($leave->end_date) : $startDate;
            $period = CarbonPeriod::create($startDate, $endDate);
            foreach ($period as $date) {
                if ($date->month == $selectedMonth && $date->year == $selectedYear) {
                    $alreadyAttendance = $attendances->filter(function ($att) use ($date) {
                        return $att->check_in_time->isSameDay($date);
                    })->isNotEmpty();
                    if (!$alreadyAttendance) {
                        $fakeAtt = new Attendance();
                        $fakeAtt->id = 'leave_' . $leave->id . '_' . $date->timestamp;
                        $fakeAtt->user_id = $user->id;
                        $fakeAtt->check_in_time = $date->copy()->setTime(8, 0, 0);
                        $fakeAtt->check_out_time = null;
                        $typeLabel = ucfirst($leave->type);
                        if ($leave->type == 'telat') $typeLabel = 'Izin Telat';
                        if ($leave->type == 'wfh') $typeLabel = 'WFH';
                        $fakeAtt->presence_status = $typeLabel;
                        $fakeAtt->status = 'verified';
                        $fakeAtt->attendance_type = 'leave';
                        $fakeAtt->is_late_checkin = false;
                        $fakeAtt->is_early_checkout = false;
                        $fakeAtt->photo_path = null;
                        $fakeAtt->photo_out_path = null;
                        $fakeAtt->audit_photo_path = null;
                        $fakeAtt->audit_note = "Pengajuan: " . $leave->reason;
                        $fakeAtt->setRelation('leaveRequest', $leave);
                        $historyCollection->push($fakeAtt);
                    }
                }
            }
        }
        $history = $historyCollection->sortByDesc('check_in_time');
        $summary = [
            'total' => $history->count(),
            'hadir' => $history->filter(function ($item) {
                $s = strtolower($item->presence_status ?? '');
                $isExplicitPresent = in_array($s, ['masuk', 'wfh', 'izin telat']) || str_contains($s, 'dinas');
                $isImplicitPresent = empty($s) && in_array($item->attendance_type, ['scan', 'self', 'manual']);
                return $isExplicitPresent || $isImplicitPresent;
            })->count(),
            'sakit' => $history->filter(function ($i) {
                return strtolower($i->presence_status ?? '') === 'sakit';
            })->count(),
            'izin' => $history->filter(function ($i) {
                return in_array(strtolower($i->presence_status ?? ''), ['izin', 'cuti']);
            })->count(),
            'alpha' => $history->filter(function ($i) {
                return strtolower($i->presence_status ?? '') === 'alpha';
            })->count(),
            'telat' => $history->where('is_late_checkin', true)->count(),
            'pulang_cepat' => $history->where('is_early_checkout', true)->count(),
            'pending' => $history->where('status', 'pending_verification')->count(),
        ];
        return ['history' => $history, 'summary' => $summary];
    }
}

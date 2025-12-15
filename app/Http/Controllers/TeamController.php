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
        
        // 1. Ambil ID Cabang yang dikontrol user
        $myBranchIds = $user->branches()->pluck('branches.id')->toArray();
        if ($user->branch_id) {
            $myBranchIds[] = $user->branch_id;
        }
        if ($user->role == 'admin' && $user->branch_id == null) {
            $myBranchIds = Branch::pluck('id')->toArray();
        }
        $myBranchIds = array_filter(array_unique($myBranchIds));

        // 2. Setup Timezone User (Untuk referensi dasar)
        $userTimezone = $user->branch->timezone ?? 'Asia/Jakarta';
        $todayInBranch = Carbon::now($userTimezone)->format('Y-m-d');
        $nowInBranch = Carbon::now($userTimezone);

        // 3. Query User Team
        $query = User::where('users.is_active', true);

        if (empty($myBranchIds)) {
            if (in_array($user->role, ['user_biasa', 'security'])) {
                $query->where('division_id', $user->division_id)->where('users.id', '!=', $user->id);
            } else {
                $query->where('users.id', 0); // Hide all if no branch
            }
        } else {
            $query->where(function ($q) use ($myBranchIds) {
                $q->whereIn('users.branch_id', $myBranchIds)
                    ->orWhereHas('branches', function ($subQ) use ($myBranchIds) {
                        $subQ->whereIn('branches.id', $myBranchIds);
                    });
            });
        }

        // 4. Eager Load dengan Logika "Habis Lembur"
        $myTeam = $query->with([
            'workSchedule',
            'attendances' => function ($q) use ($todayInBranch, $nowInBranch) {
                $q->where(function($sq) use ($todayInBranch) {
                    // A. Masuk Hari Ini
                    $sq->whereDate('check_in_time', $todayInBranch);
                })
                ->orWhere(function($sq) use ($todayInBranch) {
                    // B. Pulang Hari Ini (Tapi Masuk Kemarin) -> INI YANG MEMPERBAIKI STATUS
                    $sq->whereDate('check_out_time', $todayInBranch)
                       ->whereDate('check_in_time', '<', $todayInBranch);
                })
                ->orWhere(function($sq) use ($nowInBranch) {
                    // C. Masih Lembur (Belum Pulang dari Kemarin)
                    $sq->whereNull('check_out_time')
                       ->where('check_in_time', '>=', $nowInBranch->copy()->subHours(32));
                })
                ->orderBy('check_in_time', 'desc'); 
            },
            'leaveRequests' => function ($q) use ($todayInBranch) {
                $q->where('status', 'approved')
                  ->whereDate('start_date', '<=', $todayInBranch)
                  ->whereDate('end_date', '>=', $todayInBranch);
            },
            'activeLateStatus', 'divisions', 'branch'
        ])
        ->leftJoin('branches', 'users.branch_id', '=', 'branches.id')
        ->orderBy('branches.name', 'asc')->orderBy('users.name', 'asc')->select('users.*')->get();

        // 5. Data Statistik Sidebar (Opsional)
        $controlledBranches = Branch::whereIn('id', $myBranchIds)
            ->withCount(['users' => function ($q) { $q->where('is_active', true); }])
            ->orderBy('name', 'asc')->get();

        $assignedAudits = collect();
        if (!empty($myBranchIds)) {
            $assignedAudits = User::where('role', 'audit')->where('is_active', true)
                ->whereHas('branches', function($q) use ($myBranchIds) { $q->whereIn('branches.id', $myBranchIds); })->get();
        }

        // 6. Hitung Statistik Header
        $stats = [
            'total' => $myTeam->count(),
            'hadir' => 0,
            'izin_sakit' => 0,
            'belum_hadir' => 0
        ];

        foreach($myTeam as $member) {
            $att = $member->attendances->first(); // Ambil data attendance yg sudah difilter query diatas
            $leave = $member->leaveRequests->first();
            $isWfh = $leave && $leave->type == 'wfh';

            if ($att || $isWfh) {
                $stats['hadir']++;
            } elseif ($leave && in_array($leave->type, ['sakit', 'izin', 'cuti'])) {
                $stats['izin_sakit']++;
            }
        }
        
        $stats['belum_hadir'] = $stats['total'] - ($stats['hadir'] + $stats['izin_sakit']);
        if($stats['belum_hadir'] < 0) $stats['belum_hadir'] = 0;

        return view('user_biasa.team', compact('myTeam', 'myBranchIds', 'controlledBranches', 'assignedAudits', 'stats'));
    }

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

        // FIX ERROR CRASH: Menggunakan map() untuk memproses data
        // Agar stats_today benar-benar menempel pada object branch
        $controlledBranches = Branch::whereIn('id', $myBranchIds)
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($branch) {
                // Timezone per cabang
                $tz = $branch->timezone ?? 'Asia/Jakarta';
                $todayInBranch = Carbon::now($tz)->format('Y-m-d');
                $nowInBranch = Carbon::now($tz);

                // Hitung User Aktif
                $users = User::where('branch_id', $branch->id)->where('is_active', true)
                    ->with(['attendances' => function($q) use ($todayInBranch, $nowInBranch) {
                        // Logika Absensi yang SAMA dengan index()
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

                $branch->users_count = $users->count(); // Set manual count

                $hadir = 0; $sakit = 0; $izin_cuti = 0; $alpha = 0;

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

                // Pasang data ke object branch
                $branch->stats_today = [
                    'hadir' => $hadir,
                    'sakit' => $sakit,
                    'izin' => $izin_cuti,
                    'alpha' => $alpha
                ];

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
            if (!in_array($id, $allowedBranches)) abort(403, 'Akses Ditolak.');
        } elseif ($user->role == 'leader') {
            if ($user->branch_id != $id) {
                $pivotIds = $user->branches->pluck('id')->toArray();
                if(!in_array($id, $pivotIds)) abort(403, 'Akses Ditolak.');
            }
        } elseif ($user->role == 'admin') {
            if ($user->branch_id && $user->branch_id != $id) abort(403);
        }

        $branch = $targetBranch;
        
        $employees = User::where('branch_id', $id)->where('role', '!=', 'admin')->where('is_active', true)
            ->with(['division', 'attendances' => function ($q) use ($todayInBranch, $nowInBranch) { 
                $q->where(function($sq) use ($todayInBranch) {
                    $sq->whereDate('check_in_time', $todayInBranch);
                })
                ->orWhere(function($sq) use ($todayInBranch) {
                    $sq->whereDate('check_out_time', $todayInBranch)
                       ->whereDate('check_in_time', '<', $todayInBranch);
                })
                ->orWhere(function($sq) use ($nowInBranch) {
                    $sq->whereNull('check_out_time')
                       ->where('check_in_time', '>=', $nowInBranch->copy()->subHours(32));
                });
            }])->get();

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

            if ($attendance) { $attendanceGroups['Masuk'][] = $emp; }
            elseif ($todayLeave) {
                if ($todayLeave->type == 'sakit') $attendanceGroups['Sakit'][] = $emp;
                elseif ($todayLeave->type == 'izin') $attendanceGroups['Izin'][] = $emp;
                elseif ($todayLeave->type == 'cuti') $attendanceGroups['Cuti'][] = $emp;
                elseif ($todayLeave->type == 'wfh') $attendanceGroups['WFH / Dinas Luar'][] = $emp;
                else $attendanceGroups['Alpha / Belum Absen'][] = $emp;
            } else { $attendanceGroups['Alpha / Belum Absen'][] = $emp; }
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
        $leaves = LeaveRequest::where('user_id', $user->id)->where('status', 'approved')->where('is_active', true)->where(function ($q) use ($selectedMonth, $selectedYear) { $q->whereMonth('start_date', $selectedMonth)->whereYear('start_date', $selectedYear)->orWhere(function ($subQ) use ($selectedMonth, $selectedYear) { $subQ->whereMonth('end_date', $selectedMonth)->whereYear('end_date', $selectedYear); }); })->get();
        $historyCollection = $attendances; 
        return ['history' => $historyCollection, 'summary' => []];
    }
}
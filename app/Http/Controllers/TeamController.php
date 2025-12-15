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
        $myBranchIds = $user->branches()->pluck('branches.id')->toArray();
        if ($user->branch_id) {
            $myBranchIds[] = $user->branch_id;
        }
        if ($user->role == 'admin' && $user->branch_id == null) {
            $myBranchIds = Branch::pluck('id')->toArray();
        }
        $myBranchIds = array_filter(array_unique($myBranchIds));

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

        // [UPDATE LOGIC] Ambil Attendance Terakhir (Bisa hari ini ATAU kemarin yang belum checkout)
        $myTeam = $query->with([
            'workSchedule',
            'attendances' => function ($q) {
                $q->where(function($sq) {
                    $sq->whereDate('check_in_time', today()) // Masuk Hari ini
                       ->orWhere(function($sq2) {
                           // ATAU Masuk Kemarin TAPI Belum Pulang (Lembur Lintas Hari)
                           $sq2->whereNull('check_out_time')
                               ->where('check_in_time', '>=', Carbon::now()->subHours(24));
                       });
                })->latest('check_in_time');
            },
            'leaveRequests' => function ($q) {
                $q->where('status', 'approved')->whereDate('start_date', '<=', today())->whereDate('end_date', '>=', today());
            },
            'activeLateStatus', 'divisions', 'branch'
        ])
        ->leftJoin('branches', 'users.branch_id', '=', 'branches.id')
        ->orderBy('branches.name', 'asc')->orderBy('users.name', 'asc')->select('users.*')->get();

        $controlledBranches = Branch::whereIn('id', $myBranchIds)->withCount(['users' => function ($q) { $q->where('is_active', true); }])->orderBy('name', 'asc')->get();

        $assignedAudits = collect();
        if (!empty($myBranchIds)) {
            $assignedAudits = User::where('role', 'audit')->where('is_active', true)
                ->whereHas('branches', function($q) use ($myBranchIds) { $q->whereIn('branches.id', $myBranchIds); })->get();
        }

        // Statistik Sederhana
        $stats = [
            'total' => $myTeam->count(),
            'hadir' => $myTeam->filter(function($member) {
                $att = $member->attendances->first();
                $leave = $member->leaveRequests->first();
                $isWfh = $leave && $leave->type == 'wfh';
                // Hitung hadir jika ada data absen atau WFH
                return ($att) || $isWfh; 
            })->count(),
            'izin_sakit' => $myTeam->filter(function($member) {
                $leave = $member->leaveRequests->first();
                return $leave && in_array($leave->type, ['sakit', 'izin', 'cuti']);
            })->count(),
            'belum_hadir' => 0
        ];
        $stats['belum_hadir'] = $stats['total'] - ($stats['hadir'] + $stats['izin_sakit']);
        if($stats['belum_hadir'] < 0) $stats['belum_hadir'] = 0;

        return view('user_biasa.team', compact('myTeam', 'myBranchIds', 'controlledBranches', 'assignedAudits', 'stats'));
    }

    public function show(User $user) { return view('team.show', compact('user')); }

    public function attendance(User $user) {
        $attendances = Attendance::where('user_id', $user->id)->latest()->paginate(10);
        return view('team.attendance', compact('user', 'attendances'));
    }

    public function showBranch($id) {
        $user = Auth::user();

        if ($user->role == 'audit') {
            $allowedBranches = $user->branches->pluck('id')->toArray();
            if ($user->branch_id) {
                $allowedBranches[] = $user->branch_id;
            }
            if (!in_array($id, $allowedBranches)) abort(403, 'Akses Ditolak. Anda tidak memiliki akses ke cabang ini.');
        
        } elseif ($user->role == 'leader') {
            if ($user->branch_id != $id) {
                $pivotIds = $user->branches->pluck('id')->toArray();
                if(!in_array($id, $pivotIds)) abort(403, 'Akses Ditolak.');
            }
        } elseif ($user->role == 'admin') {
            if ($user->branch_id && $user->branch_id != $id) abort(403);
        }

        $branch = Branch::findOrFail($id);
        
        $employees = User::where('branch_id', $id)->where('role', '!=', 'admin')->where('is_active', true)
            ->with(['division', 'attendances' => function ($q) { $q->whereDate('check_in_time', today()); }])->get();

        $attendanceGroups = ['Masuk' => [], 'Izin' => [], 'Sakit' => [], 'Cuti' => [], 'WFH / Dinas Luar' => [], 'Alpha / Belum Absen' => []];

        foreach ($employees as $emp) {
            $todayLeave = LeaveRequest::where('user_id', $emp->id)->where('status', 'approved')->where('is_active', true)
                ->where(function ($q) {
                    $q->where(function ($sub) { $sub->whereIn('type', ['sakit', 'izin', 'cuti', 'wfh'])->whereDate('start_date', '<=', today())->whereDate('end_date', '>=', today()); })
                      ->orWhere(function ($sub) { $sub->where('type', 'telat')->whereDate('start_date', today()); });
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
            ->with(['users' => function($q) {
                $q->where('is_active', true)
                  ->select('id', 'branch_id', 'name') 
                  ->with(['attendances' => function($q2) {
                      $q2->whereDate('check_in_time', today())->select('user_id', 'check_in_time');
                  }, 'leaveRequests' => function($q3) {
                      $q3->where('status', 'approved')
                          ->whereDate('start_date', '<=', today())
                          ->whereDate('end_date', '>=', today())
                          ->select('user_id', 'type');
                  }]);
            }])
            ->withCount(['users' => function ($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('name', 'asc')
            ->get();

        foreach ($controlledBranches as $branch) {
            $hadir = 0;
            $sakit = 0;
            $izin_cuti = 0; 
            $alpha = 0;

            foreach ($branch->users as $user) {
                $att = $user->attendances->first();
                $leave = $user->leaveRequests->first();

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
}
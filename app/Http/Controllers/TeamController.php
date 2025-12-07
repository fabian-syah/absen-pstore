<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\Branch;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. KUMPULKAN SEMUA ID CABANG MILIK USER LOGIN
        $myBranchIds = $user->branches()->pluck('branches.id')->toArray();

        if ($user->branch_id) {
            $myBranchIds[] = $user->branch_id;
        }

        $myBranchIds = array_filter(array_unique($myBranchIds));

        // 2. QUERY USER (TIM)
        $query = User::where('users.is_active', true);

        if (empty($myBranchIds)) {
            $query->where('users.id', 0);
        } else {
            $query->where(function ($q) use ($myBranchIds) {
                $q->whereIn('users.branch_id', $myBranchIds)
                    ->orWhereHas('branches', function ($subQ) use ($myBranchIds) {
                        $subQ->whereIn('branches.id', $myBranchIds);
                    });
            });
        }

        // Ambil Data Tim
        $myTeam = $query->with([
            'workSchedule',
            // Ambil absensi hari ini
            'attendances' => function ($q) {
                $q->whereDate('check_in_time', today());
            },
            // Ambil Izin/Sakit/Cuti yang Approved hari ini
            'leaveRequests' => function ($q) {
                $q->where('status', 'approved')
                  ->whereDate('start_date', '<=', today())
                  ->whereDate('end_date', '>=', today());
            },
            'activeLateStatus',
            'divisions',
            'branch'
        ])
            ->join('branches', 'users.branch_id', '=', 'branches.id')
            ->orderBy('branches.name', 'asc')
            ->orderBy('users.name', 'asc')
            ->select('users.*')
            ->get();

        // 3. AMBIL DATA DETAIL CABANG
        $controlledBranches = Branch::whereIn('id', $myBranchIds)
            ->withCount(['users' => function ($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('name', 'asc')
            ->get();

        // 4. AMBIL DATA AUDIT YANG MEMEGANG CABANG INI
        $assignedAudits = collect();
        if (!empty($myBranchIds)) {
            $assignedAudits = User::where('role', 'audit')
                ->where('is_active', true)
                ->whereHas('branches', function($q) use ($myBranchIds) {
                    $q->whereIn('branches.id', $myBranchIds);
                })
                ->get();
        }

        // --- [BARU] LOGIKA STATISTIK SUMMARY ---
        $stats = [
            'total' => $myTeam->count(),
            'hadir' => $myTeam->filter(function($member) {
                // Logic: Dianggap hadir/online jika sudah absen masuk (dan belum pulang) ATAU sedang WFH
                $att = $member->attendances->first();
                $leave = $member->leaveRequests->first();
                $isWfh = $leave && $leave->type == 'wfh';
                
                // Jika ingin menghitung yang "Sudah Pulang" juga sebagai hadir, hapus bagian (!$att->check_out_time)
                // Disini saya set logicnya: Yang sedang aktif (Online) + Yang sudah pulang juga dihitung hadir hari ini
                return ($att) || $isWfh; 
            })->count(),
            'izin_sakit' => $myTeam->filter(function($member) {
                $leave = $member->leaveRequests->first();
                // Hitung sakit/izin/cuti (kecuali WFH karena WFH dihitung kerja)
                return $leave && in_array($leave->type, ['sakit', 'izin', 'cuti']);
            })->count(),
            'belum_hadir' => 0 // Inisialisasi
        ];

        // Hitung sisa yang belum hadir (Total - (Hadir + Izin/Sakit))
        // Note: Logic ini asumsi sederhana, bisa disesuaikan jika ada kondisi alpha
        $stats['belum_hadir'] = $stats['total'] - ($stats['hadir'] + $stats['izin_sakit']);
        if($stats['belum_hadir'] < 0) $stats['belum_hadir'] = 0; // Prevent negative

        return view('user_biasa.team', compact('myTeam', 'myBranchIds', 'controlledBranches', 'assignedAudits', 'stats'));
    }

    public function showBranch($id)
    {
        $user = Auth::user();

        // Validasi Akses
        if ($user->role == 'audit') {
            $allowedBranches = $user->branches->pluck('id')->toArray();
            if (!in_array($id, $allowedBranches)) abort(403);
        } elseif ($user->role == 'admin' && $user->branch_id) {
            if ($user->branch_id != $id) abort(403);
        }

        $branch = Branch::findOrFail($id);

        $employees = User::where('branch_id', $id)
            ->where('role', '!=', 'admin')
            ->where('is_active', true)
            ->with(['division', 'attendances' => function ($q) {
                $q->whereDate('check_in_time', today());
            }])
            ->get();

        foreach ($employees as $emp) {
            $todayLeave = LeaveRequest::where('user_id', $emp->id)
                ->where('status', 'approved')
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->where(function ($sub) {
                        $sub->whereIn('type', ['sakit', 'izin', 'cuti', 'wfh'])
                            ->whereDate('start_date', '<=', today())
                            ->whereDate('end_date', '>=', today());
                    })->orWhere(function ($sub) {
                        $sub->where('type', 'telat')
                            ->whereDate('start_date', today());
                    });
                })
                ->first();

            $emp->today_leave = $todayLeave;
        }

        return view('user_biasa.branch_detail', compact('branch', 'employees'));
    }

    public function myBranches()
    {
        $user = Auth::user();

        if ($user->role !== 'audit') {
            abort(403, 'Unauthorized action.');
        }

        $myId = $user->id;
        $myBranchIds = $user->branches()->pluck('branches.id')->toArray();

        if ($user->branch_id) {
            $myBranchIds[] = $user->branch_id;
        }

        $myBranchIds = array_filter(array_unique($myBranchIds));

        $controlledBranches = Branch::whereIn('id', $myBranchIds)
            ->withCount(['users' => function ($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('name', 'asc')
            ->get();

        return view('user_biasa.my_branches', compact('controlledBranches'));
    }

    public function showEmployeeHistory(Request $request, $branchId, $employeeId)
    {
        $user = Auth::user();

        if ($user->role == 'audit') {
            $allowedBranches = $user->branches->pluck('id')->toArray();
            if (!in_array($branchId, $allowedBranches)) {
                abort(403, 'Anda tidak memiliki akses ke cabang ini.');
            }
        } elseif ($user->role == 'admin' && $user->branch_id) {
            if ($user->branch_id != $branchId) {
                abort(403);
            }
        }

        $employee = User::with(['division', 'branch'])->findOrFail($employeeId);

        $selectedMonth = $request->get('month', date('m'));
        $selectedYear = $request->get('year', date('Y'));

        $currentDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1);
        $prevDate = $currentDate->copy()->subMonth();
        $nextDate = $currentDate->copy()->addMonth();

        $prevMonth = $prevDate->month;
        $prevYear  = $prevDate->year;
        $nextMonth = $nextDate->month;
        $nextYear  = $nextDate->year;

        $data = $this->getHistoryData($employee, $selectedMonth, $selectedYear);
        $history = $data['history'];
        $summary = $data['summary'];

        return view('attendance.history', compact(
            'history',
            'summary',
            'selectedMonth',
            'selectedYear',
            'employee',
            'prevMonth', 'prevYear',
            'nextMonth', 'nextYear'
        ));
    }

    private function getHistoryData($user, $selectedMonth, $selectedYear)
    {
        $attendances = Attendance::with('verifier') 
            ->where('user_id', $user->id)
            ->whereYear('check_in_time', $selectedYear)
            ->whereMonth('check_in_time', $selectedMonth)
            ->orderBy('check_in_time', 'desc')
            ->get();

        $leaves = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('is_active', true)
            ->where(function ($q) use ($selectedMonth, $selectedYear) {
                $q->whereMonth('start_date', $selectedMonth)->whereYear('start_date', $selectedYear)
                  ->orWhere(function ($subQ) use ($selectedMonth, $selectedYear) {
                      $subQ->whereMonth('end_date', $selectedMonth)->whereYear('end_date', $selectedYear);
                  });
            })
            ->get();

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
            'hadir' => $history->filter(function($item) {
                $s = strtolower($item->presence_status ?? '');
                $isExplicitPresent = in_array($s, ['masuk', 'wfh', 'izin telat']) || str_contains($s, 'dinas');
                $isImplicitPresent = empty($s) && in_array($item->attendance_type, ['scan', 'self', 'manual']);
                return $isExplicitPresent || $isImplicitPresent;
            })->count(),
            'sakit' => $history->filter(function($i) {
                return strtolower($i->presence_status ?? '') === 'sakit';
            })->count(),
            'izin' => $history->filter(function($i) {
                return in_array(strtolower($i->presence_status ?? ''), ['izin', 'cuti']);
            })->count(),
            'alpha' => $history->filter(function($i) {
                return strtolower($i->presence_status ?? '') === 'alpha';
            })->count(),
            'telat' => $history->where('is_late_checkin', true)->count(),
            'pulang_cepat' => $history->where('is_early_checkout', true)->count(),
            'pending' => $history->where('status', 'pending_verification')->count(),
        ];

        return ['history' => $history, 'summary' => $summary];
    }
}
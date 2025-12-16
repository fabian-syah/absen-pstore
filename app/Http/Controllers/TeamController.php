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

class TeamController extends Controller
{
    /**
     * Menampilkan daftar tim (Grouped by Timezone).
     */
    public function index()
    {
        $user = Auth::user();
        
        // 1. Tentukan Scope Cabang yang boleh dilihat
        $myBranchIds = $user->branches()->pluck('branches.id')->toArray();
        if ($user->branch_id) $myBranchIds[] = $user->branch_id;
        
        // Jika Admin Pusat (branch_id null), ambil semua cabang
        if ($user->role == 'admin' && $user->branch_id == null) {
            $myBranchIds = Branch::pluck('id')->toArray();
        }
        $myBranchIds = array_filter(array_unique($myBranchIds));

        // 2. Query Base User
        $query = User::where('users.is_active', true);

        if (empty($myBranchIds)) {
            // User Biasa / Security tanpa cabang khusus
            if (in_array($user->role, ['user_biasa', 'security'])) {
                $query->where('division_id', $user->division_id); 
            } elseif (in_array($user->role, ['audit', 'leader'])) {
                 $query->where('users.id', $user->id);
            } else {
                $query->where('users.id', 0); // Hide all
            }
        } else {
            // User dengan Cabang (Admin/Leader/Audit)
            $query->where(function ($q) use ($myBranchIds) {
                $q->whereIn('users.branch_id', $myBranchIds)
                    ->orWhereHas('branches', function ($subQ) use ($myBranchIds) {
                        $subQ->whereIn('branches.id', $myBranchIds);
                    });
            });
            if ($user->role == 'user_biasa') {
                $query->where('division_id', $user->division_id);
            }
        }

        // 3. Eager Load Data (Ambil banyak data, filter di PHP)
        $allMembers = $query->with([
            'workSchedule',
            'attendances' => function ($q) {
                $q->latest('check_in_time')->limit(10); 
            },
            'leaveRequests' => function ($q) {
                $q->where('status', 'approved')
                  ->where('end_date', '>=', Carbon::now()->subDays(2)->format('Y-m-d'));
            },
            'activeLateStatus', 'divisions', 'branch'
        ])
        ->leftJoin('branches', 'users.branch_id', '=', 'branches.id')
        ->orderByRaw("CASE WHEN users.id = {$user->id} THEN 0 ELSE 1 END") 
        ->orderBy('branches.timezone', 'asc') 
        ->orderBy('branches.name', 'asc')
        ->orderBy('users.name', 'asc')
        ->select('users.*')
        ->get();

        // 4. Proses Logika Per-Member (Sesuai Timezone Masing-masing)
        $stats = [
            'total' => $allMembers->count(),
            'hadir' => 0, 
            'izin_sakit' => 0, 
            'belum_hadir' => 0, 
            'lembur' => 0
        ];

        foreach($allMembers as $member) {
            // A. Tentukan Timezone Spesifik Member Ini
            // Ini PENTING: Semua kalkulasi waktu harus pakai timezone user TARGET, bukan Admin.
            $tz = $member->branch->timezone ?? 'Asia/Jakarta';
            $nowInTz = Carbon::now($tz);
            $todayDate = $nowInTz->format('Y-m-d');

            // B. Filter Attendance yang Cocok
            // Kita cari SATU absen yang paling relevan untuk "status saat ini"
            $validAttendance = $member->attendances->first(function ($att) use ($tz, $todayDate, $nowInTz) {
                $checkIn = Carbon::parse($att->check_in_time)->setTimezone($tz);
                $checkOut = $att->check_out_time ? Carbon::parse($att->check_out_time)->setTimezone($tz) : null;
                $cInDate = $checkIn->format('Y-m-d');

                // Case 1: Masuk Hari Ini (Absen Normal)
                if ($cInDate === $todayDate) return true;

                // Case 2: Pulang Hari Ini (Masuk Kemarin = Habis Lembur)
                if ($checkOut && $checkOut->format('Y-m-d') === $todayDate) return true;

                // Case 3: Sedang Lembur (Masuk Kemarin, Belum Pulang, Selisih < 48 jam)
                if (!$checkOut && $checkIn->diffInHours($nowInTz) < 48 && $cInDate < $todayDate) return true;

                return false;
            });

            // Set ke relation agar view tinggal pakai
            $member->setRelation('attendances', $validAttendance ? collect([$validAttendance]) : collect([]));

            // C. Filter Cuti
            $validLeave = $member->leaveRequests->first(function ($leave) use ($todayDate) {
                return $leave->start_date <= $todayDate && $leave->end_date >= $todayDate;
            });
            $member->setRelation('leaveRequests', $validLeave ? collect([$validLeave]) : collect([]));

            // D. TENTUKAN STATUS FINAL (Untuk Statistik & View Helper)
            // Kita inject attribute 'custom_status' ke object member biar View tidak mikir keras
            $status = 'belum_hadir'; // Default

            if ($validAttendance) {
                $cIn = Carbon::parse($validAttendance->check_in_time)->setTimezone($tz);
                $cOut = $validAttendance->check_out_time ? Carbon::parse($validAttendance->check_out_time)->setTimezone($tz) : null;
                $cInDate = $cIn->format('Y-m-d');

                if ($cInDate === $todayDate) {
                    if ($cOut) $status = 'pulang';
                    else $status = 'hadir'; // Sedang bekerja normal
                } else {
                    // Tanggal Masuk BUKAN hari ini
                    if ($cOut) $status = 'habis_lembur'; // Pulang hari ini, masuk kemarin
                    else $status = 'sedang_lembur'; // Belum pulang, masuk kemarin
                }
            } elseif ($validLeave) {
                if ($validLeave->type == 'wfh') $status = 'wfh';
                elseif (in_array($validLeave->type, ['sakit', 'izin', 'cuti'])) $status = 'izin_sakit';
            }

            // Inject status ke member untuk kemudahan di View
            $member->attendance_status = $status; 

            // E. Update Counter Statistik
            if (in_array($status, ['hadir', 'pulang', 'wfh'])) {
                $stats['hadir']++;
            } elseif (in_array($status, ['sedang_lembur', 'habis_lembur'])) {
                $stats['hadir']++; // Tetap dihitung hadir secara umum
                $stats['lembur']++; // Counter khusus lembur
            } elseif ($status == 'izin_sakit') {
                $stats['izin_sakit']++;
            } else {
                $stats['belum_hadir']++;
            }
        }

        // 5. Grouping Berdasarkan Timezone
        $groupedTeam = $allMembers->groupBy(function ($item) {
            return $item->branch->timezone ?? 'Asia/Jakarta';
        });

        // 6. Data Sidebar (Tetap)
        $controlledBranches = Branch::whereIn('id', $myBranchIds)
            ->withCount(['users' => function ($q) { $q->where('is_active', true); }])
            ->orderBy('name', 'asc')->get();

        $assignedAudits = collect();
        if (!empty($myBranchIds)) {
            $assignedAudits = User::where('role', 'audit')->where('is_active', true)
                ->whereHas('branches', function($q) use ($myBranchIds) { $q->whereIn('branches.id', $myBranchIds); })->get();
        }

        return view('user_biasa.team', compact('groupedTeam', 'myBranchIds', 'controlledBranches', 'assignedAudits', 'stats'));
    }

    // --- FUNCTION LAIN SAMA SEPERTI SEBELUMNYA ---
    public function myBranches()
    {
        $user = Auth::user();
        if (!in_array($user->role, ['audit', 'leader', 'admin'])) abort(403, 'Unauthorized action.');

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
                    ->with(['attendances' => function($q) { $q->latest('check_in_time')->limit(5); }, 
                            'leaveRequests' => function($q) use ($todayInBranch) {
                                $q->where('status', 'approved')->whereDate('start_date', '<=', $todayInBranch)->whereDate('end_date', '>=', $todayInBranch);
                            }])->get();

                $branch->users_count = $users->count();
                $hadir = 0; $sakit = 0; $izin_cuti = 0; $alpha = 0; $lembur = 0;

                foreach ($users as $u) {
                    // Logic Filter yang SAMA dengan index
                    $att = $u->attendances->first(function ($a) use ($todayInBranch, $nowInBranch, $tz) {
                        $cIn = Carbon::parse($a->check_in_time)->setTimezone($tz);
                        $cOut = $a->check_out_time ? Carbon::parse($a->check_out_time)->setTimezone($tz) : null;
                        if ($cIn->format('Y-m-d') === $todayInBranch) return true;
                        if ($cOut && $cOut->format('Y-m-d') === $todayInBranch) return true;
                        if (!$cOut && $cIn->diffInHours($nowInBranch) < 48 && $cIn->format('Y-m-d') < $todayInBranch) return true;
                        return false;
                    });
                    
                    $leave = $u->leaveRequests->first();
                    $isOvertime = false;
                    if ($att) {
                        $cIn = Carbon::parse($att->check_in_time)->setTimezone($tz)->format('Y-m-d');
                        if ($cIn !== $todayInBranch) { $isOvertime = true; $lembur++; }
                    }

                    if ($att || $isOvertime) $hadir++;
                    elseif ($leave) {
                        if ($leave->type == 'sakit') $sakit++; elseif ($leave->type == 'wfh') $hadir++; else $izin_cuti++;
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
            if (!in_array($id, $allowedBranches)) abort(403, 'Akses Ditolak.');
        } elseif ($user->role == 'leader') {
            if ($user->branch_id != $id && !in_array($id, $user->branches->pluck('id')->toArray())) abort(403, 'Akses Ditolak.');
        } elseif ($user->role == 'admin') {
            if ($user->branch_id && $user->branch_id != $id) abort(403);
        }

        $branch = $targetBranch;
        $employees = User::where('branch_id', $id)->where('role', '!=', 'admin')->where('is_active', true)
            ->with(['division', 'attendances' => function ($q) { $q->latest('check_in_time')->limit(10); }])->get();

        $attendanceGroups = ['Masuk' => [], 'Izin' => [], 'Sakit' => [], 'Cuti' => [], 'WFH / Dinas Luar' => [], 'Alpha / Belum Absen' => [], 'Lembur' => []];

        foreach ($employees as $emp) {
            $todayLeave = LeaveRequest::where('user_id', $emp->id)->where('status', 'approved')->where('is_active', true)
                ->where(function ($q) use ($todayInBranch) {
                    $q->where(function ($sub) use ($todayInBranch) { 
                        $sub->whereIn('type', ['sakit', 'izin', 'cuti', 'wfh'])->whereDate('start_date', '<=', $todayInBranch)->whereDate('end_date', '>=', $todayInBranch); 
                    })->orWhere(function ($sub) use ($todayInBranch) { 
                        $sub->where('type', 'telat')->whereDate('start_date', $todayInBranch); 
                    });
                })->first();
            $emp->today_leave = $todayLeave;
            
            // Logic Filter SAMA
            $attendance = $emp->attendances->first(function ($a) use ($todayInBranch, $branchTimezone, $nowInBranch) {
                $cIn = Carbon::parse($a->check_in_time)->setTimezone($branchTimezone);
                $cOut = $a->check_out_time ? Carbon::parse($a->check_out_time)->setTimezone($branchTimezone) : null;
                if ($cIn->format('Y-m-d') === $todayInBranch) return true;
                if ($cOut && $cOut->format('Y-m-d') === $todayInBranch) return true;
                if (!$cOut && $cIn->diffInHours($nowInBranch) < 48 && $cIn->format('Y-m-d') < $todayInBranch) return true;
                return false;
            });

            $isOvertime = false;
            if ($attendance) {
                $checkInDate = Carbon::parse($attendance->check_in_time)->setTimezone($branchTimezone)->format('Y-m-d');
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
            'Masuk' => count($attendanceGroups['Masuk']), 'Izin' => count($attendanceGroups['Izin']), 'Sakit' => count($attendanceGroups['Sakit']),
            'Cuti' => count($attendanceGroups['Cuti']), 'WFH / Dinas Luar' => count($attendanceGroups['WFH / Dinas Luar']),
            'Alpha / Belum Absen' => count($attendanceGroups['Alpha / Belum Absen']), 'Lembur' => count($attendanceGroups['Lembur']),
        ];

        return view('user_biasa.branch_detail', compact('branch', 'employees', 'attendanceGroups', 'statsCounts'));
    }

    public function showEmployeeHistory(Request $request, $branchId, $employeeId) {
        $employee = User::with(['division', 'branch'])->findOrFail($employeeId);
        $selectedMonth = $request->get('month', date('m'));
        $selectedYear = $request->get('year', date('Y'));
        $data = $this->getHistoryData($employee, $selectedMonth, $selectedYear);
        return view('attendance.history', array_merge($data, [
            'selectedMonth' => $selectedMonth, 'selectedYear' => $selectedYear, 'employee' => $employee,
            'prevMonth' => Carbon::createFromDate($selectedYear, $selectedMonth, 1)->subMonth()->month,
            'prevYear' => Carbon::createFromDate($selectedYear, $selectedMonth, 1)->subMonth()->year,
            'nextMonth' => Carbon::createFromDate($selectedYear, $selectedMonth, 1)->addMonth()->month,
            'nextYear' => Carbon::createFromDate($selectedYear, $selectedMonth, 1)->addMonth()->year,
        ]));
    }

    private function getHistoryData($user, $selectedMonth, $selectedYear) {
        $attendances = Attendance::with('verifier')->where('user_id', $user->id)->whereYear('check_in_time', $selectedYear)->whereMonth('check_in_time', $selectedMonth)->orderBy('check_in_time', 'desc')->get();
        foreach ($attendances as $attendance) {
            $checkInDate = Carbon::parse($attendance->check_in_time)->format('Y-m-d');
            $checkOutDate = $attendance->check_out_time ? Carbon::parse($attendance->check_out_time)->format('Y-m-d') : null;
            $attendance->is_overtime = ($checkOutDate && $checkInDate !== $checkOutDate);
            if($attendance->is_overtime) $attendance->overtime_duration = Carbon::parse($attendance->check_in_time)->diff(Carbon::parse($attendance->check_out_time));
        }
        return ['history' => $attendances, 'summary' => []];
    }
}
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

        // 2. Query Base User (Tanpa filter tanggal ketat di SQL)
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
            // Opsi: Jika User Biasa, batasi hanya divisinya saja meskipun punya branch_id
            if ($user->role == 'user_biasa') {
                $query->where('division_id', $user->division_id);
            }
        }

        // 3. Eager Load Data
        // NOTE: Kita ambil 10 absen terakhir & cuti yang aktif.
        // Filter "Hari Ini" dilakukan di PHP Loop agar sesuai Timezone masing-masing member.
        $allMembers = $query->with([
            'workSchedule',
            'attendances' => function ($q) {
                $q->latest('check_in_time')->limit(10); 
            },
            'leaveRequests' => function ($q) {
                // Ambil cuti yang overlap dengan hari ini/kemarin
                $q->where('status', 'approved')
                  ->where('end_date', '>=', Carbon::now()->subDays(2)->format('Y-m-d'));
            },
            'activeLateStatus', 'divisions', 'branch'
        ])
        ->leftJoin('branches', 'users.branch_id', '=', 'branches.id')
        // Urutkan: Diri Sendiri -> Timezone -> Nama Cabang -> Nama User
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
            $memberTz = $member->branch->timezone ?? 'Asia/Jakarta';
            $todayInMemberTz = Carbon::now($memberTz)->format('Y-m-d');
            $nowInMemberTz = Carbon::now($memberTz);

            // B. Filter Attendance yang Cocok untuk "Hari Ini" Member Tersebut
            $validAttendance = $member->attendances->first(function ($att) use ($memberTz, $todayInMemberTz, $nowInMemberTz) {
                $checkIn = Carbon::parse($att->check_in_time)->setTimezone($memberTz);
                $checkOut = $att->check_out_time ? Carbon::parse($att->check_out_time)->setTimezone($memberTz) : null;
                
                $checkInDate = $checkIn->format('Y-m-d');

                // Kondisi 1: Masuk Hari Ini (Normal)
                if ($checkInDate === $todayInMemberTz) return true;

                // Kondisi 2: Pulang Hari Ini TAPI Masuk Kemarin (Habis Lembur)
                if ($checkOut && $checkOut->format('Y-m-d') === $todayInMemberTz) return true;

                // Kondisi 3: Masih Aktif dari Kemarin (Sedang Lembur Lintas Hari)
                // Syarat: Belum checkout DAN durasi checkin < 48 jam (mencegah data hantu bulan lalu)
                if (!$checkOut && $checkIn->diffInHours($nowInMemberTz) < 48 && $checkInDate < $todayInMemberTz) return true;

                return false;
            });

            // Replace relation collection dengan single object hasil filter (atau kosong)
            $member->setRelation('attendances', $validAttendance ? collect([$validAttendance]) : collect([]));

            // C. Filter Cuti yang Cocok untuk Hari Ini
            $validLeave = $member->leaveRequests->first(function ($leave) use ($todayInMemberTz) {
                return $leave->start_date <= $todayInMemberTz && $leave->end_date >= $todayInMemberTz;
            });
            $member->setRelation('leaveRequests', $validLeave ? collect([$validLeave]) : collect([]));

            // D. Hitung Statistik Global
            $att = $validAttendance;
            $leave = $validLeave;
            $isWfh = $leave && $leave->type == 'wfh';
            $isOvertime = false;

            if ($att) {
                $cIn = Carbon::parse($att->check_in_time)->setTimezone($memberTz)->format('Y-m-d');
                // Jika tanggal checkin BUKAN hari ini, berarti itu lembur lintas hari
                if ($cIn !== $todayInMemberTz) {
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

        // 5. Grouping Berdasarkan Timezone untuk View
        // Hasilnya: Collection ['Asia/Jakarta' => [User, User], 'Asia/Jayapura' => [User]]
        $groupedTeam = $allMembers->groupBy(function ($item) {
            return $item->branch->timezone ?? 'Asia/Jakarta';
        });

        // 6. Data Sidebar (Cabang & Audit)
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

    /**
     * Menampilkan daftar cabang di Sidebar/Menu "Cabang Saya".
     * Logic ini juga harus timezone-aware per cabang.
     */
    public function myBranches()
    {
        $user = Auth::user();

        if (!in_array($user->role, ['audit', 'leader', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $myBranchIds = $user->branches()->pluck('branches.id')->toArray();
        if ($user->branch_id) $myBranchIds[] = $user->branch_id;
        
        if ($user->role == 'admin' && $user->branch_id == null) {
            $myBranchIds = Branch::pluck('id')->toArray(); 
        }
        $myBranchIds = array_filter(array_unique($myBranchIds));

        $controlledBranches = Branch::whereIn('id', $myBranchIds)
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($branch) {
                // Tentukan Timezone Cabang Ini
                $tz = $branch->timezone ?? 'Asia/Jakarta';
                $todayInBranch = Carbon::now($tz)->format('Y-m-d');
                $nowInBranch = Carbon::now($tz);

                // Ambil user aktif di cabang ini
                $users = User::where('branch_id', $branch->id)->where('is_active', true)
                    ->with(['attendances' => function($q) {
                        $q->latest('check_in_time')->limit(5);
                    }, 'leaveRequests' => function($q) use ($todayInBranch) {
                        $q->where('status', 'approved')
                          ->whereDate('start_date', '<=', $todayInBranch)
                          ->whereDate('end_date', '>=', $todayInBranch);
                    }])->get();

                $branch->users_count = $users->count();

                $hadir = 0; $sakit = 0; $izin_cuti = 0; $alpha = 0; $lembur = 0;

                foreach ($users as $u) {
                    // Filter Absensi Valid (Sama seperti index)
                    $att = $u->attendances->first(function ($a) use ($todayInBranch, $nowInBranch, $tz) {
                        $cIn = Carbon::parse($a->check_in_time)->setTimezone($tz);
                        $cOut = $a->check_out_time ? Carbon::parse($a->check_out_time)->setTimezone($tz) : null;
                        
                        // Masuk Hari Ini
                        if ($cIn->format('Y-m-d') === $todayInBranch) return true;
                        // Pulang Hari Ini
                        if ($cOut && $cOut->format('Y-m-d') === $todayInBranch) return true;
                        // Sedang Lembur
                        if (!$cOut && $cIn->diffInHours($nowInBranch) < 48 && $cIn->format('Y-m-d') < $todayInBranch) return true;
                        
                        return false;
                    });
                    
                    $leave = $u->leaveRequests->first();
                    $isOvertime = false;

                    if ($att) {
                        $checkInDate = Carbon::parse($att->check_in_time)->setTimezone($tz)->format('Y-m-d');
                        if ($checkInDate !== $todayInBranch) {
                            $isOvertime = true;
                            $lembur++;
                        }
                    }

                    if ($att || $isOvertime) {
                        $hadir++;
                    } elseif ($leave) {
                        if ($leave->type == 'sakit') $sakit++;
                        elseif ($leave->type == 'wfh') $hadir++; 
                        else $izin_cuti++; 
                    } else {
                        $alpha++; 
                    }
                }

                $branch->stats_today = [
                    'hadir' => $hadir, 'sakit' => $sakit, 'izin' => $izin_cuti,
                    'alpha' => $alpha, 'lembur' => $lembur
                ];
                return $branch;
            });

        return view('team.my_branches', compact('controlledBranches'));
    }

    /**
     * Menampilkan Detail Satu User
     */
    public function show(User $user) { 
        return view('team.show', compact('user')); 
    }

    /**
     * Menampilkan History Absensi User
     */
    public function attendance(User $user) {
        $attendances = Attendance::where('user_id', $user->id)->latest()->paginate(10);
        return view('team.attendance', compact('user', 'attendances'));
    }

    /**
     * Menampilkan Detail Satu Cabang (Isi Karyawan)
     */
    public function showBranch($id) {
        $user = Auth::user();
        $targetBranch = Branch::findOrFail($id);
        
        // Timezone Cabang Target
        $branchTimezone = $targetBranch->timezone ?? 'Asia/Jakarta';
        $todayInBranch = Carbon::now($branchTimezone)->format('Y-m-d');
        $nowInBranch = Carbon::now($branchTimezone);

        // Validasi Akses
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
        
        // Ambil Karyawan di Cabang Ini
        $employees = User::where('branch_id', $id)->where('role', '!=', 'admin')->where('is_active', true)
            ->with(['division', 'attendances' => function ($q) { 
                $q->latest('check_in_time')->limit(10);
            }])->get();

        $attendanceGroups = [
            'Masuk' => [], 'Izin' => [], 'Sakit' => [], 'Cuti' => [], 
            'WFH / Dinas Luar' => [], 'Alpha / Belum Absen' => [], 'Lembur' => []
        ];

        foreach ($employees as $emp) {
            // Cek Cuti
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
            
            // Cek Absensi (Logic sama dengan index, tapi timezone fix ke cabang ini)
            $attendance = $emp->attendances->first(function ($a) use ($todayInBranch, $nowInBranch, $branchTimezone) {
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
                if ($checkInDate !== $todayInBranch) {
                    $isOvertime = true;
                }
            }

            // Grouping Logic
            if ($isOvertime) {
                $attendanceGroups['Lembur'][] = $emp;
            } elseif ($attendance) { 
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
            'Lembur' => count($attendanceGroups['Lembur']),
        ];

        return view('user_biasa.branch_detail', compact('branch', 'employees', 'attendanceGroups', 'statsCounts'));
    }

    /**
     * History Bulanan per Employee
     */
    public function showEmployeeHistory(Request $request, $branchId, $employeeId) {
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

    private function getHistoryData($user, $selectedMonth, $selectedYear) {
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
            })->get();
        
        // Identifikasi Lembur pada History
        // NOTE: Untuk history, timezone biasanya mengikuti preferensi viewer atau disimpan UTC.
        // Di sini kita bandingkan tanggal checkin vs checkout secara raw.
        foreach ($attendances as $attendance) {
            $checkInDate = Carbon::parse($attendance->check_in_time)->format('Y-m-d');
            $checkOutDate = $attendance->check_out_time ? Carbon::parse($attendance->check_out_time)->format('Y-m-d') : null;
            
            $attendance->is_overtime = false;
            if ($checkOutDate && $checkInDate !== $checkOutDate) {
                $attendance->is_overtime = true;
                $attendance->overtime_duration = Carbon::parse($attendance->check_in_time)->diff(Carbon::parse($attendance->check_out_time));
            }
        }
        
        return ['history' => $attendances, 'summary' => []];
    }
}
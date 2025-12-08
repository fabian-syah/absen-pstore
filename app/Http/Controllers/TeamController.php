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
    /**
     * Menampilkan daftar semua anggota tim (bisa lintas cabang untuk Audit/Admin)
     */
    public function index()
    {
        $user = Auth::user();

        // 1. KUMPULKAN SEMUA ID CABANG MILIK USER LOGIN
        // Ambil dari pivot table (untuk Audit)
        $myBranchIds = $user->branches()->pluck('branches.id')->toArray();

        // Ambil dari branch_id user itu sendiri (untuk Leader/Admin)
        if ($user->branch_id) {
            $myBranchIds[] = $user->branch_id;
        }

        // Bersihkan array dari duplikat & nilai kosong
        $myBranchIds = array_filter(array_unique($myBranchIds));

        // 2. QUERY USER (TIM)
        $query = User::where('users.is_active', true);

        if (empty($myBranchIds)) {
            // Jika user tidak punya cabang, jangan tampilkan apa-apa (kecuali Admin Global mungkin)
            // Di sini kita set ID 0 agar result kosong
            $query->where('users.id', 0);
        } else {
            // Filter user yang branch_id-nya ada di list cabang kita
            // ATAU user yang punya akses ke cabang tersebut (via pivot)
            $query->where(function ($q) use ($myBranchIds) {
                $q->whereIn('users.branch_id', $myBranchIds)
                    ->orWhereHas('branches', function ($subQ) use ($myBranchIds) {
                        $subQ->whereIn('branches.id', $myBranchIds);
                    });
            });
        }

        // Ambil Data Tim dengan Relasi yang dibutuhkan
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

        // --- LOGIKA STATISTIK SUMMARY ---
        $stats = [
            'total' => $myTeam->count(),
            'hadir' => $myTeam->filter(function($member) {
                // Logic: Dianggap hadir/online jika sudah absen masuk ATAU sedang WFH
                $att = $member->attendances->first();
                $leave = $member->leaveRequests->first();
                $isWfh = $leave && $leave->type == 'wfh';
                
                // Hadir = Ada data absen atau status WFH
                return ($att) || $isWfh; 
            })->count(),
            'izin_sakit' => $myTeam->filter(function($member) {
                $leave = $member->leaveRequests->first();
                // Hitung sakit/izin/cuti (kecuali WFH karena WFH dihitung kerja/hadir)
                return $leave && in_array($leave->type, ['sakit', 'izin', 'cuti']);
            })->count(),
            'belum_hadir' => 0 // Inisialisasi
        ];

        // Hitung sisa yang belum hadir (Total - (Hadir + Izin/Sakit))
        $stats['belum_hadir'] = $stats['total'] - ($stats['hadir'] + $stats['izin_sakit']);
        if($stats['belum_hadir'] < 0) $stats['belum_hadir'] = 0; // Prevent negative

        return view('user_biasa.team', compact('myTeam', 'myBranchIds', 'controlledBranches', 'assignedAudits', 'stats'));
    }

    /**
     * Menampilkan Detail Satu Cabang
     */
    public function showBranch($id)
    {
        $user = Auth::user();

        // --- VALIDASI AKSES (Updated untuk Leader) ---
        if ($user->role == 'audit') {
            $allowedBranches = $user->branches->pluck('id')->toArray();
            if (!in_array($id, $allowedBranches)) abort(403, 'Akses Ditolak: Bukan wilayah audit Anda.');
        } 
        elseif ($user->role == 'leader') {
            // Leader hanya boleh lihat cabangnya sendiri
            if ($user->branch_id != $id) {
                // Cek juga pivot jika Leader memegang lebih dari 1 cabang (opsional)
                $pivotIds = $user->branches->pluck('id')->toArray();
                if(!in_array($id, $pivotIds)) abort(403, 'Akses Ditolak: Bukan cabang Anda.');
            }
        }
        elseif ($user->role == 'admin' && $user->branch_id) {
            // Admin cabang
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

    /**
     * Menampilkan Halaman "Cabang Saya" (List Cabang yang dikelola)
     * Diakses oleh: Audit & Leader
     */
    public function myBranches()
    {
        $user = Auth::user();

        // [UPDATED] Izinkan Leader mengakses method ini
        if (!in_array($user->role, ['audit', 'leader'])) {
            abort(403, 'Unauthorized action.');
        }

        $myId = $user->id;
        
        // Ambil cabang dari Pivot (biasanya Audit)
        $myBranchIds = $user->branches()->pluck('branches.id')->toArray();

        // Ambil cabang dari ID sendiri (biasanya Leader)
        if ($user->branch_id) {
            $myBranchIds[] = $user->branch_id;
        }

        $myBranchIds = array_filter(array_unique($myBranchIds));

        // Ambil Data Cabang berdasarkan ID yang dikumpulkan
        $controlledBranches = Branch::whereIn('id', $myBranchIds)
            ->withCount(['users' => function ($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('name', 'asc')
            ->get();

        return view('user_biasa.my_branches', compact('controlledBranches'));
    }

    /**
     * Menampilkan History Karyawan Tertentu
     */
    public function showEmployeeHistory(Request $request, $branchId, $employeeId)
    {
        $user = Auth::user();

        // --- VALIDASI AKSES (Updated untuk Leader) ---
        if ($user->role == 'audit') {
            $allowedBranches = $user->branches->pluck('id')->toArray();
            if (!in_array($branchId, $allowedBranches)) {
                abort(403, 'Anda tidak memiliki akses ke cabang ini.');
            }
        } 
        elseif ($user->role == 'leader') {
             // Leader Validasi
             if ($user->branch_id != $branchId) {
                $pivotIds = $user->branches->pluck('id')->toArray();
                if(!in_array($branchId, $pivotIds)) abort(403, 'Anda tidak memiliki akses ke cabang ini.');
            }
        }
        elseif ($user->role == 'admin' && $user->branch_id) {
            if ($user->branch_id != $branchId) {
                abort(403);
            }
        }

        $employee = User::with(['division', 'branch'])->findOrFail($employeeId);

        // Filter Tanggal
        $selectedMonth = $request->get('month', date('m'));
        $selectedYear = $request->get('year', date('Y'));

        $currentDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1);
        $prevDate = $currentDate->copy()->subMonth();
        $nextDate = $currentDate->copy()->addMonth();

        $prevMonth = $prevDate->month;
        $prevYear  = $prevDate->year;
        $nextMonth = $nextDate->month;
        $nextYear  = $nextDate->year;

        // Ambil Data History
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

    /**
     * Helper Function: Mengambil dan mengolah data absensi + cuti menjadi history timeline
     */
    private function getHistoryData($user, $selectedMonth, $selectedYear)
    {
        // 1. Ambil data Absensi Real (Scan/Self/Manual)
        $attendances = Attendance::with('verifier') 
            ->where('user_id', $user->id)
            ->whereYear('check_in_time', $selectedYear)
            ->whereMonth('check_in_time', $selectedMonth)
            ->orderBy('check_in_time', 'desc')
            ->get();

        // 2. Ambil data Cuti/Izin Approved
        $leaves = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('is_active', true)
            ->where(function ($q) use ($selectedMonth, $selectedYear) {
                // Cek overlap tanggal dengan bulan yang dipilih
                $q->whereMonth('start_date', $selectedMonth)->whereYear('start_date', $selectedYear)
                  ->orWhere(function ($subQ) use ($selectedMonth, $selectedYear) {
                      $subQ->whereMonth('end_date', $selectedMonth)->whereYear('end_date', $selectedYear);
                  });
            })
            ->get();

        $historyCollection = $attendances;

        // 3. Gabungkan Cuti ke dalam History
        foreach ($leaves as $leave) {
            $startDate = Carbon::parse($leave->start_date);
            $endDate = $leave->end_date ? Carbon::parse($leave->end_date) : $startDate;
            $period = CarbonPeriod::create($startDate, $endDate);

            foreach ($period as $date) {
                if ($date->month == $selectedMonth && $date->year == $selectedYear) {
                    
                    // Cek apakah tanggal ini sudah ada data absensi?
                    $alreadyAttendance = $attendances->filter(function ($att) use ($date) {
                        return $att->check_in_time->isSameDay($date);
                    })->isNotEmpty();

                    // Jika belum ada absen, buat object Absensi Palsu (Dummy) untuk tampilan history
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

        // 4. Sortir ulang berdasarkan tanggal terbaru
        $history = $historyCollection->sortByDesc('check_in_time');

        // 5. Hitung Summary
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
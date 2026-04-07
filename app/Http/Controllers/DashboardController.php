<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Division;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PDF;
use App\Traits\SendFcmNotification; // Import Trait

use App\Jobs\SendAuditNotificationJob;

class DashboardController extends Controller
{
    use SendFcmNotification; // Use Trait

    public function testNotification()
    {
        $user = Auth::user();

        // 1. Debug Target Audience
        $roles = ['admin', 'audit']; // Broadcast ke semua
        $branchId = null; // Force Global (Ignore Branch)

        $targetQuery = User::whereIn('role', $roles)->whereNotNull('fcm_token');
        if ($branchId) {
            $targetQuery->where('branch_id', $branchId);
        }
        $candidates = $targetQuery->get();

        $tokens = $candidates->pluck('fcm_token')->unique()->values();

        $debugInfo = [
            'requester' => $user->name,
            'role' => $user->role,
            'branch_id' => $branchId,
            'target_candidates_count' => $candidates->count(),
            'tokens_found' => $tokens->count(),
            'tokens' => $tokens->take(5)->toArray(), // Show max 5 tokens
            'config_api_key_check' => substr(config('services.firebase.api_key'), 0, 8) . '...'
        ];

        // Jika tidak ada token, langsung lapor
        if ($tokens->isEmpty()) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Tidak ada token FCM ditemukan. Cek apakah Anda sudah punya branch_id yang sesuai?',
                'debug' => $debugInfo
            ], 404);
        }

        // 2. Send Direct Notification (Sync)
        $title = "Test Broadcast Global";
        $body = "Tes notif ke semua Admin & Audit. By: " . $user->name . " @ " . now()->toTimeString();

        try {
            // Group user by Branch ID supaya bisa pakai Trait secara "legal"
            $groupedByBranch = $candidates->groupBy('branch_id');
            $sentCount = 0;
            $allResponses = [];

            foreach ($groupedByBranch as $branchId => $users) {
                // Panggil trait untuk branch ini (abaikan filter di dalam trait karena logic kita sudah ensure user exist)
                $responses = $this->sendNotificationToBranchRoles($roles, $branchId, $title, $body);
                if (is_array($responses)) {
                    $allResponses = array_merge($allResponses, $responses);
                }
                $sentCount++;
            }

            return response()->json([
                'status' => 'SUCCESS',
                'message' => "Broadcast dijalankan ke $sentCount kelompok branch.",
                'debug' => $debugInfo,
                'fcm_responses' => $allResponses,
                'note' => 'Cek log server jika masih gagal.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'EXCEPTION',
                'message' => $e->getMessage(),
                'debug' => $debugInfo
            ], 500);
        }
    }
    public function index()
    {
        $user = Auth::user();
        $data = [];
        $branch_id = $user->branch_id;

        // [TIMEZONE & LIVE CLOCK SETUP]
        // Jika Admin / Audit, maka pakai WIB (Asia/Jakarta)
        if (in_array($user->role, ['admin', 'audit'])) {
            $userTimezone = 'Asia/Jakarta';
        } else {
            $userTimezone = $user->branch?->timezone ?? 'Asia/Jakarta';
        }
        $data['current_timezone'] = $userTimezone;

        $todayInBranch = Carbon::now($userTimezone)->format('Y-m-d');
        $nowInBranch = Carbon::now($userTimezone);

        // =========================================================================
        // 1. LOGIKA ID CARD
        // =========================================================================
        $hireDate = $user->hire_date ? Carbon::parse($user->hire_date) : Carbon::now();
        $birthDate = $user->birth_date ? Carbon::parse($user->birth_date) : Carbon::parse('1999-05-12');

        $yyMasuk = $hireDate->format('y');
        $mmMasuk = $hireDate->format('m');
        $yyLahir = $birthDate->format('y');
        $mmLahir = $birthDate->format('m');
        $ddLahir = $birthDate->format('d');
        $noUrut = str_pad($user->id, 3, '0', STR_PAD_LEFT);

        $data['idCardNumber'] = "{$yyMasuk}{$mmMasuk}{$yyLahir} {$mmLahir}{$ddLahir}{$noUrut}";

        // =========================================================================
        // 2. LOGIKA JADWAL KERJA
        // =========================================================================
        $scheduleText = 'Fleksibel / Bebas';
        if ($user->check_in_start && $user->check_out_start) {
            $start = \Carbon\Carbon::parse($user->check_in_start)->format('H:i');
            $end = \Carbon\Carbon::parse($user->check_out_start)->format('H:i');
            $scheduleText = "$start - $end";
        } elseif ($user->workSchedule) {
            $start = \Carbon\Carbon::parse($user->workSchedule->start_time)->format('H:i');
            $end = \Carbon\Carbon::parse($user->workSchedule->end_time)->format('H:i');
            $scheduleText = "$start - $end";
        } else {
            $todaysAttendance = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', $todayInBranch)
                ->first();
            if ($todaysAttendance && $todaysAttendance->scheduled_check_in && $todaysAttendance->scheduled_check_out) {
                $start = \Carbon\Carbon::parse($todaysAttendance->scheduled_check_in)->format('H:i');
                $end = \Carbon\Carbon::parse($todaysAttendance->scheduled_check_out)->format('H:i');
                $scheduleText = "$start - $end (Terekam)";
            }
        }
        $data['todaySchedule'] = $scheduleText;

        // =========================================================================
        // 3. LOGIKA MULTI-BRANCH
        // =========================================================================
        $allBranchIds = [];
        if ($user->branch_id) {
            $allBranchIds[] = $user->branch_id;
        }
        $extraBranches = $user->branches()->pluck('branches.id')->toArray();
        $allBranchIds = array_merge($allBranchIds, $extraBranches);
        $allBranchIds = array_values(array_unique($allBranchIds));

        // =========================================================================
        // 4. DATA IZIN & ABSENSI PERSONAL
        // =========================================================================
        $data['myLeaveToday'] = $this->getTodayLeaveRequest($user->id, $todayInBranch, 'approved');
        $data['myPendingLeave'] = $this->getTodayLeaveRequest($user->id, $todayInBranch, 'pending');

        // A. Cek Sesi Aktif (Masuk tapi belum Pulang)
        $activeSession = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->where('check_in_time', '>=', $nowInBranch->copy()->subHours(24))
            ->where('check_in_time', '<=', $nowInBranch) // FIX: Jangan ambil data masa depan (misal Libur besok)
            ->where('attendance_type', '!=', 'leave') // <--- FIX: Jangan ambil record izin sebagai sesi aktif
            ->latest('check_in_time')
            ->first();

        // B. Cek Sesi Selesai Hari Ini
        $finishedSessionToday = Attendance::where('user_id', $user->id)
            ->whereDate('check_in_time', $todayInBranch)
            ->whereNotNull('check_out_time')
            ->latest('check_in_time')
            ->first();

        // C. Cek Sesi Lembur Lintas Hari (Pulang hari ini tapi masuk kemarin)
        $lastOvertimeSession = Attendance::where('user_id', $user->id)
            ->whereDate('check_in_time', '<', $todayInBranch)
            ->whereDate('check_out_time', $todayInBranch)
            ->latest('check_out_time')
            ->first();

        // Adjust Timezone for Display
        if ($activeSession) {
            $activeSession->check_in_time = Carbon::parse($activeSession->check_in_time)->timezone($userTimezone);
        }
        if ($finishedSessionToday) {
            $finishedSessionToday->check_in_time = Carbon::parse($finishedSessionToday->check_in_time)->timezone($userTimezone);
            $finishedSessionToday->check_out_time = Carbon::parse($finishedSessionToday->check_out_time)->timezone($userTimezone);
        }
        if ($lastOvertimeSession) {
            $lastOvertimeSession->check_out_time = Carbon::parse($lastOvertimeSession->check_out_time)->timezone($userTimezone);
        }

        // Logic Penentuan Status Dashboard
        $data['myAttendanceToday'] = null;
        $data['justFinishedOvertime'] = false;
        $data['isStillWorkingOvertime'] = false;
        $data['overtimeDuration'] = null;

        if ($activeSession) {
            $checkInDate = $activeSession->check_in_time->format('Y-m-d');
            if ($checkInDate !== $todayInBranch) {
                $data['isStillWorkingOvertime'] = true;
                $data['myAttendanceToday'] = $activeSession;
                $data['overtimeDuration'] = $activeSession->check_in_time->diff($nowInBranch);
            } else {
                $data['myAttendanceToday'] = $activeSession;
            }
        } elseif ($finishedSessionToday) {
            $data['myAttendanceToday'] = $finishedSessionToday;
        } elseif ($lastOvertimeSession) {
            $data['justFinishedOvertime'] = true;
            $data['lastOvertimeSession'] = $lastOvertimeSession;
        }

        $data['myPendingCount'] = Attendance::where('user_id', $user->id)->where('status', 'pending_verification')->count();
        $data['myTeamCount'] = User::where('division_id', $user->division_id)->where('id', '!=', $user->id)->count();

        $personalStats = $this->getUserAttendanceStats($user->id, $branch_id);

        // =========================================================================
        // 5. DATA UNTUK WIDGET & LEADERBOARD (FIXED LOGIC)
        // =========================================================================

        // --- LEADERBOARD ABSENSI (SINKRON DENGAN BRANCH LEADERBOARD) ---
        if ($user->role != 'security') {
            $data['leaderboard'] = Attendance::select(
                'user_id',
                DB::raw('count(*) as total_attendance'),
                DB::raw('SEC_TO_TIME(AVG(TIME_TO_SEC(TIME(check_in_time)))) as avg_arrival_time'),
                DB::raw('SUM(COALESCE(TIMESTAMPDIFF(SECOND, check_in_time, check_out_time), 0)) as total_work_seconds')
            )
                ->whereMonth('check_in_time', $nowInBranch->month)
                ->whereYear('check_in_time', $nowInBranch->year)
                ->where('status', 'verified')
                // Filter status yang hanya dianggap sebagai "Hadir"
                ->whereIn('presence_status', [
                    'Masuk',
                    'Hadir',
                    'Tepat Waktu',
                    'WFH',
                    'Work From Home',
                    'WFH / Dinas Luar',
                    'Dinas Luar',
                    'Kunjungan Rutin',
                    'Lembur',
                    'Telat',
                    'Izin Telat'
                ])
                /** * PERBAIKAN: 
                 * 1. Hapus whereTime '!=', '00:00:00' agar WFH Fabian (jam 00:00) terhitung.
                 * 2. Filter branch_id dipindah ke dalam whereHas user agar akurat dengan posisi karyawan sekarang.
                 */
                // SESUDAH (Samakan dengan BranchLeaderboardController)
                ->whereHas('user', function ($q) use ($user, $allBranchIds) {
                    $q->where('is_active', true)
                        ->whereNotIn('role', ['admin', 'security']); // Admin & Security dilarang masuk ranking umum
    
                    // Jika bukan admin, hanya tampilkan leaderboard dari cabang yang diakses user
                    if ($user->role !== 'admin') {
                        $q->whereIn('branch_id', $allBranchIds);
                    }
                })
                ->groupBy('user_id')
                ->orderBy('total_attendance', 'desc')
                ->orderBy('total_work_seconds', 'desc')
                ->orderBy('avg_arrival_time', 'asc')
                ->take(3)
                ->with(['user', 'user.division', 'user.branch'])
                ->get()
                ->map(function ($item) {
                    $item->avg_arrival_display = Carbon::parse($item->avg_arrival_time)->format('H:i');
                    return $item;
                });
        }

        // --- LEADERBOARD SCANNER (SECURITY & ADMIN) ---
        if ($user->role == 'admin' || $user->role == 'security') {
            $securityUsersQuery = User::where('is_active', true)->whereIn('role', ['security', 'admin']);

            if ($user->role != 'admin') {
                $securityUsersQuery->whereIn('branch_id', $allBranchIds);
            } elseif ($user->role == 'admin' && $branch_id != null) {
                $securityUsersQuery->where('branch_id', $branch_id);
            }

            $securityUsers = $securityUsersQuery->get();

            $scanners = $securityUsers->map(function ($sec) use ($nowInBranch) {
                $scanIn = Attendance::where('scanned_by_user_id', $sec->id)
                    ->whereMonth('check_in_time', $nowInBranch->month)
                    ->whereYear('check_in_time', $nowInBranch->year)
                    ->count();

                $scanOut = Attendance::where('scanned_out_by_user_id', $sec->id)
                    ->whereMonth('check_in_time', $nowInBranch->month)
                    ->whereYear('check_in_time', $nowInBranch->year)
                    ->count();

                $sec->total_scans = $scanIn + $scanOut;
                return $sec;
            });

            $data['topScanners'] = $scanners->sortByDesc('total_scans')->take(3)->values();
        }

        // =========================================================================
        // [BARU] LOGIKA GALLERY NOSTALGIA BULANAN
        // Hanya tampilkan 1 hari sebelum akhir bulan
        // =========================================================================

        // Cek apakah sekarang adalah 1 hari sebelum akhir bulan
        $lastDayOfMonth = $nowInBranch->copy()->endOfMonth()->day;
        $currentDay = $nowInBranch->day;
        $isOneDayBeforeEndOfMonth = ($currentDay == $lastDayOfMonth - 1);

        $data['showGallery'] = $isOneDayBeforeEndOfMonth;

        // Ambil data gallery hanya jika sudah waktunya ditampilkan
        if ($isOneDayBeforeEndOfMonth) {
            $data['attendanceGallery'] = Attendance::where('user_id', $user->id)
                ->whereMonth('check_in_time', $nowInBranch->month)
                ->whereYear('check_in_time', $nowInBranch->year)
                ->where(function ($q) {
                    $q->whereNotNull('photo_path')
                        ->orWhereNotNull('photo_out_path');
                })
                ->orderBy('check_in_time', 'asc')
                ->get();
        } else {
            $data['attendanceGallery'] = collect(); // Empty collection
        }

        $data['currentMonthName'] = $nowInBranch->translatedFormat('F Y');

        // =========================================================================
        // 6. DASHBOARD WIDGETS LOGIC
        // =========================================================================
        $attendanceQuery = Attendance::query();
        $userQuery = User::query();

        if ($user->role == 'admin') {
            if ($branch_id != null) {
                $attendanceQuery->where('branch_id', $branch_id);
                $userQuery->where('branch_id', $branch_id);
            }
            $data['totalUsers'] = (clone $userQuery)->where('role', '!=', 'admin')->where('is_active', true)->count();
            $data['totalBranches'] = $branch_id ? 1 : Branch::count();
            $data['attendancesToday'] = (clone $attendanceQuery)->whereDate('check_in_time', $todayInBranch)->count();
            $data['pendingVerifications'] = (clone $attendanceQuery)->where('status', 'pending_verification')->count();

            $leaveQuery = LeaveRequest::where('is_active', true)
                ->whereIn('status', ['pending', 'approved'])
                ->where(function ($query) use ($todayInBranch) {
                    $query->where(function ($q) use ($todayInBranch) {
                        $q->whereIn('type', ['libur'])
                            ->whereDate('start_date', '<=', $todayInBranch)
                            ->whereDate('end_date', '>=', $todayInBranch);
                    })->orWhere(function ($q) use ($todayInBranch) {
                        $q->where('type', 'telat')
                            ->whereDate('start_date', $todayInBranch);
                    });
                });

            if ($branch_id != null) {
                $leaveQuery->whereHas('user', function ($q) use ($branch_id) {
                    $q->where('branch_id', $branch_id);
                });
            }

            $data['leavesToday'] = $leaveQuery->count();

            $data['stats'] = $this->getAdminAttendanceStats($branch_id, $todayInBranch);
        } elseif ($user->role == 'audit') {
            $data['pendingVerifications'] = Attendance::whereIn('branch_id', $allBranchIds)->where('status', 'pending_verification')->count();
            $data['pendingLeaves'] = LeaveRequest::where('status', 'pending')->where('is_active', true)
                ->whereHas('user', function ($q) use ($allBranchIds) {
                    $q->whereIn('branch_id', $allBranchIds);
                })->count();
            $data['attendancesToday'] = Attendance::whereIn('branch_id', $allBranchIds)->whereDate('check_in_time', $todayInBranch)->count();
            $data['stats'] = $this->getAuditAttendanceStats($allBranchIds, $todayInBranch);
        } elseif ($user->role == 'security') {
            $data['myScansToday'] = Attendance::where('scanned_by_user_id', $user->id)->whereDate('check_in_time', $todayInBranch)->count();
            $data['totalUsers'] = User::whereIn('branch_id', $allBranchIds)->whereIn('role', ['user_biasa', 'leader'])->where('is_active', true)->count();
            $data['stats'] = $this->getSecurityAttendanceStats($user->id, $allBranchIds, $todayInBranch);
        } else {
            $data['stats'] = $personalStats;
        }

        if (!isset($data['attendanceStats'])) {
            $data['attendanceStats'] = isset($data['stats']) ? $data['stats'] : $personalStats;
        }
        if (!isset($data['leaderboard']))
            $data['leaderboard'] = [];
        if (!isset($data['topScanners']))
            $data['topScanners'] = [];

        // =========================================================================
        // 7. ULANG TAHUN LOGIC
        // =========================================================================
        $userBirthDate = $user->birth_date ? Carbon::parse($user->birth_date) : null;
        $birthdayData = null;

        if ($userBirthDate) {
            $nextBirthday = Carbon::createFromDate($nowInBranch->year, $userBirthDate->month, $userBirthDate->day, $userTimezone)->startOfDay();
            if ($nextBirthday->isPast() && !$nextBirthday->isSameDay($nowInBranch->startOfDay())) {
                $nextBirthday->addYear();
            }
            $diffInDays = $nowInBranch->startOfDay()->diffInDays($nextBirthday, false);
            $isToday = $nextBirthday->isSameDay($nowInBranch->startOfDay());
            if ($diffInDays <= 30) {
                $birthdayData = [
                    'is_today' => $isToday,
                    'days_left' => $diffInDays,
                    'date' => $nextBirthday->format('Y-m-d'),
                    'age_to_be' => $nextBirthday->year - $userBirthDate->year
                ];
            }
        }
        $data['birthdayData'] = $birthdayData;

        // =========================================================================
// =========================================================================
// [FIXED] LOGIKA HALL OF FAME: HITUNG LIVE PER CABANG
// =========================================================================
        $lastMonth = $nowInBranch->copy()->subMonth();
        $data['lastMonthName'] = $lastMonth->translatedFormat('F Y');

        // Kita hitung langsung dari tabel Attendance agar angka sinkron dengan Riwayat Absensi
        $data['lastMonthWinners'] = Attendance::select(
            'user_id',
            DB::raw('count(*) as total_attendance')
        )
            ->whereMonth('check_in_time', $lastMonth->month)
            ->whereYear('check_in_time', $lastMonth->year)
            // FILTER PER CABANG: Hanya ambil karyawan yang satu cabang dengan user yang login
            ->where('branch_id', $user->branch_id)
            ->where('status', 'verified')
            // Filter status yang dianggap "Masuk" (Sama dengan logic Riwayat Absensi)
            ->whereIn('presence_status', [
                'Masuk',
                'Hadir',
                'Tepat Waktu',
                'WFH',
                'Work From Home',
                'WFH / Dinas Luar',
                'Dinas Luar',
                'Kunjungan Rutin',
                'Lembur',
                'Telat',
                'Izin Telat'
            ])
            ->whereHas('user', function ($q) {
                $q->where('is_active', true)
                    ->whereNotIn('role', ['admin', 'security']); // Admin & Security tidak masuk Hall of Fame
            })
            ->groupBy('user_id')
            ->orderByDesc('total_attendance') // Urutkan dari yang masuk paling banyak
            ->limit(3) // Ambil Top 3
            ->with(['user', 'user.division'])
            ->get()
            ->map(function ($winner, $key) {
                // Tambahkan property rank secara manual agar Blade tidak error
                $winner->rank = $key + 1;
                return $winner;
            });

        // ... Logika Birthday yang sudah ada ...



        // [NEW] Logic Scanner Winner Prize
        $data['isScannerWinner'] = false;
        $data['prizeClaimed'] = (bool)($user->metadata['prize_claimed_at'] ?? false);
        
        if ($user->role == 'security' || $user->role == 'admin') {
            $lastMonth = now()->subMonth();
            $topScanner = Attendance::select('scanned_by_user_id', DB::raw('count(*) as total_scans'))
                ->whereMonth('check_in_time', $lastMonth->month)
                ->whereYear('check_in_time', $lastMonth->year)
                ->whereNotNull('scanned_by_user_id')
                ->groupBy('scanned_by_user_id')
                ->orderByDesc('total_scans')
                ->first();

            if ($topScanner && $topScanner->scanned_by_user_id == $user->id) {
                $data['isScannerWinner'] = true;
                $data['totalLastMonthScans'] = $topScanner->total_scans;
            }
        }

        // =========================================================================
        // [NEW] LOGIKA KALENDER TIM (DASHBOARD)
        // =========================================================================
        if (in_array($user->role, ['admin', 'audit', 'leader'])) {
            $month = request('month', $nowInBranch->month);
            $year = request('year', $nowInBranch->year);
            $startDate = Carbon::create($year, $month, 1, 0, 0, 0, $userTimezone)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
    
            $teamQuery = User::where('is_active', true);
            if ($user->role !== 'admin') {
                $teamQuery->whereIn('branch_id', $allBranchIds);
            }
            $teamMembers = $teamQuery->with('branch', 'division')->orderBy('name')->get();
            
            $tzMap = $teamMembers->pluck('branch.timezone', 'id')->map(function($tz) {
                return $tz ?: 'Asia/Jakarta';
            });
            
            $calendarAttendances = Attendance::whereIn('user_id', $teamMembers->pluck('id'))
                ->whereBetween('check_in_time', [$startDate, $endDate])
                ->where('presence_status', '!=', 'Alpha')
                ->get()
                ->groupBy(['user_id', function($item) use ($tzMap) {
                    $tz = $tzMap[$item->user_id] ?? 'Asia/Jakarta';
                    return Carbon::parse($item->check_in_time)->timezone($tz)->format('Y-m-d');
                }]);
                
            $calendarLeaves = LeaveRequest::whereIn('user_id', $teamMembers->pluck('id'))
                ->where('status', 'approved')
                ->where(function($q) use ($startDate, $endDate) {
                    $q->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function($sub) use ($startDate, $endDate) {
                          $sub->where('start_date', '<', $startDate)
                              ->where('end_date', '>', $endDate);
                      });
                })->get()
                ->map(function($leave) {
                    // Create date range for easier lookup
                    $start = Carbon::parse($leave->start_date);
                    $end = $leave->end_date ? Carbon::parse($leave->end_date) : $start;
                    $leave->range = collect();
                    for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                        $leave->range->push($d->format('Y-m-d'));
                    }
                    return $leave;
                })
                ->groupBy('user_id');

            $data['teamCalendar'] = [
                'members' => $teamMembers,
                'attendances' => $calendarAttendances,
                'leaves' => $calendarLeaves,
                'daysInMonth' => $startDate->daysInMonth,
                'startDate' => $startDate,
                'currentMonth' => $month,
                'currentYear' => $year
            ];
        }

        return view('dashboard', $data);
    }

    /**
     * [NEW] Klaim Hadiah Leaderboard
     */
    public function claimPrize(Request $request)
    {
        $user = Auth::user();
        
        // Simpan status klaim ke metadata JSON
        $metadata = $user->metadata ?? [];
        $metadata['prize_claimed_at'] = now()->toDateTimeString();
        
        // Gunakan forceFill jika kolom tidak ada di fillable, atau update biasa jika ada
        $user->forceFill(['metadata' => $metadata])->save();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Hadiah 1jt akan dikirim ke e-wallet Anda oleh Admin!'
        ]);
    }

    private function getTodayLeaveRequest($user_id, $todayDate, $status = 'approved')
    {
        return LeaveRequest::where('user_id', $user_id)
            ->where('is_active', true)
            ->where('status', $status)
            ->where(function ($query) use ($todayDate) {
                $query->where(function ($q) use ($todayDate) {
                    $q->whereIn('type', ['sakit', 'izin', 'cuti', 'wfh', 'libur'])
                        ->whereDate('start_date', '<=', $todayDate)
                        ->whereDate('end_date', '>=', $todayDate);
                })->orWhere(function ($q) use ($todayDate) {
                    $q->where('type', 'telat')
                        ->whereDate('start_date', $todayDate);
                });
            })
            ->latest()
            ->first();
    }

    private function getAdminAttendanceStats($branch_id = null, $todayDate)
    {
        $query = Attendance::whereDate('check_in_time', $todayDate)->where('presence_status', '!=', 'Alpha');
        if ($branch_id)
            $query->where('branch_id', $branch_id);
        $totalUsers = User::when($branch_id, function ($q) use ($branch_id) {
            return $q->where('branch_id', $branch_id);
        })->where('role', '!=', 'admin')->where('is_active', true)->count();

        return $this->calculateStats($query, $totalUsers);
    }

    private function getAuditAttendanceStats($branchIds, $todayDate)
    {
        $query = Attendance::whereDate('check_in_time', $todayDate)->where('presence_status', '!=', 'Alpha');
        if (!empty($branchIds)) {
            $query->whereIn('branch_id', is_array($branchIds) ? $branchIds : [$branchIds]);
        }
        $totalToday = (clone $query)->count();
        $verified = (clone $query)->whereNotNull('verified_by_user_id')->count();
        $pending = (clone $query)->where('status', 'pending_verification')->count();
        $late = (clone $query)->where('is_late_checkin', true)->count();

        return [
            'total' => $totalToday,
            'verified' => $verified,
            'pending' => $pending,
            'late' => $late,
            'verified_percentage' => $totalToday > 0 ? round(($verified / $totalToday) * 100) : 0,
            'pending_percentage' => $totalToday > 0 ? round(($pending / $totalToday) * 100) : 0,
            'late_percentage' => $totalToday > 0 ? round(($late / $totalToday) * 100) : 0,
        ];
    }

    private function getSecurityAttendanceStats($security_id, $branchIds, $todayDate)
    {
        $query = Attendance::whereDate('check_in_time', $todayDate);
        if (!empty($branchIds)) {
            $query->whereIn('branch_id', is_array($branchIds) ? $branchIds : [$branchIds]);
        }
        $scanQuery = (clone $query)->where('attendance_type', 'scan');
        $checkInScans = (clone $scanQuery)->count();
        $checkOutScans = (clone $scanQuery)->whereNotNull('check_out_time')->count();

        return [
            'total_scans' => $checkInScans + $checkOutScans,
            'check_in_scans' => $checkInScans,
            'check_out_scans' => $checkOutScans,
            'check_in_percentage' => 100,
            'check_out_percentage' => 100,
        ];
    }

    private function getUserAttendanceStats($user_id, $branch_id = null)
    {
        $query = Attendance::where('user_id', $user_id)
            ->whereMonth('check_in_time', Carbon::now()->month)
            ->whereYear('check_in_time', Carbon::now()->year)
            ->where('presence_status', '!=', 'Alpha');
        if ($branch_id)
            $query->where('branch_id', $branch_id);
        return $this->calculateStats($query, 0);
    }

    private function calculateStats($query, $totalUsers)
    {
        $presentCount = (clone $query)->count();
        $lateCount = (clone $query)->where('is_late_checkin', true)->count();
        $earlyCount = (clone $query)->where('is_early_checkout', true)->count();
        $pendingCount = (clone $query)->where('status', 'pending_verification')->count();
        $onTimeCount = max($presentCount - $lateCount, 0);
        $divider = $totalUsers > 0 ? $totalUsers : ($presentCount > 0 ? $presentCount : 1);
        $absentCount = max($totalUsers - $presentCount, 0);

        return [
            'total' => $presentCount,
            'present' => $presentCount,
            'late' => $lateCount,
            'early' => $earlyCount,
            'pending' => $pendingCount,
            'on_time' => $onTimeCount,
            'absent' => $absentCount,
            'present_percentage' => round(($presentCount / $divider) * 100),
            'late_percentage' => $presentCount > 0 ? round(($lateCount / $presentCount) * 100) : 0,
            'on_time_percentage' => $presentCount > 0 ? round(($onTimeCount / $presentCount) * 100) : 0,
            'pending_percentage' => $presentCount > 0 ? round(($pendingCount / $presentCount) * 100) : 0,
            'absent_percentage' => $totalUsers > 0 ? round(($absentCount / $totalUsers) * 100) : 0,
        ];
    }

    public function getStats(Request $request)
    {
        $securityUser = Auth::user();
        $today = today();
        $stats = [
            'total_scans_today' => Attendance::where('scanned_by_user_id', $securityUser->id)->whereDate('updated_at', $today)->count(),
            'check_in_count' => Attendance::where('scanned_by_user_id', $securityUser->id)->whereDate('check_in_time', $today)->whereNotNull('check_in_time')->count(),
            'check_out_count' => Attendance::where(function ($q) use ($securityUser, $today) {
                $q->where('scanned_by_user_id', $securityUser->id);
            })->whereDate('check_out_time', $today)->whereNotNull('check_out_time')->count(),
            'late_count' => Attendance::where('scanned_by_user_id', $securityUser->id)->whereDate('check_in_time', $today)->where('is_late_checkin', true)->count(),
        ];
        return response()->json(['status' => 'success', 'data' => $stats]);
    }

    public function getRecentActivities(Request $request)
    {
        return response()->json([]);
    }

    public function getAttendanceChart(Request $request)
    {
        return response()->json([]);
    }

    public function exportAttendancePDF(Request $request)
    {
        $user = Auth::user();
        $branch_id = $user->branch_id;
        $userTimezone = $user->branch?->timezone ?? 'Asia/Jakarta';
        $todayInBranch = Carbon::now($userTimezone)->format('Y-m-d');

        $allBranchIds = [];
        if ($user->branch_id)
            $allBranchIds[] = $user->branch_id;
        $extraBranches = $user->branches()->pluck('branches.id')->toArray();
        $allBranchIds = array_merge($allBranchIds, $extraBranches);
        $allBranchIds = array_values(array_unique($allBranchIds));

        $date = $request->get('date', $todayInBranch);
        $data = [];
        $data['user'] = $user;
        $data['export_date'] = now()->format('d-m-Y H:i:s');
        $data['period'] = $date;

        switch ($user->role) {
            case 'admin':
                $data['stats'] = $this->getAdminAttendanceStats($branch_id, $date);
                $data['title'] = 'Laporan Statistik Harian (Admin)';
                $data['role'] = 'Admin';
                break;
            case 'audit':
                $data['stats'] = $this->getAuditAttendanceStats($allBranchIds, $date);
                $data['title'] = 'Laporan Verifikasi Absensi';
                $data['role'] = 'Audit';
                break;
            case 'security':
                $data['stats'] = $this->getSecurityAttendanceStats($user->id, $allBranchIds, $date);
                $data['title'] = 'Laporan Aktivitas Security';
                $data['role'] = 'Security';
                break;
            default:
                $data['stats'] = $this->getUserAttendanceStats($user->id, $branch_id);
                $data['title'] = 'Laporan Absensi Personal (Bulan Ini)';
                $data['role'] = 'Karyawan';
                break;
        }
        $pdf = PDF::loadView('pdf.attendance-report', $data);
        return $pdf->download('laporan-absensi-' . $user->role . '-' . now()->format('Y-m-d') . '.pdf');
    }

    public function confirmOvertime($id)
    {
        $attendance = Attendance::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $attendance->update(['is_extended_shift' => true]);
        return response()->json(['status' => 'success', 'message' => 'Status lembur dikonfirmasi.']);
    }

    /**
     * Generate QR Code HD dan tampilkan halaman print-ready (Save as PDF).
     */
    public function downloadQrPdf()
    {
        $user = Auth::user();

        if (!$user->qr_code_value) {
            return back()->with('error', 'QR Code belum tersedia untuk akun Anda.');
        }

        // Generate QR Code sebagai SVG HD dengan error correction tinggi
        $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
            ->size(500)
            ->errorCorrection('H')
            ->margin(1)
            ->generate($user->qr_code_value);

        return view('qrcode_pdf', [
            'qrSvg' => $qrSvg,
            'userName' => $user->name,
            'branchName' => $user->branch?->name ?? 'PStore',
        ]);
    }

}
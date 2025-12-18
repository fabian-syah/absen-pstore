<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Division;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\LeaveRequest;
use App\Models\Salary;       // Model Gaji
use App\Models\CashAdvance;  // Model Kasbon
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf; // Pastikan package PDF terinstall

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $data = [];
        $branch_id = $user->branch_id;

        // [TIMEZONE CONFIGURATION]
        $userTimezone = $user->branch->timezone ?? 'Asia/Jakarta';
        $data['current_timezone'] = $userTimezone;
        
        // Tentukan 'Hari Ini' & 'Sekarang' berdasarkan lokasi user
        $todayInBranch = Carbon::now($userTimezone)->format('Y-m-d'); 
        $nowInBranch = Carbon::now($userTimezone);

        // =========================================================================
        // 1. DATA UMUM (ID CARD & PERSONAL) - UNTUK SEMUA ROLE
        // =========================================================================
        $hireDate = $user->hire_date ? Carbon::parse($user->hire_date) : Carbon::now();
        $birthDate = $user->birth_date ? Carbon::parse($user->birth_date) : Carbon::parse('1999-05-12');

        $yyMasuk = $hireDate->format('y');
        $mmMasuk = $hireDate->format('m');
        $yyLahir = $birthDate->format('y');
        $mmLahir = $birthDate->format('m');
        $ddLahir = $birthDate->format('d');
        $noUrut  = str_pad($user->id, 3, '0', STR_PAD_LEFT);

        $data['idCardNumber'] = "{$yyMasuk}{$mmMasuk}{$yyLahir} {$mmLahir}{$ddLahir}{$noUrut}";

        // Jadwal Kerja Hari Ini
        $scheduleText = 'Fleksibel / Bebas';
        if ($user->check_in_start && $user->check_out_start) {
            $start = Carbon::parse($user->check_in_start)->format('H:i');
            $end   = Carbon::parse($user->check_out_start)->format('H:i');
            $scheduleText = "$start - $end";
        } elseif ($user->workSchedule) {
            $start = Carbon::parse($user->workSchedule->start_time)->format('H:i');
            $end   = Carbon::parse($user->workSchedule->end_time)->format('H:i');
            $scheduleText = "$start - $end";
        } else {
            $todaysAttendance = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', $todayInBranch)
                ->first();
            if ($todaysAttendance && $todaysAttendance->scheduled_check_in && $todaysAttendance->scheduled_check_out) {
                $start = Carbon::parse($todaysAttendance->scheduled_check_in)->format('H:i');
                $end   = Carbon::parse($todaysAttendance->scheduled_check_out)->format('H:i');
                $scheduleText = "$start - $end (Terekam)";
            }
        }
        $data['todaySchedule'] = $scheduleText;


        // =========================================================================
        // 2. DASHBOARD KHUSUS ADMIN GAJI
        // =========================================================================
        if ($user->role == 'admin_gaji') {
            
            // --- A. Statistik Payroll (Bulan Ini) ---
            $data['totalPayrollPaid'] = Salary::where('month', $nowInBranch->format('m'))
                ->where('year', $nowInBranch->year)
                ->where('status', 'paid')
                ->sum('total_amount');

            $data['pendingPayrollCount'] = Salary::where('status', 'pending')->count();

            // --- B. Statistik Kasbon ---
            // Total sisa hutang aktif (Approved dan belum lunas sepenuhnya)
            $data['activeCashAdvanceTotal'] = CashAdvance::where('status', 'approved')
                ->whereRaw('total_paid < amount')
                ->sum(DB::raw('amount - total_paid'));

            $data['pendingCashAdvanceCount'] = CashAdvance::where('status', 'pending')->count();

            // --- C. Data Tabel Terakhir ---
            $data['recentPayrolls'] = Salary::with('user')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            $data['recentCashAdvances'] = CashAdvance::with('user')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            // Return view khusus atau view yang sama dengan logic blade if
            return view('dashboard', $data);
        }


        // =========================================================================
        // 3. DASHBOARD UMUM (ADMIN UTAMA, AUDIT, SECURITY, USER, LEADER)
        // =========================================================================
        
        // Scope Branch IDs
        $allBranchIds = [];
        if ($user->branch_id) {
            $allBranchIds[] = $user->branch_id;
        }
        $extraBranches = $user->branches()->pluck('branches.id')->toArray();
        $allBranchIds = array_merge($allBranchIds, $extraBranches);
        $allBranchIds = array_values(array_unique($allBranchIds));

        // --- Data Absensi Personal ---
        $data['myLeaveToday'] = $this->getTodayLeaveRequest($user->id, $todayInBranch, 'approved');
        $data['myPendingLeave'] = $this->getTodayLeaveRequest($user->id, $todayInBranch, 'pending');

        // Logic Sesi Absensi (Masuk/Pulang/Lembur)
        $activeSession = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->where('check_in_time', '>=', $nowInBranch->copy()->subHours(32))
            ->latest('check_in_time')
            ->first();

        if ($activeSession) {
            $activeSession->check_in_time = Carbon::parse($activeSession->check_in_time)->timezone($userTimezone);
        }

        $finishedSessionToday = Attendance::where('user_id', $user->id)
            ->whereDate('check_in_time', $todayInBranch)
            ->whereNotNull('check_out_time')
            ->latest('check_in_time')
            ->first();

        if ($finishedSessionToday) {
            $finishedSessionToday->check_in_time = Carbon::parse($finishedSessionToday->check_in_time)->timezone($userTimezone);
            $finishedSessionToday->check_out_time = Carbon::parse($finishedSessionToday->check_out_time)->timezone($userTimezone);
        }

        $lastOvertimeSession = Attendance::where('user_id', $user->id)
            ->whereDate('check_in_time', '<', $todayInBranch)
            ->whereDate('check_out_time', $todayInBranch)
            ->latest('check_out_time')
            ->first();

        if ($lastOvertimeSession) {
            $lastOvertimeSession->check_out_time = Carbon::parse($lastOvertimeSession->check_out_time)->timezone($userTimezone);
        }

        $data['justFinishedOvertime'] = false;
        $data['lastOvertimeSession'] = null;

        if ($activeSession) {
            $data['myAttendanceToday'] = $activeSession;
        } elseif ($finishedSessionToday) {
            $data['myAttendanceToday'] = $finishedSessionToday;
        } elseif ($lastOvertimeSession) {
            $data['myAttendanceToday'] = null;
            $data['justFinishedOvertime'] = true;
            $data['lastOvertimeSession'] = $lastOvertimeSession;
        } else {
            $data['myAttendanceToday'] = null;
        }

        $data['myPendingCount'] = Attendance::where('user_id', $user->id)->where('status', 'pending_verification')->count();
        $data['myTeamCount'] = User::where('division_id', $user->division_id)->where('id', '!=', $user->id)->count();
        $personalStats = $this->getUserAttendanceStats($user->id, $branch_id);

        // --- Data Statistik Global / Role Based ---
        $data['totalUsers'] = 0; 
        $data['totalBranches'] = 0;
        $data['attendancesToday'] = 0;
        $data['pendingVerifications'] = 0;
        $data['pendingLeaves'] = 0;
        $data['myScansToday'] = 0;

        if ($user->role != 'security') {
            $data['leaderboard'] = Attendance::select('user_id', DB::raw('count(*) as total_attendance'), DB::raw('SEC_TO_TIME(AVG(TIME_TO_SEC(TIME(check_in_time)))) as avg_arrival_time'))
                ->whereMonth('check_in_time', $nowInBranch->month)
                ->whereYear('check_in_time', $nowInBranch->year)
                ->whereNotNull('check_out_time')
                ->where('status', 'verified')
                ->whereIn('presence_status', ['Masuk', 'WFH', 'WFH / Dinas Luar'])
                ->where('presence_status', '!=', 'Alpha')
                ->whereTime('check_in_time', '!=', '00:00:00')
                ->whereHas('user', function ($q) use ($user, $allBranchIds) {
                    $q->where('is_active', true)->whereNotIn('role', ['admin', 'security']);
                    if ($user->role !== 'admin') {
                        $q->whereIn('branch_id', $allBranchIds);
                    }
                })
                ->groupBy('user_id')
                ->orderBy('total_attendance', 'desc')
                ->orderBy('avg_arrival_time', 'asc')
                ->take(5)
                ->with(['user', 'user.division'])
                ->get();
        }

        if ($user->role == 'admin' || $user->role == 'security') {
            $securityUsersQuery = User::where('is_active', true)->whereIn('role', ['security', 'admin']);
            if ($user->role != 'admin') {
                $securityUsersQuery->whereIn('branch_id', $allBranchIds);
            } elseif ($user->role == 'admin' && $branch_id != null) {
                $securityUsersQuery->where('branch_id', $branch_id);
            }
            $securityUsers = $securityUsersQuery->get();

            $scanners = $securityUsers->map(function ($sec) use ($nowInBranch) {
                $scanIn = Attendance::where('scanned_by_user_id', $sec->id)->whereMonth('check_in_time', $nowInBranch->month)->whereYear('check_in_time', $nowInBranch->year)->count();
                $scanOut = Attendance::where('scanned_out_by_user_id', $sec->id)->whereMonth('check_in_time', $nowInBranch->month)->whereYear('check_in_time', $nowInBranch->year)->count();
                $sec->total_scans = $scanIn + $scanOut;
                return $sec;
            });
            $data['topScanners'] = $scanners->sortByDesc('total_scans')->take(5)->values();
        }

        // Widget Stats
        $attendanceQuery = Attendance::query();
        $userQuery = User::query();

        if ($user->role == 'admin') {
            if ($branch_id) {
                $attendanceQuery->where('branch_id', $branch_id);
                $userQuery->where('branch_id', $branch_id);
            }
            $data['totalUsers'] = (clone $userQuery)->where('role', '!=', 'admin')->where('is_active', true)->count();
            $data['totalBranches'] = $branch_id ? 1 : Branch::count();
            $data['attendancesToday'] = (clone $attendanceQuery)->whereDate('check_in_time', $todayInBranch)->count();
            $data['pendingVerifications'] = (clone $attendanceQuery)->where('status', 'pending_verification')->count();
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

        if (!isset($data['leaderboard'])) $data['leaderboard'] = [];
        if (!isset($data['topScanners'])) $data['topScanners'] = [];

        return view('dashboard', $data);
    }

    // =========================================================================
    // PRIVATE HELPER FUNCTIONS
    // =========================================================================

    private function getTodayLeaveRequest($user_id, $todayDate, $status = 'approved')
    {
        return LeaveRequest::where('user_id', $user_id)
            ->where('is_active', true)
            ->where('status', $status)
            ->where(function ($query) use ($todayDate) {
                $query->where(function ($q) use ($todayDate) {
                    $q->whereIn('type', ['sakit', 'izin', 'cuti', 'wfh'])
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
        if ($branch_id) $query->where('branch_id', $branch_id);
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
        if ($branch_id) $query->where('branch_id', $branch_id);
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

    public function exportAttendancePDF(Request $request)
    {
        $user = Auth::user();
        $branch_id = $user->branch_id;
        
        $userTimezone = $user->branch->timezone ?? 'Asia/Jakarta';
        $todayInBranch = Carbon::now($userTimezone)->format('Y-m-d');

        $allBranchIds = [];
        if ($user->branch_id) $allBranchIds[] = $user->branch_id;
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
}
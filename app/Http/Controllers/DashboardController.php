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

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $data = [];
        $branch_id = $user->branch_id;

        // =========================================================================
        // 1. LOGIKA NOMOR ID CARD CUSTOM
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

        // =========================================================================
        // 2. LOGIKA JADWAL KERJA
        // =========================================================================
        $scheduleText = 'Fleksibel / Bebas';
        if ($user->check_in_start && $user->check_out_start) {
            $start = Carbon::parse($user->check_in_start)->format('H:i');
            $end   = Carbon::parse($user->check_out_start)->format('H:i');
            $scheduleText = "$start - $end";
        } elseif ($user->workSchedule) {
            $start = Carbon::parse($user->workSchedule->start_time)->format('H:i');
            $end   = Carbon::parse($user->workSchedule->end_time)->format('H:i');
            $scheduleText = "$start - $end";
        }
        $data['todaySchedule'] = $scheduleText;

        // =========================================================================
        // 3. QUERY DASAR (UNTUK WIDGET ATAS)
        // =========================================================================
        $attendanceQuery = Attendance::query();
        $userQuery = User::query();
        // $divisionQuery = Division::query(); // Jika dibutuhkan

        if ($user->role == 'audit') {
            $auditBranchIds = $user->branches->pluck('id')->toArray();
            $attendanceQuery->whereIn('branch_id', $auditBranchIds);
            $userQuery->whereIn('branch_id', $auditBranchIds);
        } elseif ($user->role == 'admin' && $branch_id == null) {
            // Super Admin melihat semua
        } else {
            // Admin Cabang / User Biasa / Security melihat sesuai cabang
            if ($branch_id) {
                $attendanceQuery->where('branch_id', $branch_id);
                $userQuery->where('branch_id', $branch_id);
            }
        }

        // =========================================================================
        // 4. DATA IZIN HARI INI (MODIFIED)
        // =========================================================================
        
        // A. Mengambil Izin yang sudah APPROVED (Untuk menampilkan status 'Sedang Cuti/Sakit')
        $data['myLeaveToday'] = $this->getTodayLeaveRequest($user->id, 'approved');

        // B. Mengambil Izin yang masih PENDING (Untuk menampilkan status 'Menunggu Verifikasi')
        // Ini kunci agar dashboard bisa menampilkan kartu kuning "Pending"
        $data['myPendingLeave'] = $this->getTodayLeaveRequest($user->id, 'pending');


        // =========================================================================
        // 5. DATA ABSENSI HARI INI (LOGIKA LEMBUR / CROSS DAY)
        // =========================================================================
        
        // Cek sesi aktif: Check-in dalam 24 jam terakhir tapi belum check-out
        $activeSession = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->where('check_in_time', '>=', Carbon::now()->subHours(24))
            ->latest('check_in_time')
            ->first();

        if ($activeSession) {
            $data['myAttendanceToday'] = $activeSession;
        } else {
            // Jika tidak ada sesi aktif, ambil sesi yang sudah selesai HARI INI
            $finishedSession = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->whereNotNull('check_out_time')
                ->latest('check_in_time')
                ->first();

            $data['myAttendanceToday'] = $finishedSession;
        }

        // =========================================================================
        // 6. HITUNG DATA PERSONAL LAINNYA
        // =========================================================================
        $data['myPendingCount'] = Attendance::where('user_id', $user->id)
            ->where('status', 'pending_verification')
            ->count();

        $data['myTeamCount'] = User::where('division_id', $user->division_id)
            ->where('id', '!=', $user->id)
            ->count();

        $personalStats = $this->getUserAttendanceStats($user->id, $branch_id);

        // =========================================================================
        // 7. LEADERBOARD LOGIC (Top Performance)
        // =========================================================================
        if ($user->role != 'security') {
            $data['leaderboard'] = Attendance::select(
                'user_id',
                DB::raw('count(*) as total_attendance'),
                DB::raw('SEC_TO_TIME(AVG(TIME_TO_SEC(TIME(check_in_time)))) as avg_arrival_time'),
                DB::raw('SUM(TIMESTAMPDIFF(SECOND, check_in_time, check_out_time)) as total_work_seconds')
            )
                ->whereMonth('check_in_time', Carbon::now()->month)
                ->whereYear('check_in_time', Carbon::now()->year)
                ->whereNotNull('check_out_time')
                ->where('status', 'verified')
                ->whereIn('presence_status', ['Masuk', 'WFH', 'WFH / Dinas Luar'])
                ->where('presence_status', '!=', 'Alpha')
                ->whereTime('check_in_time', '!=', '00:00:00')
                ->whereRaw('TIMESTAMPDIFF(SECOND, check_in_time, check_out_time) > 0')
                ->whereHas('user', function ($q) use ($user) {
                    $q->where('is_active', true)
                        ->whereNotIn('role', ['admin', 'security']); // Admin & Security exclude
                    if ($user->role !== 'admin') {
                        $q->where('branch_id', $user->branch_id);
                    }
                })
                ->groupBy('user_id')
                ->orderBy('total_attendance', 'desc')
                ->orderBy('avg_arrival_time', 'asc')
                ->take(5)
                ->with(['user', 'user.division'])
                ->get();
        }

        // =========================================================================
        // 8. SCANNER LEADERBOARD (Top Security)
        // =========================================================================
        if ($user->role == 'admin' || $user->role == 'security') {
            $securityUsersQuery = User::where('is_active', true)
                ->whereIn('role', ['security', 'admin']);

            if ($user->role != 'admin' || ($user->role == 'admin' && $branch_id != null)) {
                $securityUsersQuery->where('branch_id', $branch_id);
            }

            $securityUsers = $securityUsersQuery->get();

            $scanners = $securityUsers->map(function ($sec) {
                // Hitung Scan Masuk
                $scanIn = Attendance::where('scanned_by_user_id', $sec->id)
                    ->whereMonth('check_in_time', Carbon::now()->month)
                    ->whereYear('check_in_time', Carbon::now()->year)
                    ->count();

                // Hitung Scan Pulang
                $scanOut = 0;
                try {
                    $scanOut = Attendance::where('scanned_out_by_user_id', $sec->id)
                        ->whereMonth('check_in_time', Carbon::now()->month)
                        ->whereYear('check_in_time', Carbon::now()->year)
                        ->count();
                } catch (\Exception $e) {
                    $scanOut = 0; 
                }

                $sec->total_scans = $scanIn + $scanOut;
                return $sec;
            });

            $data['topScanners'] = $scanners->sortByDesc('total_scans')->take(5)->values();
        }

        // =========================================================================
        // 9. LOGIKA DASHBOARD PEKERJAAN (WIDGET ADMIN/AUDIT/SECURITY)
        // =========================================================================
        if ($user->role == 'admin') {
            $data['totalUsers'] = (clone $userQuery)->where('role', '!=', 'admin')->where('is_active', true)->count();
            $data['totalBranches'] = $branch_id ? 1 : Branch::count();
            $data['attendancesToday'] = (clone $attendanceQuery)->whereDate('check_in_time', today())->count();
            $data['pendingVerifications'] = (clone $attendanceQuery)->where('status', 'pending_verification')->count();
            $data['stats'] = $this->getAdminAttendanceStats($branch_id);
        } elseif ($user->role == 'audit') {
            $data['myTeamMembers'] = (clone $userQuery)->whereIn('role', ['user_biasa', 'leader'])->count();
            $data['pendingVerifications'] = (clone $attendanceQuery)->where('status', 'pending_verification')->count();
            $data['attendancesToday'] = (clone $attendanceQuery)->whereDate('check_in_time', today())->count();
            $auditBranchIds = $user->branches->pluck('id')->toArray();
            $data['stats'] = $this->getAuditAttendanceStats($auditBranchIds);
        } elseif ($user->role == 'security') {
            $data['myScansToday'] = Attendance::where('scanned_by_user_id', $user->id)
                ->whereDate('check_in_time', today())->count();
            $data['totalUsers'] = (clone $userQuery)->whereIn('role', ['user_biasa', 'leader'])->where('is_active', true)->count();
            $data['stats'] = $this->getSecurityAttendanceStats($user->id, $branch_id);
        } else {
            $data['stats'] = $personalStats;
        }

        if (!isset($data['attendanceStats'])) {
            $data['attendanceStats'] = isset($data['stats']) ? $data['stats'] : $personalStats;
        }

        return view('dashboard', $data);
    }

    // =========================================================================
    // PRIVATE HELPER FUNCTIONS
    // =========================================================================

    /**
     * Mengambil Leave Request hari ini berdasarkan status
     * Updated: Menerima parameter status (approved/pending)
     */
    private function getTodayLeaveRequest($user_id, $status = 'approved')
    {
        return LeaveRequest::where('user_id', $user_id)
            ->where('is_active', true)
            ->where('status', $status) // <-- Filter Status Dinamis
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereIn('type', ['sakit', 'izin', 'cuti', 'wfh'])
                        ->whereDate('start_date', '<=', today())
                        ->whereDate('end_date', '>=', today());
                })->orWhere(function ($q) {
                    $q->where('type', 'telat')
                        ->whereDate('start_date', today());
                });
            })
            ->latest()
            ->first();
    }

    private function getAdminAttendanceStats($branch_id = null)
    {
        $query = Attendance::whereDate('check_in_time', today())
            ->where('presence_status', '!=', 'Alpha');

        if ($branch_id) $query->where('branch_id', $branch_id);

        $totalUsers = User::when($branch_id, function ($q) use ($branch_id) {
            return $q->where('branch_id', $branch_id);
        })->where('role', '!=', 'admin')->where('is_active', true)->count();

        $presentCount = (clone $query)->count();
        $lateCount = (clone $query)->where('is_late_checkin', true)->count();
        $earlyCount = (clone $query)->where('is_early_checkout', true)->count();
        $pendingCount = (clone $query)->where('status', 'pending_verification')->count();
        $onTimeCount = max($presentCount - $lateCount, 0);
        $absentCount = max($totalUsers - $presentCount, 0);

        return [
            'total' => $presentCount,
            'present' => $presentCount,
            'late' => $lateCount,
            'early' => $earlyCount,
            'pending' => $pendingCount,
            'on_time' => $onTimeCount,
            'absent' => $absentCount,
            'present_percentage' => $totalUsers > 0 ? round(($presentCount / $totalUsers) * 100) : 0,
            'late_percentage' => $presentCount > 0 ? round(($lateCount / $presentCount) * 100) : 0,
            'pending_percentage' => $presentCount > 0 ? round(($pendingCount / $presentCount) * 100) : 0,
            'absent_percentage' => $totalUsers > 0 ? round(($absentCount / $totalUsers) * 100) : 0,
        ];
    }

    private function getAuditAttendanceStats($branchData = null)
    {
        $query = Attendance::whereDate('check_in_time', today())
            ->where('presence_status', '!=', 'Alpha');

        if ($branchData) {
            if (is_array($branchData)) {
                $query->whereIn('branch_id', $branchData);
            } else {
                $query->where('branch_id', $branchData);
            }
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

    private function getSecurityAttendanceStats($security_id, $branch_id = null)
    {
        $query = Attendance::whereDate('check_in_time', today());
        if ($branch_id) $query->where('branch_id', $branch_id);

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

        $totalAttendances = (clone $query)->count();
        $late = (clone $query)->where('is_late_checkin', true)->count();
        $early = (clone $query)->where('is_early_checkout', true)->count();
        $pending = (clone $query)->where('status', 'pending_verification')->count();
        $onTime = max($totalAttendances - $late, 0);

        return [
            'total' => $totalAttendances,
            'present' => $totalAttendances,
            'late' => $late,
            'early' => $early,
            'pending' => $pending,
            'on_time' => $onTime,
            'present_percentage' => 100,
            'late_percentage' => $totalAttendances > 0 ? round(($late / $totalAttendances) * 100) : 0,
            'on_time_percentage' => $totalAttendances > 0 ? round(($onTime / $totalAttendances) * 100) : 0,
            'pending_percentage' => $totalAttendances > 0 ? round(($pending / $totalAttendances) * 100) : 0,
        ];
    }

    public function exportAttendancePDF(Request $request)
    {
        $user = Auth::user();
        $branch_id = $user->branch_id;
        $date = $request->get('date', today()->format('Y-m-d'));

        $data = [];
        $data['user'] = $user;
        $data['export_date'] = now()->format('d-m-Y H:i:s');
        $data['period'] = $date;

        switch ($user->role) {
            case 'admin':
                $data['stats'] = $this->getAdminAttendanceStats($branch_id);
                $data['title'] = 'Laporan Statistik Harian (Admin)';
                $data['role'] = 'Admin';
                break;
            case 'audit':
                $auditBranchIds = $user->branches->pluck('id')->toArray();
                $data['stats'] = $this->getAuditAttendanceStats($auditBranchIds);
                $data['title'] = 'Laporan Verifikasi Absensi';
                $data['role'] = 'Audit';
                break;
            case 'security':
                $data['stats'] = $this->getSecurityAttendanceStats($user->id, $branch_id);
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
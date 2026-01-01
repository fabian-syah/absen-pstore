<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AttendanceSummaryController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $selectedYear = $request->get('year', date('Y'));
        $selectedMonth = $request->get('month', date('n')); // Ambil bulan yang dipilih user
        $targetUserId = $request->get('user_id');

        // --- 1. SEARCH & SCOPE KARYAWAN ---
        $employees = collect([]);
        $targetUser = $currentUser; 
        $allowedRoles = ['admin', 'audit', 'leader', 'admin_gaji'];

        if (in_array($currentUser->role, $allowedRoles)) {
            if ($currentUser->role === 'audit' || $currentUser->role === 'leader') {
                $handledBranchIds = $currentUser->branches ? $currentUser->branches->pluck('id')->toArray() : [];
                if($currentUser->branch_id) { $handledBranchIds[] = $currentUser->branch_id; }
                $employees = User::whereIn('branch_id', array_unique($handledBranchIds))->orderBy('name', 'asc')->get();
            } else {
                $employees = User::orderBy('name', 'asc')->get();
            }

            if ($targetUserId) {
                $foundUser = $employees->where('id', $targetUserId)->first();
                if ($foundUser) {
                    $targetUser = $foundUser;
                } elseif (in_array($currentUser->role, ['admin', 'admin_gaji'])) {
                    $directFind = User::find($targetUserId);
                    if($directFind) $targetUser = $directFind;
                }
            }
        } 

        // --- 2. AMBIL DATA ---
        // Data absensi khusus bulan dan tahun yang dipilih user
        $history = Attendance::where('user_id', $targetUser->id)
            ->whereYear('check_in_time', $selectedYear)
            ->whereMonth('check_in_time', $selectedMonth)
            ->orderBy('check_in_time', 'desc')
            ->get();

        $attendances = Attendance::where('user_id', $targetUser->id)->whereYear('check_in_time', $selectedYear)->get();
        $leaves = LeaveRequest::where('user_id', $targetUser->id)->where('status', 'approved')
            ->where(function($q) use ($selectedYear) {
                $q->whereYear('start_date', $selectedYear)->orWhereYear('end_date', $selectedYear);
            })->get();

        // --- 3. INISIALISASI ---
        $monthsData = [];
        $grandTotal = [
            'total_hari' => 0, 'masuk' => 0, 'wfh' => 0, 'sakit' => 0,
            'izin' => 0, 'cuti' => 0, 'alpha' => 0, 'telat' => 0, 'pulang_cepat' => 0, 'pending' => 0
        ];

        // --- 4. LOOPING 12 BULAN ---
        for ($m = 1; $m <= 12; $m++) {
            $monthAtt = $attendances->filter(fn($q) => $q->check_in_time->month == $m);

            // Hitung Telat & Alpha
            $telatFromAttendance = $monthAtt->filter(fn($row) => $row->is_late_checkin == true || str_contains(strtolower($row->presence_status ?? ''), 'telat'))->count();
            $alphaCount = $monthAtt->filter(fn($q) => strtolower($q->presence_status ?? '') == 'alpha')->count();
            $pendingCount = $monthAtt->filter(fn($q) => $q->status == 'pending_verification')->count();

            // Hitung Masuk (Kunci: Bukan Alpha)
            $masukCount = $monthAtt->filter(function($row) {
                $st = strtolower($row->presence_status ?? '');
                return in_array($st, ['masuk', 'wfh', 'dinas', 'izin telat', 'telat']) && $st !== 'alpha';
            })->count();

            $wfhCount = $monthAtt->filter(fn($q) => str_contains(strtolower($q->presence_status ?? ''), 'wfh'))->count();
            $pulangCepatCount = $monthAtt->where('is_early_checkout', true)->count();

            // Hitung dari Tabel Leave Request
            $cutiCount = 0; $sakitCount = 0; $izinCount = 0; $telatFromLeave = 0; $wfhFromLeaveCount = 0; 
            foreach ($leaves as $leave) {
                $start = Carbon::parse($leave->start_date);
                $end = $leave->end_date ? Carbon::parse($leave->end_date) : $start->copy();
                $period = CarbonPeriod::create($start, $end);
                foreach ($period as $date) {
                    if ($date->month == $m && $date->year == $selectedYear) {
                        $alreadyInAttendance = $monthAtt->filter(fn($att) => $att->check_in_time->isSameDay($date))->isNotEmpty();
                        if ($leave->type == 'cuti') $cutiCount++;
                        elseif ($leave->type == 'sakit') $sakitCount++;
                        elseif ($leave->type == 'telat') $telatFromLeave++;
                        elseif (strtolower($leave->type) == 'wfh') { if (!$alreadyInAttendance) $wfhFromLeaveCount++; }
                        else $izinCount++;
                    }
                }
            }

            $totalMasukBulanIni = $masukCount + $wfhFromLeaveCount; 
            $totalHariBulanIni = $totalMasukBulanIni + $sakitCount + $izinCount + $cutiCount + $alphaCount;

            $monthsData[$m] = [
                'name' => Carbon::create()->month($m)->translatedFormat('F'),
                'total_hari' => $totalHariBulanIni,
                'masuk' => $totalMasukBulanIni,
                'wfh' => $wfhCount + $wfhFromLeaveCount,
                'sakit' => $sakitCount,
                'izin' => $izinCount,
                'cuti' => $cutiCount,
                'alpha' => $alphaCount,
                'telat' => $telatFromAttendance + $telatFromLeave,
                'pulang_cepat' => $pulangCepatCount,
                'pending' => $pendingCount
            ];
            foreach($grandTotal as $key => $val) { $grandTotal[$key] += $monthsData[$m][$key] ?? 0; }
        }

        // --- 5. DATA UNTUK BOX ATAS ---
        $summary = $monthsData[$selectedMonth];

        // Navigasi Prev/Next
        $prevMonth = $selectedMonth == 1 ? 12 : $selectedMonth - 1;
        $prevYear = $selectedMonth == 1 ? $selectedYear - 1 : $selectedYear;
        $nextMonth = $selectedMonth == 12 ? 1 : $selectedMonth + 1;
        $nextYear = $selectedMonth == 12 ? $selectedYear + 1 : $selectedYear;

        return view('attendance.history', [ // PASTIKAN NAMA VIEW ADALAH history
            'employee' => $targetUser,
            'history' => $history,
            'selectedYear' => $selectedYear,
            'selectedMonth' => $selectedMonth,
            'monthsData' => $monthsData,
            'grandTotal' => $grandTotal,
            'summary' => $summary, 
            'employees' => $employees,
            'prevMonth' => $prevMonth, 'prevYear' => $prevYear,
            'nextMonth' => $nextMonth, 'nextYear' => $nextYear,
        ]);
    }
}
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
        $selectedMonth = $request->get('month', date('n'));
        $targetUserId = $request->get('user_id');

        // --- 1. SEARCH & SCOPE KARYAWAN ---
        $employees = collect([]);
        $targetUser = $currentUser; 
        $allowedRoles = ['admin', 'audit', 'leader', 'admin_gaji'];

        $isAccessGranted = in_array($currentUser->role, $allowedRoles);

        if ($isAccessGranted) {
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

        // --- 2. AMBIL DATA ABSENSI SAJA ---
        // Ambil semua attendance untuk tahun ini
        $attendances = Attendance::where('user_id', $targetUser->id)
            ->whereYear('check_in_time', $selectedYear)
            ->get();

        // --- 3. HITUNG DARI ATTENDANCE SAJA (KONSISTEN DENGAN HISTORY) ---
        $monthsData = [];
        $grandTotal = [
            'total_hari' => 0, 'masuk' => 0, 'wfh' => 0, 'sakit' => 0,
            'izin' => 0, 'cuti' => 0, 'alpha' => 0, 'telat' => 0, 
            'pulang_cepat' => 0, 'pending' => 0
        ];

        // --- 4. LOOPING 12 BULAN ---
        for ($m = 1; $m <= 12; $m++) {
            // Filter attendance untuk bulan ini
            $monthAtt = $attendances->filter(fn($q) => 
                $q->check_in_time->month == $m && $q->check_in_time->year == $selectedYear
            );

            if ($monthAtt->isEmpty()) {
                // Bulan kosong
                $monthsData[$m] = [
                    'name' => Carbon::create()->month($m)->translatedFormat('F'),
                    'total_hari' => 0,
                    'masuk' => 0,
                    'wfh' => 0,
                    'sakit' => 0,
                    'izin' => 0,
                    'cuti' => 0,
                    'alpha' => 0,
                    'telat' => 0,
                    'pulang_cepat' => 0,
                    'pending' => 0
                ];
            } else {
                // Hitung dari attendance yang ada
                $totalHariBulanIni = $monthAtt->count();
                
                // Kategorikan berdasarkan presence_status
                $masukCount = $monthAtt->filter(fn($att) => 
                    in_array(strtolower($att->presence_status ?? ''), ['masuk', 'izin telat', 'telat'])
                )->count();
                
                $wfhCount = $monthAtt->filter(fn($att) => 
                    str_contains(strtolower($att->presence_status ?? ''), 'wfh') ||
                    str_contains(strtolower($att->presence_status ?? ''), 'dinas')
                )->count();
                
                $sakitCount = $monthAtt->filter(fn($att) => 
                    strtolower($att->presence_status ?? '') == 'sakit'
                )->count();
                
                $izinCount = $monthAtt->filter(fn($att) => 
                    strtolower($att->presence_status ?? '') == 'izin'
                )->count();
                
                $cutiCount = $monthAtt->filter(fn($att) => 
                    strtolower($att->presence_status ?? '') == 'cuti'
                )->count();
                
                $alphaCount = $monthAtt->filter(fn($att) => 
                    strtolower($att->presence_status ?? '') == 'alpha'
                )->count();
                
                $telatCount = $monthAtt->where('is_late_checkin', true)->count();
                $pulangCepatCount = $monthAtt->where('is_early_checkout', true)->count();
                $pendingCount = $monthAtt->where('status', 'pending_verification')->count();

                $monthsData[$m] = [
                    'name' => Carbon::create()->month($m)->translatedFormat('F'),
                    'total_hari' => $totalHariBulanIni,
                    'masuk' => $masukCount + $wfhCount, // Masuk termasuk WFH
                    'wfh' => $wfhCount,
                    'sakit' => $sakitCount,
                    'izin' => $izinCount,
                    'cuti' => $cutiCount,
                    'alpha' => $alphaCount,
                    'telat' => $telatCount,
                    'pulang_cepat' => $pulangCepatCount,
                    'pending' => $pendingCount
                ];
            }
            
            // Update grand total
            foreach($grandTotal as $key => $val) { 
                $grandTotal[$key] += $monthsData[$m][$key] ?? 0; 
            }
        }

        // --- 5. DATA UNTUK BOX ATAS (dari bulan yang dipilih) ---
        $rawSummary = $monthsData[$selectedMonth];
        $summary = [
            'present' => $rawSummary['masuk'],
            'sakit'   => $rawSummary['sakit'],
            'izin'    => $rawSummary['izin'],
            'alpha'   => $rawSummary['alpha'],
            'total'   => $rawSummary['total_hari'],
        ];

        return view('attendance.summary', [
            'user' => $targetUser,
            'isAccessGranted' => $isAccessGranted,
            'selectedYear' => $selectedYear,
            'selectedMonth' => $selectedMonth,
            'monthsData' => $monthsData,
            'grandTotal' => $grandTotal,
            'summary' => $summary, 
            'employees' => $employees,
        ]);
    }
}
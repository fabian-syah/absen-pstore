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
        $targetUserId = $request->get('user_id');

        // --- 1. SEARCH & SCOPE KARYAWAN ---
        $employees = collect([]);
        $targetUser = $currentUser;

        if (in_array($currentUser->role, ['admin', 'audit'])) {
            if ($currentUser->role === 'audit') {
                $handledBranchIds = $currentUser->branches ? $currentUser->branches->pluck('id')->toArray() : [];
                $employees = User::whereIn('branch_id', $handledBranchIds)->orderBy('name', 'asc')->get();
            } else {
                $employees = User::orderBy('name', 'asc')->get();
            }

            if ($targetUserId) {
                $foundUser = $employees->where('id', $targetUserId)->first();
                if ($foundUser) $targetUser = $foundUser;
                else $targetUser = User::find($targetUserId) ?? $currentUser;
            }
        } 

        // --- 2. AMBIL DATA ---
        $attendances = Attendance::where('user_id', $targetUser->id)
            ->whereYear('check_in_time', $selectedYear)
            ->get();

        $leaves = LeaveRequest::where('user_id', $targetUser->id)
            ->where('status', 'approved')
            ->where(function($q) use ($selectedYear) {
                $q->whereYear('start_date', $selectedYear)
                  ->orWhereYear('end_date', $selectedYear);
            })
            ->get();

        // --- 3. INISIALISASI ---
        $monthsData = [];
        $grandTotal = [
            'total_hari' => 0, 'masuk' => 0, 'wfh' => 0, 'sakit' => 0,
            'izin' => 0, 'cuti' => 0, 'alpha' => 0, 'telat' => 0, 'pulang_cepat' => 0
        ];

        // --- 4. LOOPING ---
        for ($m = 1; $m <= 12; $m++) {
            $monthAtt = $attendances->filter(fn($q) => $q->check_in_time->month == $m);

            // ==========================================
            // LOGIKA PERHITUNGAN YANG DIPERBAIKI
            // ==========================================

            // A. TELAT (Prioritas Cek ini Dulu)
            // Cek 3 Kemungkinan:
            // 1. Boolean is_late_checkin bernilai true
            // 2. Status teknis ('status') adalah 'late'
            // 3. Label tampilan ('presence_status') mengandung kata 'Telat'
            $telatCount = $monthAtt->filter(function($row) {
                return $row->is_late_checkin == true 
                       || strtolower($row->status) === 'late'
                       || str_contains(strtolower($row->presence_status), 'telat');
            })->count();

            // B. ALPHA
            $alphaCount = $monthAtt->filter(function($q) {
                return strtolower($q->status) === 'alpha' 
                       || strtolower($q->presence_status) === 'alpha';
            })->count();

            // C. MASUK (Hadir Tepat Waktu + Telat + WFH)
            // Catatan: Telat biasanya tetap dihitung sebagai kehadiran (Masuk), tapi dicatat di kolom telat terpisah.
            $masukCount = $monthAtt->filter(function($row) {
                $st = strtolower($row->presence_status ?? '');
                $type = strtolower($row->attendance_type ?? '');
                
                // Dianggap masuk jika statusnya wajar (masuk/wfh/dinas/telat) 
                // ATAU tipenya scan/self tapi bukan sakit/izin/cuti/alpha
                return in_array($st, ['masuk', 'wfh', 'dinas', 'izin telat', 'telat']) 
                       || (in_array($type, ['scan', 'self', 'manual']) && !in_array($st, ['sakit', 'izin', 'cuti', 'alpha']));
            })->count();

            // D. WFH
            $wfhCount = $monthAtt->filter(fn($q) => str_contains(strtolower($q->presence_status), 'wfh'))->count();
            
            // E. PULANG CEPAT
            $pulangCepatCount = $monthAtt->where('is_early_checkout', true)->count();

            // F. DATA CUTI/SAKIT DARI LEAVE REQUESTS
            $cutiCount = 0; $sakitCount = 0; $izinCount = 0; $wfhFromLeaveCount = 0; 

            foreach ($leaves as $leave) {
                $start = Carbon::parse($leave->start_date);
                $end   = $leave->end_date ? Carbon::parse($leave->end_date) : $start->copy();
                $period = CarbonPeriod::create($start, $end);

                foreach ($period as $date) {
                    if ($date->month == $m && $date->year == $selectedYear) {
                        // Cek apakah tanggal ini sudah ada absen (misal WFH absen masuk)
                        $alreadyInAttendance = $monthAtt->filter(fn($att) => $att->check_in_time->isSameDay($date))->isNotEmpty();

                        if ($leave->type == 'cuti') $cutiCount++;
                        elseif ($leave->type == 'sakit') $sakitCount++;
                        elseif (strtolower($leave->type) == 'wfh') {
                            if (!$alreadyInAttendance) $wfhFromLeaveCount++;
                        } else $izinCount++; // Izin biasa
                    }
                }
            }

            // G. AGGREGASI
            $totalWfhBulanIni = $wfhCount + $wfhFromLeaveCount;
            // Total Masuk (Hadir Fisik + Telat + WFH)
            $totalMasukBulanIni = $masukCount + $wfhFromLeaveCount;
            
            // Total Hari = (Masuk + WFH) + Sakit + Izin + Cuti + Alpha
            // Perhatikan: Telat sudah termasuk di dalam "Masuk", jadi tidak dijumlah lagi ke Total Hari
            $totalHariBulanIni = $totalMasukBulanIni + $sakitCount + $izinCount + $cutiCount + $alphaCount;

            $monthsData[$m] = [
                'name' => Carbon::create()->month($m)->translatedFormat('F'),
                'total_hari' => $totalHariBulanIni,
                'masuk' => $totalMasukBulanIni,
                'wfh' => $totalWfhBulanIni,
                'sakit' => $sakitCount,
                'izin' => $izinCount,
                'cuti' => $cutiCount,
                'alpha' => $alphaCount,
                'telat' => $telatCount,
                'pulang_cepat' => $pulangCepatCount
            ];

            // Akumulasi ke Grand Total
            $grandTotal['total_hari'] += $totalHariBulanIni;
            $grandTotal['masuk'] += $totalMasukBulanIni;
            $grandTotal['wfh'] += $totalWfhBulanIni;
            $grandTotal['sakit'] += $sakitCount;
            $grandTotal['izin'] += $izinCount;
            $grandTotal['cuti'] += $cutiCount;
            $grandTotal['alpha'] += $alphaCount;
            $grandTotal['telat'] += $telatCount;
            $grandTotal['pulang_cepat'] += $pulangCepatCount;
        }

        return view('attendance.summary', [
            'user' => $targetUser,
            'selectedYear' => $selectedYear,
            'monthsData' => $monthsData,
            'grandTotal' => $grandTotal,
            'employees' => $employees,
            'isAccessGranted' => in_array($currentUser->role, ['admin', 'audit'])
        ]);
    }
}
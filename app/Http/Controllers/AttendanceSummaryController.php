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
        // Ambil Data Kehadiran Real (Scan/Selfie)
        $attendances = Attendance::where('user_id', $targetUser->id)
            ->whereYear('check_in_time', $selectedYear)
            ->get();

        // Ambil Data Pengajuan (Izin/Sakit/Cuti/Telat) yang Approved
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

        // --- 4. LOOPING 12 BULAN ---
        for ($m = 1; $m <= 12; $m++) {
            $monthAtt = $attendances->filter(fn($q) => $q->check_in_time->month == $m);

            // ==========================================
            // LOGIKA 1: HITUNG DARI TABEL ATTENDANCE
            // ==========================================
            
            // Hitung Telat Fisik (Dari mesin absen/scan)
            $telatFromAttendance = $monthAtt->filter(function($row) {
                return $row->is_late_checkin == true 
                       || strtolower($row->status) === 'late'
                       || str_contains(strtolower($row->presence_status ?? ''), 'telat');
            })->count();

            // Hitung Alpha (Tanpa Keterangan)
            $alphaCount = $monthAtt->filter(fn($q) => strtolower($q->presence_status ?? '') == 'alpha' || $q->status == 'alpha')->count();

            // Hitung Masuk (Hadir + WFH + Dinas + Telat Fisik)
            // Catatan: Telat fisik tetap dihitung sebagai "Masuk" (Hadir), tapi juga dicatat di kolom Telat
            $masukCount = $monthAtt->filter(function($row) {
                $st = strtolower($row->presence_status ?? '');
                $type = strtolower($row->attendance_type ?? '');
                
                return in_array($st, ['masuk', 'wfh', 'dinas', 'izin telat', 'telat']) 
                       || (in_array($type, ['scan', 'self', 'manual']) && !in_array($st, ['sakit', 'izin', 'cuti', 'alpha']));
            })->count();

            $wfhCount = $monthAtt->filter(fn($q) => str_contains(strtolower($q->presence_status ?? ''), 'wfh'))->count();
            $pulangCepatCount = $monthAtt->where('is_early_checkout', true)->count();

            // ==========================================
            // LOGIKA 2: HITUNG DARI TABEL LEAVE REQUEST
            // ==========================================
            
            $cutiCount = 0; 
            $sakitCount = 0; 
            $izinCount = 0; 
            $telatFromLeave = 0; // Variabel baru untuk menampung 'Izin Telat'
            $wfhFromLeaveCount = 0; 

            foreach ($leaves as $leave) {
                $start = Carbon::parse($leave->start_date);
                $end   = $leave->end_date ? Carbon::parse($leave->end_date) : $start->copy();
                $period = CarbonPeriod::create($start, $end);

                foreach ($period as $date) {
                    if ($date->month == $m && $date->year == $selectedYear) {
                        // Cek apakah hari ini sudah ada absen fisik agar tidak double count WFH
                        $alreadyInAttendance = $monthAtt->filter(fn($att) => $att->check_in_time->isSameDay($date))->isNotEmpty();

                        if ($leave->type == 'cuti') {
                            $cutiCount++;
                        } elseif ($leave->type == 'sakit') {
                            $sakitCount++;
                        } elseif ($leave->type == 'telat') {
                            // [FIXING] Jika tipe 'telat', masukkan ke counter Telat, JANGAN ke Izin
                            $telatFromLeave++;
                        } elseif (strtolower($leave->type) == 'wfh') {
                            if (!$alreadyInAttendance) $wfhFromLeaveCount++;
                        } else {
                            // Sisanya (Izin biasa/lainnya) masuk ke counter Izin
                            $izinCount++;
                        }
                    }
                }
            }

            // ==========================================
            // AGGREGASI TOTAL
            // ==========================================

            // Total Telat = Telat di Mesin + Izin Telat
            $totalTelatBulanIni = $telatFromAttendance + $telatFromLeave;

            $totalWfhBulanIni = $wfhCount + $wfhFromLeaveCount;
            
            // Total Masuk = Absen Fisik + WFH Resmi
            // Note: Izin Telat biasanya orangnya TETAP MASUK tapi telat. 
            // Jika Anda ingin Izin Telat dihitung sebagai "Hadir" juga, uncomment baris bawah:
            // $totalMasukBulanIni = $masukCount + $wfhFromLeaveCount + $telatFromLeave; 
            $totalMasukBulanIni = $masukCount + $wfhFromLeaveCount; 

            // Total Hari = Masuk + Sakit + Izin + Cuti + Alpha 
            // (Telat tidak dijumlah ke Total Hari karena Telat itu status dari 'Masuk')
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
                'telat' => $totalTelatBulanIni, // Gunakan Total Telat Gabungan
                'pulang_cepat' => $pulangCepatCount
            ];

            // Akumulasi ke Grand Total Tahunan
            $grandTotal['total_hari'] += $totalHariBulanIni;
            $grandTotal['masuk'] += $totalMasukBulanIni;
            $grandTotal['wfh'] += $totalWfhBulanIni;
            $grandTotal['sakit'] += $sakitCount;
            $grandTotal['izin'] += $izinCount;
            $grandTotal['cuti'] += $cutiCount;
            $grandTotal['alpha'] += $alphaCount;
            $grandTotal['telat'] += $totalTelatBulanIni;
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
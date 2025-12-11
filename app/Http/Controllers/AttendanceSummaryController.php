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
    public function index(Request $request, $userId = null)
    {
        $currentUser = Auth::user();
        
        // 1. Tentukan Target User
        if ($userId) {
            // Cek Hak Akses: Hanya Admin, Audit, Leader, Admin Gaji yang boleh intip orang lain
            if (!in_array($currentUser->role, ['admin', 'audit', 'leader', 'admin_gaji'])) {
                abort(403, 'Akses Ditolak.');
            }

            // Validasi Tambahan (Optional): Pastikan Leader/Audit hanya lihat bawahan/wilayahnya
            // (Bisa ditambahkan jika perlu strict access control seperti di UserController)
            
            $targetUser = User::findOrFail($userId);
        } else {
            // Jika tidak ada ID, berarti lihat punya sendiri
            $targetUser = $currentUser;
        }

        // 2. Ambil Filter Tahun (Default Tahun Ini)
        $selectedYear = $request->get('year', date('Y'));

        // 3. Ambil Data Kehadiran Real (Masuk, WFH Scan, Telat, Alpha, Dinas)
        $attendances = Attendance::where('user_id', $targetUser->id)
            ->whereYear('check_in_time', $selectedYear)
            ->get();

        // 4. Ambil Data Izin/Cuti/Sakit/WFH Request yang sudah disetujui
        $leaves = LeaveRequest::where('user_id', $targetUser->id)
            ->where('status', 'approved')
            ->where(function($q) use ($selectedYear) {
                $q->whereYear('start_date', $selectedYear)
                  ->orWhereYear('end_date', $selectedYear);
            })
            ->get();

        // 5. Inisialisasi Variable Data
        $monthsData = [];
        $grandTotal = [
            'total_hari' => 0,
            'masuk'      => 0, 
            'wfh'        => 0, 
            'sakit'      => 0,
            'izin'       => 0, 
            'cuti'       => 0,
            'alpha'      => 0,
            'telat'      => 0,
            'pulang_cepat' => 0
        ];

        // 6. Loop 12 Bulan
        for ($m = 1; $m <= 12; $m++) {
            // -- A. Hitung Data dari Tabel Attendance (Real Time Scan/Absen) --
            $monthAtt = $attendances->filter(fn($q) => $q->check_in_time->month == $m);

            // Hitung Masuk Dasar (Dari Scan/Manual)
            $masukCount = $monthAtt->filter(function($row) {
                $status = strtolower($row->presence_status ?? '');
                // Hitung masuk jika statusnya WFH, Masuk, Dinas, dll
                return in_array($status, ['masuk', 'wfh', 'dinas', 'izin telat']) 
                        || (in_array($row->attendance_type, ['scan', 'self', 'manual']) && !in_array($status, ['sakit', 'izin', 'cuti', 'alpha']));
            })->count();

            // Hitung WFH yang berasal dari Absen Mandiri/Scan (bukan dari request izin)
            $wfhCount = $monthAtt->filter(fn($q) => str_contains(strtolower($q->presence_status), 'wfh'))->count();
            
            $alphaCount = $monthAtt->filter(fn($q) => strtolower($q->presence_status) == 'alpha')->count();
            $telatCount = $monthAtt->where('is_late_checkin', true)->count();
            $pulangCepatCount = $monthAtt->where('is_early_checkout', true)->count();

            // -- B. Hitung Data dari Tabel LeaveRequest (Izin/Sakit/Cuti/WFH Request) --
            $cutiCount = 0;
            $sakitCount = 0;
            $izinCount = 0;
            
            // Variable temp untuk WFH dari Leave Request agar tidak double count
            $wfhFromLeaveCount = 0; 

            foreach ($leaves as $leave) {
                $start = Carbon::parse($leave->start_date);
                $end   = $leave->end_date ? Carbon::parse($leave->end_date) : $start->copy();
                $period = CarbonPeriod::create($start, $end);

                foreach ($period as $date) {
                    if ($date->month == $m && $date->year == $selectedYear) {
                        
                        // Cek apakah tanggal ini SUDAH ada di tabel Attendance (scan)?
                        $alreadyInAttendance = $monthAtt->filter(fn($att) => $att->check_in_time->isSameDay($date))->isNotEmpty();

                        if ($leave->type == 'cuti') {
                            $cutiCount++;
                        } elseif ($leave->type == 'sakit') {
                            $sakitCount++;
                        } elseif (strtolower($leave->type) == 'wfh') {
                            if (!$alreadyInAttendance) {
                                $wfhFromLeaveCount++;
                            }
                        } else {
                            $izinCount++;
                        }
                    }
                }
            }

            // Gabungkan WFH dari Scan + WFH dari Leave Request
            $totalWfhBulanIni = $wfhCount + $wfhFromLeaveCount;
            
            // Update Masuk Count (Masuk Scan + WFH dari Leave)
            $totalMasukBulanIni = $masukCount + $wfhFromLeaveCount;

            // -- C. Total Hari Aktif --
            $totalHariBulanIni = $totalMasukBulanIni + $sakitCount + $izinCount + $cutiCount + $alphaCount;

            // -- D. Masukkan ke Array Bulanan --
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

            // -- E. Akumulasi Grand Total --
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
            'user' => $targetUser, // Kirim data user yang sedang dilihat
            'selectedYear' => $selectedYear,
            'monthsData' => $monthsData,
            'grandTotal' => $grandTotal
        ]);
    }
}
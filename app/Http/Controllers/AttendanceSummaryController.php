<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AttendanceSummaryController extends Controller
{
    public function index(Request $request)
    {
        // 1. Pastikan Data Terisolasi (Hanya User yang Login)
        $user = Auth::user();

        // 2. Ambil Filter Tahun (Default Tahun Ini)
        $selectedYear = $request->get('year', date('Y'));

        // 3. Ambil Data Kehadiran Real (Masuk, WFH, Telat, Alpha, Dinas)
        // Kita ambil semua data kehadiran user di tahun tersebut
        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('check_in_time', $selectedYear)
            ->get();

        // 4. Ambil Data Izin/Cuti/Sakit yang sudah disetujui
        $leaves = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where(function($q) use ($selectedYear) {
                $q->whereYear('start_date', $selectedYear)
                  ->orWhereYear('end_date', $selectedYear);
            })
            ->get();

        // 5. Inisialisasi Variable Data
        $monthsData = [];
        // Grand Total untuk setahun
        $grandTotal = [
            'total_hari' => 0,
            'masuk'      => 0, // Termasuk WFH & Dinas
            'wfh'        => 0, // Counter khusus WFH
            'sakit'      => 0,
            'izin'       => 0, // Izin + Libur
            'cuti'       => 0,
            'alpha'      => 0,
            'telat'      => 0,
            'pulang_cepat' => 0
        ];

        // 6. Loop 12 Bulan
        for ($m = 1; $m <= 12; $m++) {
            // -- A. Hitung Data dari Tabel Attendance (Real Time Scan/Absen) --
            // Filter data bulan ini
            $monthAtt = $attendances->filter(fn($q) => $q->check_in_time->month == $m);

            // Hitung Masuk (Status Masuk, WFH, Dinas, atau tipe Scan/Selfie/Manual)
            // Mengecualikan status Sakit/Izin/Cuti/Alpha dari tabel attendance agar tidak double count dengan LeaveRequest
            $masukCount = $monthAtt->filter(function($row) {
                $status = strtolower($row->presence_status ?? '');
                return in_array($status, ['masuk', 'wfh', 'dinas', 'izin telat']) 
                       || (in_array($row->attendance_type, ['scan', 'self', 'manual']) && !in_array($status, ['sakit', 'izin', 'cuti', 'alpha']));
            })->count();

            $wfhCount = $monthAtt->filter(fn($q) => str_contains(strtolower($q->presence_status), 'wfh'))->count();
            $alphaCount = $monthAtt->filter(fn($q) => strtolower($q->presence_status) == 'alpha')->count();
            $telatCount = $monthAtt->where('is_late_checkin', true)->count();
            $pulangCepatCount = $monthAtt->where('is_early_checkout', true)->count();

            // -- B. Hitung Data dari Tabel LeaveRequest (Izin/Sakit/Cuti) --
            // Kita hitung manual berdasarkan range tanggal (Start s/d End)
            $cutiCount = 0;
            $sakitCount = 0;
            $izinCount = 0;

            foreach ($leaves as $leave) {
                $start = Carbon::parse($leave->start_date);
                $end   = $leave->end_date ? Carbon::parse($leave->end_date) : $start->copy();
                $period = CarbonPeriod::create($start, $end);

                foreach ($period as $date) {
                    // Hanya hitung jika tanggal berada di bulan & tahun yang sedang diloop
                    if ($date->month == $m && $date->year == $selectedYear) {
                        if ($leave->type == 'cuti') {
                            $cutiCount++;
                        } elseif ($leave->type == 'sakit') {
                            $sakitCount++;
                        } else {
                            // Izin, Libur, dll
                            $izinCount++;
                        }
                    }
                }
            }

            // -- C. Total Hari Aktif --
            $totalHariBulanIni = $masukCount + $sakitCount + $izinCount + $cutiCount + $alphaCount;

            // -- D. Masukkan ke Array Bulanan --
            $monthsData[$m] = [
                'name' => Carbon::create()->month($m)->translatedFormat('F'), // Nama Bulan (Indonesia)
                'total_hari' => $totalHariBulanIni,
                'masuk' => $masukCount,
                'wfh' => $wfhCount,
                'sakit' => $sakitCount,
                'izin' => $izinCount,
                'cuti' => $cutiCount,
                'alpha' => $alphaCount,
                'telat' => $telatCount,
                'pulang_cepat' => $pulangCepatCount
            ];

            // -- E. Akumulasi Grand Total --
            $grandTotal['total_hari'] += $totalHariBulanIni;
            $grandTotal['masuk'] += $masukCount;
            $grandTotal['wfh'] += $wfhCount;
            $grandTotal['sakit'] += $sakitCount;
            $grandTotal['izin'] += $izinCount;
            $grandTotal['cuti'] += $cutiCount;
            $grandTotal['alpha'] += $alphaCount;
            $grandTotal['telat'] += $telatCount;
            $grandTotal['pulang_cepat'] += $pulangCepatCount;
        }

        return view('attendance.summary', compact('user', 'selectedYear', 'monthsData', 'grandTotal'));
    }
}
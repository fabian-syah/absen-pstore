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

        // --- 1. SEARCH & SCOPE KARYAWAN (FIXED) ---
        $employees = collect([]);
        $targetUser = $currentUser; // Default: Diri sendiri

        // Daftar role yang BOLEH melihat history orang lain
        $allowedRoles = ['admin', 'audit', 'leader', 'admin_gaji'];

        // Cek apakah user yang login punya hak akses intip data orang
        if (in_array($currentUser->role, $allowedRoles)) {
            
            // A. Tentukan List Karyawan (Dropdown) berdasarkan Role
            if ($currentUser->role === 'audit' || $currentUser->role === 'leader') {
                // Hanya user di cabang yang dipegang
                $handledBranchIds = $currentUser->branches ? $currentUser->branches->pluck('id')->toArray() : [];
                if($currentUser->branch_id) {
                    $handledBranchIds[] = $currentUser->branch_id;
                }
                $employees = User::whereIn('branch_id', array_unique($handledBranchIds))
                                 ->orderBy('name', 'asc')
                                 ->get();
            } else {
                // Admin Pusat / Admin Gaji bisa lihat semua
                $employees = User::orderBy('name', 'asc')->get();
            }

            // B. Tentukan Siapa Target User yang Mau Dilihat
            if ($targetUserId) {
                // Cek apakah user target ada di dalam list employees yang diizinkan
                $foundUser = $employees->where('id', $targetUserId)->first();
                
                if ($foundUser) {
                    $targetUser = $foundUser;
                } else {
                    // Fallback Khusus Admin: Jika user tidak ada di dropdown (misal beda cabang), tapi admin mau paksa lihat via URL
                    if (in_array($currentUser->role, ['admin', 'admin_gaji'])) {
                        $directFind = User::find($targetUserId);
                        if($directFind) $targetUser = $directFind;
                    }
                }
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

            // LOGIKA 1: HITUNG DARI TABEL ATTENDANCE
            $telatFromAttendance = $monthAtt->filter(function($row) {
                return $row->is_late_checkin == true 
                        || strtolower($row->status) === 'late'
                        || str_contains(strtolower($row->presence_status ?? ''), 'telat');
            })->count();

            // --- PERBAIKAN LOGIKA HITUNG MASUK ---
$masukCount = $monthAtt->filter(function($row) {
    $st = strtolower($row->presence_status ?? '');
    
    // PERBAIKAN: Alpha HARUS dikecualikan secara eksplisit
    // Hanya hitung status yang benar-benar melakukan pekerjaan
    return in_array($st, ['masuk', 'wfh', 'dinas', 'izin telat', 'telat']) 
           && $st !== 'alpha'; 
})->count();

// --- PERBAIKAN LOGIKA HITUNG ALPHA ---
$alphaCount = $monthAtt->filter(function($q) {
    $st = strtolower($q->presence_status ?? '');
    // Hitung record yang statusnya memang Alpha atau record kosong yang dianggap sistem Alpha
    return $st == 'alpha' || $q->status == 'alpha';
})->count();

            $wfhCount = $monthAtt->filter(fn($q) => str_contains(strtolower($q->presence_status ?? ''), 'wfh'))->count();
            $pulangCepatCount = $monthAtt->where('is_early_checkout', true)->count();

            // LOGIKA 2: HITUNG DARI TABEL LEAVE REQUEST
            $cutiCount = 0; 
            $sakitCount = 0; 
            $izinCount = 0; 
            $telatFromLeave = 0; 
            $wfhFromLeaveCount = 0; 

            foreach ($leaves as $leave) {
                $start = Carbon::parse($leave->start_date);
                $end   = $leave->end_date ? Carbon::parse($leave->end_date) : $start->copy();
                $period = CarbonPeriod::create($start, $end);

                foreach ($period as $date) {
                    if ($date->month == $m && $date->year == $selectedYear) {
                        $alreadyInAttendance = $monthAtt->filter(fn($att) => $att->check_in_time->isSameDay($date))->isNotEmpty();

                        if ($leave->type == 'cuti') {
                            $cutiCount++;
                        } elseif ($leave->type == 'sakit') {
                            $sakitCount++;
                        } elseif ($leave->type == 'telat') {
                            $telatFromLeave++;
                        } elseif (strtolower($leave->type) == 'wfh') {
                            if (!$alreadyInAttendance) $wfhFromLeaveCount++;
                        } else {
                            $izinCount++;
                        }
                    }
                }
            }

            // AGGREGASI TOTAL
            $totalTelatBulanIni = $telatFromAttendance + $telatFromLeave;
            $totalWfhBulanIni = $wfhCount + $wfhFromLeaveCount;
            $totalMasukBulanIni = $masukCount + $wfhFromLeaveCount; 
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
                'telat' => $totalTelatBulanIni,
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
            'user' => $targetUser, // Ini sekarang sudah benar (bukan user login, tapi user yang dipilih)
            'selectedYear' => $selectedYear,
            'monthsData' => $monthsData,
            'grandTotal' => $grandTotal,
            'employees' => $employees,
            'isAccessGranted' => in_array($currentUser->role, $allowedRoles) // Flag akses untuk view
        ]);
    }
}
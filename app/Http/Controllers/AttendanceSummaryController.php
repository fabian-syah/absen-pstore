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

        // --- 1. LOGIKA SEARCH & SCOPE KARYAWAN (Admin & Audit) ---
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
                if ($foundUser) {
                    $targetUser = $foundUser;
                } else {
                    if($currentUser->role === 'audit') {
                         return redirect()->route('attendance.summary')->with('error', 'Karyawan tidak ditemukan di cabang anda.');
                    }
                    $targetUser = User::find($targetUserId) ?? $currentUser;
                }
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

        // --- 3. INISIALISASI VARIABEL ---
        $monthsData = [];
        $grandTotal = [
            'total_hari' => 0, 'masuk' => 0, 'wfh' => 0, 'sakit' => 0,
            'izin' => 0, 'cuti' => 0, 'alpha' => 0, 'telat' => 0, 'pulang_cepat' => 0
        ];

        // --- 4. LOOPING 12 BULAN ---
        for ($m = 1; $m <= 12; $m++) {
            $monthAtt = $attendances->filter(fn($q) => $q->check_in_time->month == $m);

            // LOGIKA PERBAIKAN DI SINI
            
            // 1. Hitung Masuk (Status Masuk, WFH, Dinas, atau Scan normal)
            $masukCount = $monthAtt->filter(function($row) {
                $status = strtolower($row->presence_status ?? '');
                // Masukkan 'telat' sebagai hadir juga (tapi nanti dihitung telatnya terpisah)
                return in_array($status, ['masuk', 'wfh', 'dinas', 'izin telat', 'telat']) 
                        || (in_array($row->attendance_type, ['scan', 'self', 'manual']) && !in_array($status, ['sakit', 'izin', 'cuti', 'alpha']));
            })->count();

            $wfhCount = $monthAtt->filter(fn($q) => str_contains(strtolower($q->presence_status), 'wfh'))->count();
            
            // 2. Hitung Alpha (Status Alpha)
            $alphaCount = $monthAtt->filter(fn($q) => strtolower($q->presence_status) == 'alpha' || $q->status == 'alpha')->count();
            
            // 3. Hitung Telat (PERBAIKAN UTAMA)
            // Cek kolom 'is_late_checkin' ATAU status string 'Telat' / 'late'
            $telatCount = $monthAtt->filter(function($row) {
                return $row->is_late_checkin == true 
                       || strtolower($row->presence_status) == 'telat' 
                       || $row->status == 'late';
            })->count();

            $pulangCepatCount = $monthAtt->where('is_early_checkout', true)->count();

            // Hitung Data Leave (Cuti/Sakit/Izin)
            $cutiCount = 0; $sakitCount = 0; $izinCount = 0; $wfhFromLeaveCount = 0; 

            foreach ($leaves as $leave) {
                $start = Carbon::parse($leave->start_date);
                $end   = $leave->end_date ? Carbon::parse($leave->end_date) : $start->copy();
                $period = CarbonPeriod::create($start, $end);

                foreach ($period as $date) {
                    if ($date->month == $m && $date->year == $selectedYear) {
                        $alreadyInAttendance = $monthAtt->filter(fn($att) => $att->check_in_time->isSameDay($date))->isNotEmpty();

                        if ($leave->type == 'cuti') $cutiCount++;
                        elseif ($leave->type == 'sakit') $sakitCount++;
                        elseif (strtolower($leave->type) == 'wfh') {
                            if (!$alreadyInAttendance) $wfhFromLeaveCount++;
                        } else $izinCount++;
                    }
                }
            }

            $totalWfhBulanIni = $wfhCount + $wfhFromLeaveCount;
            $totalMasukBulanIni = $masukCount + $wfhFromLeaveCount;
            // Total Hari = Masuk + Sakit + Izin + Cuti + Alpha (Telat sudah termasuk di Masuk, jadi jangan ditambah lagi)
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
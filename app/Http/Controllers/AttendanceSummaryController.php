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
        
        // Default bulan sesuai periode 26-25
        $now = Carbon::now($currentUser->branch?->timezone ?? 'Asia/Jakarta');
        if ($now->day >= 26) {
            $defaultPeriod = $now->copy()->addMonth();
        } else {
            $defaultPeriod = $now->copy();
        }
        $selectedYear = $request->get('year', $defaultPeriod->year);
        $selectedMonth = $request->get('month', $defaultPeriod->month);
        $targetUserId = $request->get('user_id');

        // --- 1. SEARCH & SCOPE KARYAWAN ---
        $employees = collect([]);
        $targetUser = $currentUser;
        $allowedRoles = ['admin', 'audit', 'leader', 'admin_gaji'];

        $isAccessGranted = in_array($currentUser->role, $allowedRoles);

        if ($isAccessGranted) {
            if ($currentUser->role === 'audit' || $currentUser->role === 'leader') {
                $handledBranchIds = $currentUser->branches ? $currentUser->branches->pluck('id')->toArray() : [];
                if ($currentUser->branch_id) {
                    $handledBranchIds[] = $currentUser->branch_id;
                }
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
                    if ($directFind)
                        $targetUser = $directFind;
                }
            }
        }

        // --- 2. AMBIL DATA ---
        $attendances = Attendance::where('user_id', $targetUser->id)
            ->where(function($q) use ($selectedYear) {
                $q->whereYear('check_in_time', $selectedYear)
                  ->orWhereYear('check_in_time', $selectedYear - 1);
            })
            ->get();

        $leaves = LeaveRequest::where('user_id', $targetUser->id)
            ->where('status', 'approved')
            ->where(function ($q) use ($selectedYear) {
                $q->whereYear('start_date', $selectedYear)
                    ->orWhereYear('end_date', $selectedYear)
                    ->orWhereYear('start_date', $selectedYear - 1)
                    ->orWhereYear('end_date', $selectedYear - 1);
            })
            ->get();

        // --- 3. AMBIL TIMEZONE ---
        $branchTimezone = $targetUser->branch?->timezone ?? 'Asia/Jakarta';

        // --- 4. PRE-PROCESS DATA (Group by Local Date) ---
        // Group attendances by local date to handle timezone shifts and multiple records per day
        $attendancesByDate = $attendances->groupBy(function ($att) use ($branchTimezone) {
            return Carbon::parse($att->check_in_time)->timezone($branchTimezone)->format('Y-m-d');
        });

        // Group leaves by local date (potentially multiple days per leave)
        $leavesByDate = collect();
        foreach ($leaves as $leave) {
            $start = Carbon::parse($leave->start_date);
            $end = $leave->end_date ? Carbon::parse($leave->end_date) : $start->copy();
            $period = CarbonPeriod::create($start, $end);
            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');
                if (!$leavesByDate->has($dateStr)) {
                    $leavesByDate->put($dateStr, collect());
                }
                $leavesByDate->get($dateStr)->push($leave);
            }
        }

        // --- 5. HITUNG PER BULAN ---
        $monthsData = [];
        $grandTotal = [
            'total_hari' => 0,
            'masuk' => 0,
            'wfh' => 0,
            'sakit' => 0,
            'izin' => 0,
            'cuti' => 0,
            'libur' => 0,
            'alpha' => 0,
            'telat' => 0,
            'pulang_cepat' => 0,
            'pending' => 0
        ];

        // --- 6. LOOPING 12 BULAN ---
        for ($m = 1; $m <= 12; $m++) {
            $masukCount = 0;
            $wfhCount = 0;
            $sakitCount = 0;
            $izinCount = 0;
            $cutiCount = 0;
            $liburCount = 0;
            $alphaCount = 0;
            $telatCount = 0;
            $pulangCepatCount = 0;
            $pendingCount = 0;

            // Tentukan range hari di bulan ini (26 bulan lalu - 25 bulan ini)
            $monthStart = Carbon::createFromDate($selectedYear, $m, 26, $branchTimezone)->subMonth()->startOfDay();
            $monthEnd = Carbon::createFromDate($selectedYear, $m, 25, $branchTimezone)->endOfDay();
            
            // Kita hanya hitung hari yang sudah lewat atau sedang berjalan (limitDate)
            $todayInBranch = Carbon::now($branchTimezone)->startOfDay();

            // Skip bulan yang belum dimulai sama sekali
            if ($monthStart->gt($todayInBranch)) {
                $monthsData[$m] = [
                    'name' => Carbon::create()->month($m)->translatedFormat('F'),
                    'total_hari' => 0, 'masuk' => 0, 'wfh' => 0, 'sakit' => 0,
                    'izin' => 0, 'cuti' => 0, 'libur' => 0, 'alpha' => 0,
                    'telat' => 0, 'pulang_cepat' => 0, 'pending' => 0
                ];
                continue;
            }

            $limitDate = ($monthEnd->gt($todayInBranch)) ? $todayInBranch : $monthEnd;
            
            // Period untuk iterasi hari demi hari agar akurat
            $period = CarbonPeriod::create($monthStart, $limitDate);

            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');
                
                // 1. Ambil Attendance "Terbaik" untuk hari ini
                $dayAtts = $attendancesByDate->get($dateStr, collect());
                
                // Prioritas: Manual/Scan/Self > Leave > System Alpha
                $bestAtt = $dayAtts->filter(fn($a) => $a->attendance_type !== 'system')->sortBy(function($a) {
                    $s = strtolower($a->presence_status ?? '');
                    if (in_array($s, ['masuk', 'wfh', 'dinas', 'telat', 'izin telat'])) return 0;
                    return 1;
                })->first();

                // Jika tidak ada data real, cek apakah ada System Alpha
                if (!$bestAtt) {
                    $bestAtt = $dayAtts->filter(fn($a) => $a->attendance_type === 'system' && strtolower($a->presence_status) === 'alpha')->first();
                }

                // 2. Cek Izin/Cuti untuk hari ini
                $dayLeave = $leavesByDate->get($dateStr, collect())->first();

                if ($bestAtt && $bestAtt->attendance_type !== 'system') {
                    // Ada data absensi real
                    $status = strtolower($bestAtt->presence_status ?? '');

                    if ($status == 'masuk' || $status == 'telat' || $status == 'izin telat' || str_contains($status, 'hadir')) {
                        $masukCount++;
                        if ($bestAtt->is_late_checkin || str_contains($status, 'telat')) {
                            $telatCount++;
                        }
                    } elseif (str_contains($status, 'wfh') || str_contains($status, 'dinas')) {
                        $masukCount++;
                        $wfhCount++;
                    } elseif ($status == 'sakit') {
                        $sakitCount++;
                    } elseif ($status == 'izin') {
                        $izinCount++;
                    } elseif ($status == 'cuti') {
                        $cutiCount++;
                    } elseif ($status == 'libur') {
                        $liburCount++;
                    }

                    if ($bestAtt->is_early_checkout) {
                        $pulangCepatCount++;
                    }
                    if ($bestAtt->status == 'pending_verification') {
                        $pendingCount++;
                    }
                } elseif ($dayLeave) {
                    // Tidak ada absensi real tapi ada Izin/Cuti
                    $leaveType = strtolower($dayLeave->type);

                    if ($leaveType == 'telat') {
                        $masukCount++;
                        $telatCount++;
                    } elseif ($leaveType == 'sakit') {
                        $sakitCount++;
                    } elseif ($leaveType == 'izin') {
                        $izinCount++;
                    } elseif ($leaveType == 'cuti') {
                        $cutiCount++;
                    } elseif ($leaveType == 'libur') {
                        $liburCount++;
                    } elseif ($leaveType == 'wfh') {
                        $masukCount++;
                        $wfhCount++;
                    }
                } elseif ($bestAtt && $bestAtt->attendance_type === 'system') {
                    // Hanya ada record System Alpha
                    $alphaCount++;
                }
                // Jika benar-benar kosong (dan sudah lewat hari), Alpha biasanya dihitung oleh system command.
                // Tapi untuk summary, kita hanya hitung yang terekam di DB (baik Alpha sistem maupun status Alpha).
            }

            // Total hari (yang memiliki status kehadiran)
            $totalHari = $masukCount + $sakitCount + $izinCount + $cutiCount + $liburCount + $alphaCount;

            $monthsData[$m] = [
                'name' => Carbon::create()->month($m)->translatedFormat('F'),
                'total_hari' => $totalHari,
                'masuk' => $masukCount,
                'wfh' => $wfhCount,
                'sakit' => $sakitCount,
                'izin' => $izinCount,
                'cuti' => $cutiCount,
                'libur' => $liburCount,
                'alpha' => $alphaCount,
                'telat' => $telatCount,
                'pulang_cepat' => $pulangCepatCount,
                'pending' => $pendingCount
            ];

            // Update grand total
            foreach ($grandTotal as $key => $val) {
                $grandTotal[$key] += $monthsData[$m][$key] ?? 0;
            }
        }

        // --- 7. DATA UNTUK BOX ATAS ---
        $rawSummary = $monthsData[$selectedMonth];
        $summary = [
            'present' => $rawSummary['masuk'],
            'sakit' => $rawSummary['sakit'],
            'izin' => $rawSummary['izin'],
            'alpha' => $rawSummary['alpha'],
            'total' => $rawSummary['total_hari'],
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
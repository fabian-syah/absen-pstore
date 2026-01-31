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
            ->whereYear('check_in_time', $selectedYear)
            ->get();

        $leaves = LeaveRequest::where('user_id', $targetUser->id)
            ->where('status', 'approved')
            ->where(function ($q) use ($selectedYear) {
                $q->whereYear('start_date', $selectedYear)
                    ->orWhereYear('end_date', $selectedYear);
            })
            ->get();

        // --- 3. HITUNG PER BULAN ---
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

        // --- 4. LOOPING 12 BULAN ---
        for ($m = 1; $m <= 12; $m++) {
            // Data dari Attendance
            $monthAtt = $attendances->filter(
                fn($q) =>
                $q->check_in_time->month == $m && $q->check_in_time->year == $selectedYear
            );

            // Data dari LeaveRequest untuk bulan ini
            $monthLeaves = $leaves->filter(function ($leave) use ($m, $selectedYear) {
                $start = Carbon::parse($leave->start_date);
                $end = $leave->end_date ? Carbon::parse($leave->end_date) : $start->copy();

                $period = CarbonPeriod::create($start, $end);
                foreach ($period as $date) {
                    if ($date->month == $m && $date->year == $selectedYear) {
                        return true;
                    }
                }
                return false;
            });

            // Hitung dari Attendance
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

            // Proses setiap attendance
            foreach ($monthAtt as $att) {
                $date = $att->check_in_time->format('Y-m-d');

                // Cek apakah ada leave untuk tanggal ini
                $leaveForDate = $monthLeaves->filter(function ($leave) use ($date) {
                    $start = Carbon::parse($leave->start_date);
                    $end = $leave->end_date ? Carbon::parse($leave->end_date) : $start->copy();
                    $period = CarbonPeriod::create($start, $end);

                    foreach ($period as $d) {
                        if ($d->format('Y-m-d') == $date) {
                            return true;
                        }
                    }
                    return false;
                })->first();

                if ($leaveForDate) {
                    // Ada leave, gunakan tipe leave
                    $leaveType = strtolower($leaveForDate->type);

                    if ($leaveType == 'telat') {
                        // Telat special case: attendance tetap masuk tapi status telat
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
                } else {
                    // Tidak ada leave, gunakan attendance
                    $status = strtolower($att->presence_status ?? '');

                    if ($status == 'masuk' || $status == 'telat' || $status == 'izin telat') {
                        $masukCount++;
                        if ($att->is_late_checkin) {
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
                    } elseif ($status == 'alpha') {
                        $alphaCount++;
                    }

                    if ($att->is_early_checkout) {
                        $pulangCepatCount++;
                    }

                    if ($att->status == 'pending_verification') {
                        $pendingCount++;
                    }
                }
            }

            // Hitung leaves yang tidak memiliki attendance
            $processedDates = $monthAtt->map(fn($att) => $att->check_in_time->format('Y-m-d'))->toArray();

            foreach ($monthLeaves as $leave) {
                $start = Carbon::parse($leave->start_date);
                $end = $leave->end_date ? Carbon::parse($leave->end_date) : $start->copy();
                $period = CarbonPeriod::create($start, $end);

                foreach ($period as $date) {
                    if ($date->month == $m && $date->year == $selectedYear) {
                        $dateStr = $date->format('Y-m-d');

                        // Skip jika sudah diproses di attendance
                        if (in_array($dateStr, $processedDates))
                            continue;

                        $leaveType = strtolower($leave->type);

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
                    }
                }
            }

            // Total hari
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

        // --- 5. DATA UNTUK BOX ATAS ---
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
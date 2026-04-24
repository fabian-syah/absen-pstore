<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\WorkSchedule;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Barryvdh\DomPDF\Facade\Pdf;

class AttendanceHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($request->has('employeeId') && in_array($user->role, ['audit', 'admin', 'leader', 'admin_gaji'])) {
            $targetUser = User::find($request->employeeId);
            $employee = $targetUser;
        } else {
            $targetUser = $user;
            $employee = null;
        }

        if (!$targetUser) {
            return back()->with('error', 'Karyawan tidak ditemukan');
        }

        $selectedMonth = $request->get('month', date('m'));
        $selectedYear = $request->get('year', date('Y'));

        $currentDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1);
        $prevDate = $currentDate->copy()->subMonth();
        $nextDate = $currentDate->copy()->addMonth();

        $data = $this->getHistoryData($targetUser, $selectedMonth, $selectedYear);

        return view('attendance.history', array_merge($data, [
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'employee' => $employee,
            'prevMonth' => $prevDate->month,
            'prevYear' => $prevDate->year,
            'nextMonth' => $nextDate->month,
            'nextYear' => $nextDate->year,
        ]));
    }

    private function getHistoryData($user, $selectedMonth, $selectedYear)
    {
        // 1. Ambil timezone cabang
        $branchTimezone = $user->branch?->timezone ?? 'Asia/Jakarta';

        // 2. Tentukan range awal bulan (Calendar Month untuk tampilan riwayat)
        $startDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1, $branchTimezone)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // 3. Ambil "Hari Ini" di cabang tersebut
        $todayInBranch = Carbon::now($branchTimezone)->startOfDay();

        // 4. Query Database (Pindah ke atas agar data tersedia untuk penentuan limit)
        $attendances = Attendance::with(['verifier', 'scanner', 'scannerOut', 'user'])
            ->where('user_id', $user->id)
            ->whereBetween('check_in_time', [
                $startDate->copy()->subDay()->startOfDay(),
                $endDate->copy()->addDay()->endOfDay()
            ])
            ->get();

        $leaves = LeaveRequest::with('verifier')
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->where(function ($query) use ($startDate, $endDate) {
                $s = $startDate->format('Y-m-d');
                $e = $endDate->format('Y-m-d');
                $query->where('start_date', '<=', $e)
                    ->where(function ($q) use ($s) {
                    $q->whereNull('end_date')
                        ->orWhere('end_date', '>=', $s);
                });
            })->get();

        // 5. Logika Penentuan Limit (Sekarang variabel sudah didefinisikan)
        $isCurrentMonth = ($selectedMonth == Carbon::now($branchTimezone)->month && $selectedYear == Carbon::now($branchTimezone)->year);
        if ($isCurrentMonth) {
            // Hanya tampilkan hari ini jika sudah ada absen masuk/leave hari ini,
            // atau jika sudah melewati ambang dini hari (4 pagi) agar tidak muncul Alpha prematur.
            $hasActivityToday = $attendances->first(fn($a) => Carbon::parse($a->check_in_time)->timezone($branchTimezone)->isToday()) ||
                                $leaves->first(fn($l) => Carbon::parse($l->start_date)->isToday());

            if (!$hasActivityToday && Carbon::now($branchTimezone)->hour < 4) {
                $limitDate = $todayInBranch->copy()->subDay();
            } else {
                $limitDate = $todayInBranch;
            }
        } else {
            $limitDate = ($endDate->gt(Carbon::now($branchTimezone))) ? $todayInBranch : $endDate;
        }

        $historyCollection = collect();

        // 6. Buat period (PENTING: Gunakan startOfDay agar tidak terpotong jam)
        $period = CarbonPeriod::create($startDate->copy()->startOfDay(), $limitDate->copy()->startOfDay());

        foreach ($period as $date) {
            $currentDateStr = $date->format('Y-m-d');

            // === PASS 1: Cari attendance REAL (skip system Alpha) untuk hari ini ===
            $att = $attendances->filter(function ($a) use ($currentDateStr, $branchTimezone) {
                // Skip system-generated Alpha agar Pass 2 (shift malam) bisa jalan
                if ($a->attendance_type === 'system' && strtolower($a->presence_status) === 'alpha') return false;
                return Carbon::parse($a->check_in_time)->timezone($branchTimezone)->format('Y-m-d') === $currentDateStr;
            })->sortBy(fn($a) => $a->attendance_type == 'system' ? 1 : 0)->first();

            // === PASS 2: Jika tidak ada, cek Shift Malam yang MASIH BERJALAN dari kemarin ===
            $nightShift = null;
            if (!$att) {
                $nightShift = $attendances->first(function ($a) use ($date, $branchTimezone) {
                    $checkIn = Carbon::parse($a->check_in_time)->timezone($branchTimezone);
                    $checkOut = $a->check_out_time ? Carbon::parse($a->check_out_time)->timezone($branchTimezone) : null;

                    if ($checkIn->format('Y-m-d') !== $date->copy()->subDay()->format('Y-m-d')) return false;
                    // Hanya sistem Alpha yang di-skip, bukan absen real
                    if ($a->attendance_type === 'system' && strtolower($a->presence_status) === 'alpha') return false;

                    if (!$checkOut) return $checkIn->diffInHours(Carbon::now($branchTimezone)) < 24;
                    return false;
                });
            }

            // === PASS 3: Cek apakah hari ini adalah hari "Pulang" dari shift malam kemarin ===
            $endedShift = null;
            if (!$att && !$nightShift) {
                $endedShift = $attendances->first(function ($a) use ($date, $branchTimezone) {
                    $checkIn = Carbon::parse($a->check_in_time)->timezone($branchTimezone);
                    $checkOut = $a->check_out_time ? Carbon::parse($a->check_out_time)->timezone($branchTimezone) : null;

                    if (!$checkOut) return false;

                    // Apakah check_in kemarin DAN check_out hari ini?
                    return $checkIn->format('Y-m-d') === $date->copy()->subDay()->format('Y-m-d') &&
                           $checkOut->format('Y-m-d') === $date->format('Y-m-d');
                });
            }

            // Cari leave
            $leave = $leaves->filter(function ($l) use ($date, $branchTimezone) {
                $lStart = Carbon::parse($l->start_date, $branchTimezone)->startOfDay();
                $lEnd = Carbon::parse($l->end_date ?? $l->start_date, $branchTimezone)->endOfDay();
                return $date->between($lStart, $lEnd);
            })->first();

            if ($att) {
                // Normal attendance
                $displayAtt = clone $att;
                $displayAtt->check_in_time = Carbon::parse($att->check_in_time)->timezone($branchTimezone);
                if ($att->check_out_time) {
                    $displayAtt->check_out_time = Carbon::parse($att->check_out_time)->timezone($branchTimezone);
                }
                if ($leave) {
                    $displayAtt->setRelation('leaveRequest', $leave);
                }
                $historyCollection->push($displayAtt);

            } elseif ($nightShift) {
                // Shift Malam dari kemarin (Masih Berjalan)
                $checkInLocal = Carbon::parse($nightShift->check_in_time)->timezone($branchTimezone);
                $checkOutLocal = $nightShift->check_out_time ? Carbon::parse($nightShift->check_out_time)->timezone($branchTimezone) : null;

                $shiftAtt = new Attendance();
                $shiftAtt->user_id = $user->id;
                $shiftAtt->user = $user;
                $shiftAtt->check_in_time = null;
                $shiftAtt->check_out_time = $checkOutLocal;
                $shiftAtt->presence_status = $nightShift->presence_status ?? 'Masuk';
                $shiftAtt->status = $nightShift->status;
                $shiftAtt->attendance_type = $nightShift->attendance_type;
                $shiftAtt->notes = 'Lanjutan Shift Malam (Masuk: ' . $checkInLocal->format('d/m H:i') . ')';

                // COPY METADATA
                $shiftAtt->photo_path = $nightShift->photo_path;
                $shiftAtt->photo_out_path = $nightShift->photo_out_path;
                $shiftAtt->latitude = $nightShift->latitude;
                $shiftAtt->longitude = $nightShift->longitude;
                $shiftAtt->latitude_out = $nightShift->latitude_out;
                $shiftAtt->longitude_out = $nightShift->longitude_out;
                $shiftAtt->scanned_by_user_id = $nightShift->scanned_by_user_id;
                $shiftAtt->scanned_out_by_user_id = $nightShift->scanned_out_by_user_id;
                $shiftAtt->verified_by_user_id = $nightShift->verified_by_user_id;

                $shiftAtt->setRelation('scanner', $nightShift->scanner);
                $shiftAtt->setRelation('scannerOut', $nightShift->scannerOut);
                $shiftAtt->setRelation('verifier', $nightShift->verifier);
                if ($leave) {
                    $shiftAtt->setRelation('leaveRequest', $leave);
                }
                $historyCollection->push($shiftAtt);

            } else {
                // Jika tidak ada attendance (Alpha / Leave / Selesai Shift)
                $fakeAtt = new Attendance();
                $fakeAtt->user_id = $user->id;
                $fakeAtt->user = $user;
                $fakeAtt->check_in_time = $date->copy()->startOfDay();

                if ($leave) {
                    $presenceStatusMap = [
                        'telat' => 'Izin Telat',
                        'wfh' => 'WFH',
                        'dinas' => 'Dinas Luar',
                        'izin' => 'Izin',
                        'sakit' => 'Sakit',
                        'cuti' => 'Cuti',
                        'libur' => 'Libur',
                    ];
                    $fakeAtt->presence_status = $presenceStatusMap[$leave->type] ?? ucfirst($leave->type);
                    if ($leave->type == 'telat') {
                        $fakeAtt->is_late_checkin = true;
                        // FIX: Gunakan jam dari leave (start_time) agar tidak 00:00
                        if ($leave->start_time) {
                            $fakeAtt->check_in_time = Carbon::parse($currentDateStr . ' ' . $leave->start_time);
                        }
                    }
                    $fakeAtt->attendance_type = 'leave';
                    $fakeAtt->notes = $leave->reason;
                    $fakeAtt->setRelation('leaveRequest', $leave);
                    $fakeAtt->setRelation('verifier', $leave->verifier);
                } elseif ($endedShift && !$date->isToday()) {
                    // Tampilkan sebagai "Masuk" dengan menyalin data asli agar lengkap
                    $fakeAtt->presence_status = 'Masuk';
                    $fakeAtt->attendance_type = $endedShift->attendance_type;
                    $fakeAtt->notes = 'Masuk (Lanjutan Shift Malam)';
                    
                    // Foto & Lokasi (Copy dari record asli)
                    $fakeAtt->photo_path = $endedShift->photo_path;
                    $fakeAtt->photo_out_path = $endedShift->photo_out_path;
                    $fakeAtt->latitude = $endedShift->latitude;
                    $fakeAtt->longitude = $endedShift->longitude;
                    $fakeAtt->latitude_out = $endedShift->latitude_out;
                    $fakeAtt->longitude_out = $endedShift->longitude_out;

                    // Timestamps & Relasi
                    $fakeAtt->check_out_time = Carbon::parse($endedShift->check_out_time)->timezone($branchTimezone);
                    $fakeAtt->setRelation('scanner', $endedShift->scanner);
                    $fakeAtt->setRelation('scannerOut', $endedShift->scannerOut);
                    $fakeAtt->setRelation('verifier', $endedShift->verifier);
                } else {
                    $fakeAtt->presence_status = 'Alpha';
                    $fakeAtt->attendance_type = 'system';
                }
                $fakeAtt->status = 'verified';
                $historyCollection->push($fakeAtt);
            }
        }

        $history = $historyCollection->sortByDesc(function ($item) {
            // Gunakan check_in_time, jika null (shift malam) gunakan check_out_time sebagai patokan urutan
            $time = $item->check_in_time ?? $item->check_out_time;
            return $time ? $time->timestamp : 0;
        });

        // 7. Kalkulasi Summary
        $summary = [
            'total' => $history->count(),
            'present' => $history->filter(function ($item) {
                $s = strtolower($item->presence_status ?? '');
                return in_array($s, ['masuk', 'wfh', 'dinas', 'izin telat', 'telat', 'telat hadir']) || str_contains($s, 'telat');
            })->count(),
            'sakit' => $history->filter(fn($i) => strtolower($i->presence_status ?? '') === 'sakit')->count(),
            'izin' => $history->filter(fn($i) => strtolower($i->presence_status ?? '') === 'izin')->count(),
            'cuti' => $history->filter(fn($i) => strtolower($i->presence_status ?? '') === 'cuti')->count(),
            'libur' => $history->filter(fn($i) => strtolower($i->presence_status ?? '') === 'libur')->count(),
            'alpha' => $history->filter(fn($i) => strtolower($i->presence_status ?? '') === 'alpha')->count(),
            'telat' => $history->filter(function ($item) {
                return $item->is_late_checkin == true || str_contains(strtolower($item->presence_status ?? ''), 'telat');
            })->count(),
            'pulang_cepat' => $history->where('is_early_checkout', true)->count(),
            'pending' => $history->where('status', 'pending_verification')->count(),
        ];

        return ['history' => $history, 'summary' => $summary];
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        $targetUser = $request->has('employeeId') ? User::find($request->employeeId) : $user;
        $data = $this->getHistoryData($targetUser, $request->month, $request->year);

        // Fix Undefined MonthName
        $monthName = \Carbon\Carbon::createFromDate($request->year, $request->month, 1)->translatedFormat('F Y');

        // Fix Missing Summary Key 'hadir' (Controller uses 'present')
        $data['summary']['hadir'] = $data['summary']['present'];

        // Pass to View
        $pdf = Pdf::loadView('attendance.export_pdf', array_merge($data, [
            'user' => $targetUser,
            'monthName' => $monthName
        ]));
        return $pdf->download('Laporan_' . $targetUser->name . '_' . $monthName . '.pdf');
    }

    public function updateByAudit(Request $request, $id)
    {
        if (!in_array(Auth::user()->role, ['audit', 'admin'])) {
            abort(403, 'Akses Ditolak.');
        }

        $attendance = Attendance::findOrFail($id);
        $request->validate([
            'presence_status' => 'required|string',
            'check_in_time' => 'required',
            'check_out_time' => 'nullable',
            'status' => 'required|in:verified,pending_verification,rejected',
            'audit_note' => 'nullable|string',
            'audit_photo' => 'nullable|image|max:8192'
        ]);

        $branchTimezone = $attendance->user->branch?->timezone ?? 'Asia/Jakarta';
        $originalDateLocal = Carbon::parse($attendance->check_in_time)->timezone($branchTimezone)->format('Y-m-d');
        
        $newCheckInLocal = Carbon::createFromFormat('Y-m-d H:i', $originalDateLocal . ' ' . $request->check_in_time, $branchTimezone);
        $newCheckIn = $newCheckInLocal->copy()->setTimezone(config('app.timezone'));

        $newCheckOut = null;
        if ($request->check_out_time) {
            $newCheckOutLocal = Carbon::createFromFormat('Y-m-d H:i', $originalDateLocal . ' ' . $request->check_out_time, $branchTimezone);
            if ($newCheckOutLocal->lt($newCheckInLocal)) {
                $newCheckOutLocal->addDay();
            }
            $newCheckOut = $newCheckOutLocal->copy()->setTimezone(config('app.timezone'));
        }

        $isLate = false;
        if ($request->presence_status == 'Masuk' && $attendance->scheduled_check_in) {
            $scheduleIn = Carbon::parse($originalDateLocal . ' ' . $attendance->scheduled_check_in, $branchTimezone);
            $isLate = $newCheckIn->gt($scheduleIn);
        }

        $auditPhotoPath = $attendance->audit_photo_path;
        if ($request->hasFile('audit_photo')) {
            $auditPhotoPath = $request->file('audit_photo')->store('audit-proofs', 'public');
        }

        $attendance->update([
            'presence_status' => $request->presence_status,
            'check_in_time' => $newCheckIn,
            'check_out_time' => $newCheckOut,
            'status' => $request->status,
            'is_late_checkin' => $isLate,
            'audit_note' => $request->audit_note,
            'audit_photo_path' => $auditPhotoPath,
            'verified_by_user_id' => Auth::id(),
            'attendance_type' => 'manual',
        ]);

        // [SYNC CUTI]
        $this->syncWithLeaveRequest($attendance);

        return back()->with('success', 'Data absensi berhasil diperbarui.');
    }

    /**
     * Helper untuk sinkronisasi dengan tabel LeaveRequest agar saldo cuti terupdate
     */
    private function syncWithLeaveRequest($attendance)
    {
        $branchTimezone = $attendance->user->branch?->timezone ?? 'Asia/Jakarta';
        $date = Carbon::parse($attendance->check_in_time)->timezone($branchTimezone)->format('Y-m-d');
        $userId = $attendance->user_id;
        $status = strtolower($attendance->presence_status ?? '');

        if ($status === 'cuti') {
            // Jika status dIubah ke Cuti, pastikan ada record di LeaveRequest agar saldo terpotong
            if (!LeaveRequest::where('user_id', $userId)->whereDate('start_date', $date)->where('type', 'cuti')->where('status', 'approved')->exists()) {
                LeaveRequest::create([
                    'user_id' => $userId,
                    'type' => 'cuti',
                    'start_date' => $date,
                    'end_date' => $date,
                    'status' => 'approved',
                    'approved_by' => Auth::id(),
                    'reason' => 'Sinkronisasi Koreksi Audit (Manual)',
                    'is_active' => true,
                ]);
            }
        } else {
            // Jika status diubah dari Cuti ke status lain, hapus record cuti di tanggal tersebut
            LeaveRequest::where('user_id', $userId)
                ->where('type', 'cuti')
                ->where(function ($q) use ($date) {
                    $q->whereDate('start_date', $date)
                        ->orWhere(function ($q2) use ($date) {
                            $q2->whereDate('start_date', '<=', $date)
                                ->whereDate('end_date', '>=', $date);
                        });
                })
                ->delete();
        }
    }
}
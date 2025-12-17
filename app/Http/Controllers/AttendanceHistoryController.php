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
    /**
     * Menampilkan Halaman Riwayat Absensi
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // [FIXING] Tambahkan 'admin_gaji' ke dalam array izin akses view orang lain
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

        // Ambil Parameter Bulan & Tahun
        $selectedMonth = $request->get('month', date('m'));
        $selectedYear = $request->get('year', date('Y'));

        // Logic Navigasi Bulan
        $currentDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1);
        $prevDate = $currentDate->copy()->subMonth();
        $nextDate = $currentDate->copy()->addMonth();

        $prevMonth = $prevDate->month;
        $prevYear  = $prevDate->year;
        $nextMonth = $nextDate->month;
        $nextYear  = $nextDate->year;

        // Ambil Data
        $data = $this->getHistoryData($targetUser, $selectedMonth, $selectedYear);

        $history = $data['history'];
        $summary = $data['summary'];

        return view('attendance.history', compact(
            'history',
            'summary',
            'selectedMonth',
            'selectedYear',
            'employee',
            'prevMonth',
            'prevYear',
            'nextMonth',
            'nextYear'
        ));
    }

    /**
     * Export Riwayat ke PDF
     */
    public function exportPdf(Request $request)
    {
        $user = Auth::user();

        if ($request->has('employeeId') && in_array($user->role, ['audit', 'admin', 'leader'])) {
            $targetUser = User::find($request->employeeId);
        } else {
            $targetUser = $user;
        }

        if (!$targetUser) {
            return back()->with('error', 'User tidak ditemukan.');
        }

        $selectedMonth = $request->get('month', date('m'));
        $selectedYear = $request->get('year', date('Y'));

        $data = $this->getHistoryData($targetUser, $selectedMonth, $selectedYear);

        $pdf = Pdf::loadView('attendance.export_pdf', [
            'history' => $data['history'],
            'summary' => $data['summary'],
            'user' => $targetUser,
            'monthName' => Carbon::createFromDate($selectedYear, $selectedMonth, 1)->translatedFormat('F Y')
        ]);

        return $pdf->download('Laporan_Absensi_' . $targetUser->name . '_' . $selectedMonth . '-' . $selectedYear . '.pdf');
    }

    /**
     * Core Logic: Menggabungkan Data Absensi Real & Data Izin (Leave)
     */
    private function getHistoryData($user, $selectedMonth, $selectedYear)
    {
        // 1. AMBIL DATA ABSENSI BULAN INI + DATA AKHIR BULAN LALU (Untuk cek lembur lintas hari)
        $startDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->subDay(); // H-1
        $endDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->endOfMonth();

        $attendances = Attendance::with(['verifier', 'scanner', 'user'])
            ->where('user_id', $user->id)
            ->whereBetween('check_in_time', [$startDate, $endDate])
            ->orderBy('check_in_time', 'asc')
            ->get();

        // 2. AMBIL DATA IZIN (APPROVED)
        $leaves = LeaveRequest::with('verifier')
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('is_active', true)
            ->where(function ($q) use ($selectedMonth, $selectedYear) {
                $q->whereMonth('start_date', $selectedMonth)->whereYear('start_date', $selectedYear)
                    ->orWhere(function ($subQ) use ($selectedMonth, $selectedYear) {
                        $subQ->whereMonth('end_date', $selectedMonth)->whereYear('end_date', $selectedYear);
                    });
            })
            ->get();

        $historyCollection = collect();

        // --- PROSES DATA ABSENSI REAL ---
        foreach ($attendances as $index => $att) {
            // Hanya masukkan ke history jika check_in_time masuk bulan yang dipilih
            if ($att->check_in_time->month == $selectedMonth) {

                // === LOGIKA VALIDASI LEMBUR LINTAS HARI ===
                $att->is_excused_late = false;

                // Cari absen kemarin
                $yesterday = $att->check_in_time->copy()->subDay()->format('Y-m-d');
                $prevAtt = $attendances->filter(function ($a) use ($yesterday) {
                    return $a->check_in_time->format('Y-m-d') == $yesterday;
                })->first();

                if ($prevAtt && $prevAtt->check_out_time) {
                    // Ambang batas: Pulang di atas jam 02:00 pagi hari ini
                    $thresholdTime = $att->check_in_time->copy()->setTime(2, 0, 0);

                    if ($prevAtt->check_out_time->gt($thresholdTime)) {
                        $att->is_excused_late = true;
                        $att->overtime_reason = "Pulang s/d " . $prevAtt->check_out_time->format('H:i');
                    }
                }

                $historyCollection->push($att);
            }
        }

        // --- PROSES DATA CUTI/IZIN (GABUNGKAN KE HISTORY) ---
        foreach ($leaves as $leave) {
            $start = Carbon::parse($leave->start_date);
            $end = $leave->end_date ? Carbon::parse($leave->end_date) : $start;
            $period = CarbonPeriod::create($start, $end);

            foreach ($period as $date) {
                if ($date->month == $selectedMonth && $date->year == $selectedYear) {
                    // Cek Konflik: Apakah tanggal ini sudah ada absen real?
                    $exists = $historyCollection->filter(function ($a) use ($date) {
                        return $a->check_in_time->isSameDay($date);
                    })->isNotEmpty();

                    // Jika belum ada absen real, buat data attendance dummy dari Izin
                    if (!$exists) {
                        $fakeAtt = new Attendance();
                        $fakeAtt->id = 'leave_' . $leave->id . '_' . $date->timestamp;
                        $fakeAtt->user_id = $user->id;
                        $fakeAtt->check_in_time = $date->copy()->setTime(8, 0, 0);
                        $fakeAtt->check_out_time = null;

                        $typeLabel = ucfirst($leave->type);
                        if ($leave->type == 'telat') $typeLabel = 'Izin Telat';
                        if ($leave->type == 'wfh') $typeLabel = 'WFH';

                        $fakeAtt->presence_status = $typeLabel;
                        $fakeAtt->status = 'verified';
                        $fakeAtt->attendance_type = 'leave';

                        // [FIXING DISINI]
                        // Jika izinnya tipe 'telat', set is_late_checkin = true
                        // Agar terhitung di summary bawah
                        $fakeAtt->is_late_checkin = ($leave->type == 'telat');

                        $fakeAtt->is_early_checkout = false;
                        $fakeAtt->photo_path = null;
                        $fakeAtt->photo_out_path = null;
                        $fakeAtt->audit_photo_path = null;
                        $fakeAtt->audit_note = "Pengajuan: " . $leave->reason;
                        $fakeAtt->latitude = null;
                        $fakeAtt->longitude = null;

                        $fakeAtt->setRelation('leaveRequest', $leave);
                        $fakeAtt->setRelation('verifier', $leave->verifier);
                        $fakeAtt->setRelation('user', $user);

                        $historyCollection->push($fakeAtt);
                    }
                }
            }
        }

        $history = $historyCollection->sortByDesc('check_in_time');

        // HITUNG SUMMARY
        $summary = [
            'total' => $history->count(),
            'hadir' => $history->filter(function ($item) {
                $s = strtolower($item->presence_status ?? '');
                $isExplicitPresent = in_array($s, ['masuk', 'wfh', 'izin telat']) || str_contains($s, 'dinas');
                $isImplicitPresent = empty($s) && in_array($item->attendance_type, ['scan', 'self', 'manual']);
                return $isExplicitPresent || $isImplicitPresent;
            })->count(),
            'sakit' => $history->filter(function ($i) {
                return strtolower($i->presence_status ?? '') === 'sakit';
            })->count(),
            'izin' => $history->filter(function ($i) {
                return in_array(strtolower($i->presence_status ?? ''), ['izin', 'cuti']);
            })->count(),
            'alpha' => $history->filter(function ($i) {
                return strtolower($i->presence_status ?? '') === 'alpha';
            })->count(),

            // Perhitungan Telat (Sekarang akan menghitung Izin Telat juga)
            'telat' => $history->where('is_late_checkin', true)->count(),

            'pulang_cepat' => $history->where('is_early_checkout', true)->count(),
            'pending' => $history->where('status', 'pending_verification')->count(),
        ];

        return ['history' => $history, 'summary' => $summary];
    }

    /**
     * Update Data Absensi oleh Audit
     */
    public function updateByAudit(Request $request, $id)
    {
        if (Auth::user()->role !== 'audit') {
            abort(403, 'Akses Ditolak.');
        }

        $request->validate([
            'presence_status' => 'required|string',
            'check_in_time'   => 'required',
            'check_out_time'  => 'nullable',
            'status'          => 'required|in:verified,pending_verification,rejected',
            'audit_note'      => 'nullable|string',
            'audit_photo'     => 'nullable|image|max:2048'
        ]);

        $attendance = Attendance::findOrFail($id);

        $originalDate = $attendance->check_in_time->format('Y-m-d');
        $newCheckIn = Carbon::parse($originalDate . ' ' . $request->check_in_time);

        $checkOutTimeStr = $request->check_out_time;
        $newCheckOut = null;

        if ($checkOutTimeStr) {
            $newCheckOut = Carbon::parse($originalDate . ' ' . $checkOutTimeStr);
            if ($newCheckOut->lt($newCheckIn)) {
                $newCheckOut->addDay();
            }
        }

        $workSchedule = WorkSchedule::getScheduleForUser($attendance->user_id);
        $isLate = $attendance->is_late_checkin;

        // Logic hitung telat manual saat edit
        if ($newCheckIn->format('Y-m-d') >= '2025-12-01') {
            if ($workSchedule && $request->presence_status == 'Masuk') {
                $scheduleStart = Carbon::parse($originalDate . ' ' . $workSchedule->check_in_end);
                $isLate = $newCheckIn->gt($scheduleStart);
            }
        } else {
            $isLate = false;
        }

        $auditPhotoPath = $attendance->audit_photo_path;
        if ($request->hasFile('audit_photo')) {
            $auditPhotoPath = $request->file('audit_photo')->store('audit-proofs', 'public');
        }

        $attendance->update([
            'presence_status'     => $request->presence_status,
            'check_in_time'       => $newCheckIn,
            'check_out_time'      => $newCheckOut,
            'status'              => $request->status,
            'is_late_checkin'     => $isLate,
            'audit_note'          => $request->audit_note,
            'audit_photo_path'    => $auditPhotoPath,
            'verified_by_user_id' => ($request->status == 'verified') ? Auth::id() : null,
            'attendance_type'     => ($attendance->presence_status == 'Alpha' && $request->presence_status != 'Alpha') ? 'manual' : $attendance->attendance_type,
        ]);

        return back()->with('success', 'Data absensi berhasil diperbarui oleh Audit.');
    }
}

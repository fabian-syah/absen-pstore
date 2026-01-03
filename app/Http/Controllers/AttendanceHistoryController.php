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

        // Ambil Data dengan Kalender Lengkap
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
     * [FIXED] Memastikan tanggal Izin muncul di tabel meskipun absen kosong
     */
    private function getHistoryData($user, $selectedMonth, $selectedYear)
    {
        $branchTimezone = $user->branch->timezone ?? 'Asia/Jakarta';

        // 1. Tentukan Range Kalender
        $startDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        $today = Carbon::now()->timezone($branchTimezone)->startOfDay();
        $limitDate = ($endDate->gt($today)) ? $today : $endDate;

        // 2. Ambil Absensi (Termasuk H-1 untuk cek lembur)
        $attendances = Attendance::with(['verifier', 'scanner', 'user'])
            ->where('user_id', $user->id)
            ->whereBetween('check_in_time', [$startDate->copy()->subDay(), $endDate->copy()->addDay()])
            ->get();

        // 3. Ambil Izin
        $leaves = LeaveRequest::with('verifier')
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('is_active', true)
            ->get();

        $historyCollection = collect();
        $period = CarbonPeriod::create($startDate, $limitDate);

        // 4. Loop Kalender untuk menyisipkan Izin di tanggal yang absennya kosong
        foreach ($period as $date) {
            $currentDateStr = $date->format('Y-m-d');

            // Cari data absen di DB
            $att = $attendances->filter(function ($a) use ($currentDateStr, $branchTimezone) {
                return Carbon::parse($a->check_in_time)->timezone($branchTimezone)->format('Y-m-d') == $currentDateStr;
            })->first();

            if ($att) {
                $att->check_in_time = Carbon::parse($att->check_in_time)->timezone($branchTimezone);
                if ($att->check_out_time) { $att->check_out_time = Carbon::parse($att->check_out_time)->timezone($branchTimezone); }
                $historyCollection->push($att);
            } else {
                // JIKA ABSEN KOSONG, CEK APAKAH ADA IZIN (Contoh: Tanggal 28)
                $leave = $leaves->filter(function ($l) use ($date) {
                    return $date->between(Carbon::parse($l->start_date)->startOfDay(), Carbon::parse($l->end_date ?? $l->start_date)->endOfDay());
                })->first();

                $fakeAtt = new Attendance();
                $fakeAtt->user_id = $user->id;
                $fakeAtt->check_in_time = $date->copy()->setTime(0, 0, 0);
                
                if ($leave) {
                    $typeLabel = ucfirst($leave->type);
                    if ($leave->type == 'telat') $typeLabel = 'Izin Telat';
                    if ($leave->type == 'wfh') $typeLabel = 'WFH';

                    $fakeAtt->presence_status = $typeLabel;
                    $fakeAtt->status = 'verified';
                    $fakeAtt->attendance_type = 'leave';
                    $fakeAtt->is_late_checkin = ($leave->type == 'telat');
                    $fakeAtt->notes = "[Izin Approved: " . $leave->reason . "]";
                    $fakeAtt->setRelation('leaveRequest', $leave);
                    $fakeAtt->setRelation('verifier', $leave->verifier);
                } else {
                    $fakeAtt->presence_status = $date->isWeekend() ? 'Libur' : 'Alpha';
                    $fakeAtt->status = 'verified';
                    $fakeAtt->attendance_type = 'system';
                }
                $historyCollection->push($fakeAtt);
            }
        }

        $history = $historyCollection->sortByDesc('check_in_time');

        // 5. HITUNG SUMMARY
        $summary = [
            'total' => $history->count(),
            'hadir' => $history->filter(function ($item) {
                $s = strtolower($item->presence_status ?? '');
                return in_array($s, ['masuk', 'wfh', 'izin telat']) || str_contains($s, 'dinas') || 
                       (empty($s) && in_array($item->attendance_type, ['scan', 'self', 'manual']));
            })->count(),
            'sakit' => $history->filter(fn($i) => strtolower($i->presence_status ?? '') === 'sakit')->count(),
            'izin' => $history->filter(fn($i) => in_array(strtolower($i->presence_status ?? ''), ['izin', 'cuti']))->count(),
            'alpha' => $history->filter(fn($i) => strtolower($i->presence_status ?? '') === 'alpha')->count(),
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
        if (!in_array(Auth::user()->role, ['audit', 'admin'])) {
            abort(403, 'Akses Ditolak.');
        }

        $attendance = Attendance::find($id);
        
        if (!$attendance) {
            return back()->with('error', 'Data absensi asli tidak ditemukan.');
        }

        $request->validate([
            'presence_status' => 'required|string',
            'check_in_time'   => 'required',
            'check_out_time'  => 'nullable',
            'status'          => 'required|in:verified,pending_verification,rejected',
            'audit_note'      => 'nullable|string',
            'audit_photo'     => $attendance->audit_photo_path ? 'nullable|image|max:2048' : 'required|image|max:2048'
        ], [
            'audit_photo.required' => 'Bukti foto wajib di-upload untuk melakukan koreksi.',
        ]);

        $originalDate = $attendance->check_in_time->format('Y-m-d');
        $newCheckIn = Carbon::parse($originalDate . ' ' . $request->check_in_time);

        $newCheckOut = null;
        if ($request->check_out_time) {
            $newCheckOut = Carbon::parse($originalDate . ' ' . $request->check_out_time);
            if ($newCheckOut->lt($newCheckIn)) { $newCheckOut->addDay(); }
        }

        $workSchedule = WorkSchedule::getScheduleForUser($attendance->user_id);
        $isLate = $attendance->is_late_checkin;

        if ($newCheckIn->format('Y-m-d') >= '2025-12-01') {
            if ($workSchedule && $request->presence_status == 'Masuk') {
                $scheduleStart = Carbon::parse($originalDate . ' ' . $workSchedule->check_in_end);
                $isLate = $newCheckIn->gt($scheduleStart);
            }
        } else { $isLate = false; }

        $auditPhotoPath = $attendance->audit_photo_path;
        if ($request->hasFile('audit_photo')) { $auditPhotoPath = $request->file('audit_photo')->store('audit-proofs', 'public'); }

        $attendance->update([
            'presence_status'     => $request->presence_status,
            'check_in_time'       => $newCheckIn,
            'check_out_time'      => $newCheckOut,
            'status'              => $request->status,
            'is_late_checkin'     => $isLate,
            'audit_note'          => $request->audit_note,
            'audit_photo_path'    => $auditPhotoPath,
            'verified_by_user_id' => Auth::id(),
            'attendance_type'     => ($attendance->presence_status == 'Alpha' && $request->presence_status != 'Alpha') ? 'manual' : $attendance->attendance_type,
        ]);

        return back()->with('success', 'Data absensi berhasil diperbarui oleh Audit.');
    }
}
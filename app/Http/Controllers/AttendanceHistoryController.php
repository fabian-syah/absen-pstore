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

        // Izin akses untuk admin/audit/leader/admin_gaji melihat history user lain
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

        // Ambil Parameter Bulan & Tahun (Default hari ini)
        $selectedMonth = $request->get('month', date('m'));
        $selectedYear = $request->get('year', date('Y'));

        // Navigasi Bulan
        $currentDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1);
        $prevDate = $currentDate->copy()->subMonth();
        $nextDate = $currentDate->copy()->addMonth();

        $prevMonth = $prevDate->month;
        $prevYear  = $prevDate->year;
        $nextMonth = $nextDate->month;
        $nextYear  = $nextDate->year;

        // Ambil Data dengan Sinkronisasi Field Model
        $data = $this->getHistoryData($targetUser, $selectedMonth, $selectedYear);

        return view('attendance.history', array_merge($data, [
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'employee' => $employee,
            'prevMonth' => $prevMonth,
            'prevYear' => $prevYear,
            'nextMonth' => $nextMonth,
            'nextYear' => $nextYear,
        ]));
    }

    /**
     * Core Logic: Menggabungkan Data Absensi & Izin
     * [FIXED] Filter Izin sekarang mengikuti Range Bulan yang dipilih
     */
    private function getHistoryData($user, $selectedMonth, $selectedYear)
    {
        $branchTimezone = $user->branch->timezone ?? 'Asia/Jakarta';

        // 1. Tentukan Range Awal dan Akhir Bulan yang sedang dilihat
        $startDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        // Batasi penampilan sampai hari ini saja (jika melihat bulan berjalan)
        $today = Carbon::now()->timezone($branchTimezone)->startOfDay();
        $limitDate = ($endDate->gt($today)) ? $today : $endDate;

        // 2. Ambil Absensi Real (Include H-1 untuk lembur lintas hari)
        $attendances = Attendance::with(['verifier', 'scanner', 'scannerOut', 'user'])
            ->where('user_id', $user->id)
            ->whereBetween('check_in_time', [$startDate->copy()->subDay(), $endDate->copy()->addDay()])
            ->get();

        // 3. [FIXING DISINI] Ambil Izin yang HANYA bersinggungan dengan bulan terpilih
        $leaves = LeaveRequest::with('verifier')
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('is_active', true)
            ->where(function ($query) use ($startDate, $endDate) {
                // Izin yang mulai atau berakhir di bulan ini, atau izin panjang yang melewati bulan ini
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function ($q) use ($startDate, $endDate) {
                          $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                      });
            })
            ->get();

        $historyCollection = collect();
        $period = CarbonPeriod::create($startDate, $limitDate);

        // 4. Loop Kalender Harian
        foreach ($period as $date) {
            $currentDateStr = $date->format('Y-m-d');

            // Cek Absen Real
            $att = $attendances->filter(function ($a) use ($currentDateStr, $branchTimezone) {
                return Carbon::parse($a->check_in_time)->timezone($branchTimezone)->format('Y-m-d') == $currentDateStr;
            })->first();

            if ($att) {
                $att->check_in_time = Carbon::parse($att->check_in_time)->timezone($branchTimezone);
                if ($att->check_out_time) { 
                    $att->check_out_time = Carbon::parse($att->check_out_time)->timezone($branchTimezone); 
                }
                $historyCollection->push($att);
            } else {
                // JIKA ABSEN KOSONG, Cek Izin di tabel leaves yang sudah difilter per bulan
                $leave = $leaves->filter(function ($l) use ($date) {
                    $lStart = Carbon::parse($l->start_date)->startOfDay();
                    $lEnd = Carbon::parse($l->end_date ?? $l->start_date)->endOfDay();
                    return $date->between($lStart, $lEnd);
                })->first();

                $fakeAtt = new Attendance();
                $fakeAtt->user_id = $user->id;
                $fakeAtt->user = $user;
                $fakeAtt->check_in_time = $date->copy()->setTime(0, 0, 0);
                
                if ($leave) {
                    $typeLabel = ucfirst($leave->type);
                    if ($leave->type == 'telat') $typeLabel = 'Izin Telat';
                    if ($leave->type == 'wfh') $typeLabel = 'WFH';

                    $fakeAtt->presence_status = $typeLabel;
                    $fakeAtt->status = 'verified';
                    $fakeAtt->attendance_type = 'leave';
                    $fakeAtt->notes = "Izin: " . $leave->reason;
                    $fakeAtt->is_late_checkin = ($leave->type == 'telat');
                    
                    // Snapshot Jadwal
                    $fakeAtt->scheduled_check_in = $user->check_in_start;
                    $fakeAtt->scheduled_check_out = $user->check_out_start;

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

        // 5. HITUNG SUMMARY (Sinkron dengan tampilan tabel)
        $summary = [
            'total' => $history->count(),
            'present' => $history->filter(function ($item) {
                $s = strtolower($item->presence_status ?? '');
                return in_array($s, ['masuk', 'wfh', 'izin telat']) || str_contains($s, 'dinas') || 
                       (empty($s) && in_array($item->attendance_type, ['scan', 'self', 'manual']));
            })->count(),
            'sakit' => $history->filter(fn($i) => strtolower($i->presence_status ?? '') === 'sakit')->count(),
            'izin' => $history->filter(fn($i) => in_array(strtolower($i->presence_status ?? ''), ['izin', 'cuti', 'offday']))->count(),
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

        $attendance = Attendance::findOrFail($id);

        $request->validate([
            'presence_status' => 'required|string',
            'check_in_time'   => 'required',
            'check_out_time'  => 'nullable',
            'status'          => 'required|in:verified,pending_verification,rejected',
            'audit_note'      => 'nullable|string',
            'audit_photo'     => $attendance->audit_photo_path ? 'nullable|image|max:2048' : 'required|image|max:2048'
        ]);

        $originalDate = $attendance->check_in_time->format('Y-m-d');
        $newCheckIn = Carbon::parse($originalDate . ' ' . $request->check_in_time);

        $newCheckOut = null;
        if ($request->check_out_time) {
            $newCheckOut = Carbon::parse($originalDate . ' ' . $request->check_out_time);
            if ($newCheckOut->lt($newCheckIn)) { $newCheckOut->addDay(); }
        }

        $isLate = false;
        if ($request->presence_status == 'Masuk' && $attendance->scheduled_check_in) {
            $scheduleIn = Carbon::parse($originalDate . ' ' . $attendance->scheduled_check_in);
            $isLate = $newCheckIn->gt($scheduleIn);
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
            'verified_by_user_id' => Auth::id(),
            'attendance_type'     => 'manual',
        ]);

        return back()->with('success', 'Data absensi berhasil diperbarui.');
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        $targetUser = $request->has('employeeId') ? User::find($request->employeeId) : $user;
        $selectedMonth = $request->get('month', date('m'));
        $selectedYear = $request->get('year', date('Y'));
        $data = $this->getHistoryData($targetUser, $selectedMonth, $selectedYear);

        $pdf = Pdf::loadView('attendance.export_pdf', [
            'history' => $data['history'],
            'summary' => $data['summary'],
            'user' => $targetUser,
            'monthName' => Carbon::createFromDate($selectedYear, $selectedMonth, 1)->translatedFormat('F Y')
        ]);

        return $pdf->download('Laporan_Absensi_' . $targetUser->name . '.pdf');
    }
}
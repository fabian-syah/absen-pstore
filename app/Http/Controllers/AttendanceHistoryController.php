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

        if ($request->has('employeeId') && in_array($user->role, ['audit', 'admin', 'leader'])) {
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

        $prevMonth = $prevDate->month;
        $prevYear  = $prevDate->year;
        $nextMonth = $nextDate->month;
        $nextYear  = $nextDate->year;

        // Ambil Data dengan Logic Cross-Day Validation
        $data = $this->getHistoryData($targetUser, $selectedMonth, $selectedYear);

        $history = $data['history'];
        $summary = $data['summary'];

        return view('attendance.history', compact(
            'history', 
            'summary', 
            'selectedMonth', 
            'selectedYear',
            'employee',
            'prevMonth', 'prevYear',
            'nextMonth', 'nextYear'
        ));
    }

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

    private function getHistoryData($user, $selectedMonth, $selectedYear)
    {
        // [TIMEZONE LOGIC]
        // Ambil Timezone Cabang User
        $branchTz = $user->branch->timezone ?? 'Asia/Jakarta';

        // 1. AMBIL DATA ABSENSI BULAN INI + DATA AKHIR BULAN LALU (Untuk cek lembur tgl 1)
        // Kita ambil range lebih luas sedikit untuk validasi hari sebelumnya
        $startDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->subDay(); // H-1
        $endDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->endOfMonth();

        $attendances = Attendance::with(['verifier', 'scanner', 'user']) 
            ->where('user_id', $user->id)
            ->whereBetween('check_in_time', [$startDate, $endDate])
            ->orderBy('check_in_time', 'asc') // Sort ASC dulu untuk urutan validasi
            ->get();

        // 2. DATA IZIN
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

        // --- PROSES DATA & DETEKSI LEMBUR ---
        // Kita iterasi data Absensi asli
        foreach ($attendances as $index => $att) {
            // Hanya masukkan ke history jika check_in_time masuk bulan yang dipilih
            if ($att->check_in_time->month == $selectedMonth) {
                
                // === [LOGIC UPDATE] KONVERSI WAKTU KE LOKAL ===
                $att->check_in_local = Carbon::parse($att->check_in_time)->timezone($branchTz);
                $att->check_out_local = $att->check_out_time ? Carbon::parse($att->check_out_time)->timezone($branchTz) : null;

                // === LOGIKA HITUNG KETERLAMBATAN (REALTIME vs SNAPSHOT) ===
                $fixedScheduleIn  = $att->scheduled_check_in ?? ($user->check_in_start ?? ($user->workSchedule->start_time ?? null));
                $att->is_calculated_late = false;
                $att->late_duration_text = '';

                if ($fixedScheduleIn && $att->attendance_type != 'leave') {
                    $actualTimeStr = $att->check_in_local->format('H:i');
                    $scheduleTimeStr = Carbon::parse($fixedScheduleIn)->format('H:i');

                    if ($actualTimeStr > $scheduleTimeStr) {
                         $tActual = Carbon::parse($actualTimeStr);
                         $tSched = Carbon::parse($scheduleTimeStr);
                         $lateMinutes = $tSched->diffInMinutes($tActual);
                         
                         $hours = floor($lateMinutes / 60);
                         $mins = $lateMinutes % 60;
                         $att->late_duration_text = ($hours > 0) ? "{$hours}j {$mins}m" : "{$mins}m";
                         $att->is_calculated_late = true;
                    }
                }

                // === LOGIKA VALIDASI LEMBUR LINTAS HARI ===
                // Cek record SEBELUMNYA (H-1)
                $att->is_excused_late = false;
                
                // Cari absen kemarin
                $yesterday = $att->check_in_time->copy()->subDay()->format('Y-m-d');
                $prevAtt = $attendances->filter(function($a) use ($yesterday) {
                    return $a->check_in_time->format('Y-m-d') == $yesterday;
                })->first();

                if ($prevAtt && $prevAtt->check_out_time) {
                    // Cek apakah pulang kemarin LEWAT TENGAH MALAM (Misal jam 03:00 pagi hari ini)
                    $thresholdTime = $att->check_in_time->copy()->setTime(2, 0, 0); // Jam 2 Pagi Hari Ini
                    
                    if ($prevAtt->check_out_time->gt($thresholdTime)) {
                        // Jika pulang kemarin > Jam 2 pagi hari ini, maka telat hari ini DIMAKLUMI
                        $att->is_excused_late = true;
                        
                        // Tampilkan jam pulang lembur (Lokal)
                        $prevOutLocal = Carbon::parse($prevAtt->check_out_time)->timezone($branchTz);
                        $att->overtime_reason = "Pulang s/d " . $prevOutLocal->format('H:i');
                    }
                }

                $historyCollection->push($att);
            }
        }

        // --- PROSES DATA CUTI/IZIN (GABUNGKAN) ---
        foreach ($leaves as $leave) {
            $start = Carbon::parse($leave->start_date);
            $end = $leave->end_date ? Carbon::parse($leave->end_date) : $start;
            $period = CarbonPeriod::create($start, $end);

            foreach ($period as $date) {
                if ($date->month == $selectedMonth && $date->year == $selectedYear) {
                    // Cek Conflict
                    $exists = $historyCollection->filter(function ($a) use ($date) {
                        return $a->check_in_time->isSameDay($date);
                    })->isNotEmpty();

                    if (!$exists) {
                        $fakeAtt = new Attendance();
                        $fakeAtt->id = 'leave_' . $leave->id . '_' . $date->timestamp; 
                        $fakeAtt->user_id = $user->id;
                        $fakeAtt->check_in_time = $date->copy()->setTime(8, 0, 0); 
                        $fakeAtt->check_out_time = null;
                        
                        // Fake local time agar tidak error di view
                        $fakeAtt->check_in_local = $fakeAtt->check_in_time;
                        $fakeAtt->check_out_local = null;
                        
                        $typeLabel = ucfirst($leave->type); 
                        if ($leave->type == 'telat') $typeLabel = 'Izin Telat';
                        if ($leave->type == 'wfh') $typeLabel = 'WFH';

                        $fakeAtt->presence_status = $typeLabel;
                        $fakeAtt->status = 'verified';
                        $fakeAtt->attendance_type = 'leave'; 
                        $fakeAtt->is_late_checkin = false;
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

        $summary = [
            'total' => $history->count(),
            'hadir' => $history->filter(function($item) {
                $s = strtolower($item->presence_status ?? '');
                $isExplicitPresent = in_array($s, ['masuk', 'wfh', 'izin telat']) || str_contains($s, 'dinas');
                $isImplicitPresent = empty($s) && in_array($item->attendance_type, ['scan', 'self', 'manual']);
                return $isExplicitPresent || $isImplicitPresent;
            })->count(),
            'sakit' => $history->filter(function($i) { return strtolower($i->presence_status ?? '') === 'sakit'; })->count(),
            'izin' => $history->filter(function($i) { return in_array(strtolower($i->presence_status ?? ''), ['izin', 'cuti']); })->count(),
            'alpha' => $history->filter(function($i) { return strtolower($i->presence_status ?? '') === 'alpha'; })->count(),
            'telat' => $history->where('is_calculated_late', true)->count(), // Pakai flag hitungan baru
            'pulang_cepat' => $history->where('is_early_checkout', true)->count(),
            'pending' => $history->where('status', 'pending_verification')->count(),
        ];

        return ['history' => $history, 'summary' => $summary];
    }

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
        
        // Handle Cross-Day Checkout manually in Audit Edit
        $checkOutTimeStr = $request->check_out_time;
        $newCheckOut = null;

        if ($checkOutTimeStr) {
            $newCheckOut = Carbon::parse($originalDate . ' ' . $checkOutTimeStr);
            // Jika jam pulang < jam masuk, asumsikan besoknya (lintas hari)
            if ($newCheckOut->lt($newCheckIn)) {
                $newCheckOut->addDay();
            }
        }

        $workSchedule = WorkSchedule::getScheduleForUser($attendance->user_id);
        $isLate = $attendance->is_late_checkin;
        
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
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

        if ($request->has('employeeId') && (Auth::user()->role == 'audit' || Auth::user()->role == 'admin')) {
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

        if ($request->has('employeeId') && (Auth::user()->role == 'audit' || Auth::user()->role == 'admin')) {
            $targetUser = User::find($request->employeeId);
        } else {
            $targetUser = $user;
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
        // 1. AMBIL DATA ABSENSI ASLI
        $attendances = Attendance::with(['verifier', 'scanner']) 
            ->where('user_id', $user->id)
            ->whereYear('check_in_time', $selectedYear)
            ->whereMonth('check_in_time', $selectedMonth)
            ->orderBy('check_in_time', 'desc')
            ->get();

        // 2. DATA IZIN (Menggunakan relasi 'verifier' yang baru dibuat di Model)
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

        $historyCollection = $attendances;

        foreach ($leaves as $leave) {
            $startDate = Carbon::parse($leave->start_date);
            $endDate = $leave->end_date ? Carbon::parse($leave->end_date) : $startDate;
            $period = CarbonPeriod::create($startDate, $endDate);

            foreach ($period as $date) {
                if ($date->month == $selectedMonth && $date->year == $selectedYear) {
                    
                    // Cek Conflict tanggal
                    $alreadyAttendance = $attendances->filter(function ($att) use ($date) {
                        return $att->check_in_time->isSameDay($date);
                    })->isNotEmpty();

                    if (!$alreadyAttendance) {
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
                        $fakeAtt->is_late_checkin = false;
                        $fakeAtt->is_early_checkout = false;
                        $fakeAtt->photo_path = null;
                        $fakeAtt->photo_out_path = null;
                        $fakeAtt->audit_photo_path = null;
                        $fakeAtt->audit_note = "Pengajuan: " . $leave->reason;
                        
                        // Set Relasi agar bisa dipanggil di View
                        $fakeAtt->setRelation('leaveRequest', $leave);
                        
                        // Mapping Verifier Izin ke Verifier Attendance
                        $fakeAtt->setRelation('verifier', $leave->verifier); 
                        
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
            'sakit' => $history->filter(function($i) {
                return strtolower($i->presence_status ?? '') === 'sakit';
            })->count(),
            'izin' => $history->filter(function($i) {
                return in_array(strtolower($i->presence_status ?? ''), ['izin', 'cuti']);
            })->count(),
            'alpha' => $history->filter(function($i) {
                return strtolower($i->presence_status ?? '') === 'alpha';
            })->count(),
            'telat' => $history->where('is_late_checkin', true)->count(),
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
        $newCheckOut = $request->check_out_time ? Carbon::parse($originalDate . ' ' . $request->check_out_time) : null;

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
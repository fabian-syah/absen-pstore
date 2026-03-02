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
        $branchTimezone = $user->branch->timezone ?? 'Asia/Jakarta';

        // 2. Tentukan range awal bulan (Calendar Month untuk tampilan riwayat)
        $startDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1, $branchTimezone)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // 3. Ambil "Hari Ini" di cabang tersebut
        $todayInBranch = Carbon::now($branchTimezone)->startOfDay();

        // 4. Logika Penentuan Limit
        $isCurrentMonth = ($selectedMonth == Carbon::now($branchTimezone)->month && $selectedYear == Carbon::now($branchTimezone)->year);
        if ($isCurrentMonth) {
            $limitDate = $todayInBranch;
        } else {
            $limitDate = ($endDate->gt(Carbon::now($branchTimezone))) ? $todayInBranch : $endDate;
        }

        // 5. Query Database dengan buffer satu hari
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
                $query->whereBetween('start_date', [$s, $e])
                    ->orWhereBetween('end_date', [$s, $e])
                    ->orWhere(function ($q) use ($s, $e) {
                        $q->where('start_date', '<=', $s)
                            ->where('end_date', '>=', $e);
                    });
            })->get();

        $historyCollection = collect();

        // 6. Buat period (PENTING: Gunakan startOfDay agar tidak terpotong jam)
        $period = CarbonPeriod::create($startDate->copy()->startOfDay(), $limitDate->copy()->startOfDay());

        foreach ($period as $date) {
            $currentDateStr = $date->format('Y-m-d');

            // Cari attendance (Prioritaskan yang punya jam masuk asli / bukan 00:00 jika ada lebih dari satu)
            $att = $attendances->filter(function ($a) use ($currentDateStr, $branchTimezone) {
                return Carbon::parse($a->check_in_time)->timezone($branchTimezone)->format('Y-m-d') == $currentDateStr;
            })->sortBy(fn($a) => $a->attendance_type == 'system' ? 1 : 0)->first();

            // Cari leave
            $leave = $leaves->filter(function ($l) use ($date) {
                return $date->between(
                    Carbon::parse($l->start_date)->startOfDay(),
                    Carbon::parse($l->end_date ?? $l->start_date)->endOfDay()
                );
            })->first();

            if ($att) {
                $att->check_in_time = Carbon::parse($att->check_in_time)->timezone($branchTimezone);
                if ($att->check_out_time) {
                    $att->check_out_time = Carbon::parse($att->check_out_time)->timezone($branchTimezone);
                }

                // FIX: Attach relation leaveRequest manual agar foto bukti muncul di view
                if ($leave) {
                    $att->setRelation('leaveRequest', $leave);
                }

                $historyCollection->push($att);
            } else {
                // Jika tidak ada attendance (Alpha / Leave)
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
                } else {
                    $fakeAtt->presence_status = 'Alpha';
                    $fakeAtt->attendance_type = 'system';
                }
                $fakeAtt->status = 'verified';
                $historyCollection->push($fakeAtt);
            }
        }

        $history = $historyCollection->sortByDesc(function ($item) {
            return $item->check_in_time->timestamp;
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
            'audit_photo' => $attendance->audit_photo_path ? 'nullable|image|max:2048' : 'required|image|max:2048'
        ]);

        $originalDate = $attendance->check_in_time->format('Y-m-d');
        $newCheckIn = Carbon::parse($originalDate . ' ' . $request->check_in_time);
        $newCheckOut = $request->check_out_time ? Carbon::parse($originalDate . ' ' . $request->check_out_time) : null;
        if ($newCheckOut && $newCheckOut->lt($newCheckIn)) {
            $newCheckOut->addDay();
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

        return back()->with('success', 'Data absensi berhasil diperbarui.');
    }
}
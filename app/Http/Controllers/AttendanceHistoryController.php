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
        $branchTimezone = $user->branch->timezone ?? 'Asia/Jakarta';
        $startDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        $today = Carbon::now()->timezone($branchTimezone)->startOfDay();
        $limitDate = ($endDate->gt($today)) ? $today : $endDate;

        $attendances = Attendance::with(['verifier', 'scanner', 'scannerOut', 'user'])
            ->where('user_id', $user->id)
            ->whereBetween('check_in_time', [$startDate->copy()->subDay(), $endDate->copy()->addDay()])
            ->get();

        $leaves = LeaveRequest::with('verifier')
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('is_active', true)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function ($q) use ($startDate, $endDate) {
                          $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                      });
            })->get();

        $historyCollection = collect();
        $period = CarbonPeriod::create($startDate, $limitDate);

        foreach ($period as $date) {
            $currentDateStr = $date->format('Y-m-d');
            $att = $attendances->filter(function ($a) use ($currentDateStr, $branchTimezone) {
                return Carbon::parse($a->check_in_time)->timezone($branchTimezone)->format('Y-m-d') == $currentDateStr;
            })->first();

            if ($att) {
                $att->check_in_time = Carbon::parse($att->check_in_time)->timezone($branchTimezone);
                if ($att->check_out_time) { $att->check_out_time = Carbon::parse($att->check_out_time)->timezone($branchTimezone); }
                $historyCollection->push($att);
            } else {
                $leave = $leaves->filter(function ($l) use ($date) {
                    return $date->between(Carbon::parse($l->start_date)->startOfDay(), Carbon::parse($l->end_date ?? $l->start_date)->endOfDay());
                })->first();

                $fakeAtt = new Attendance();
                $fakeAtt->user_id = $user->id;
                $fakeAtt->user = $user;
                $fakeAtt->check_in_time = $date->copy()->setTime(0, 0, 0);
                
                if ($leave) {
                    $fakeAtt->presence_status = ucfirst($leave->type);
                    $fakeAtt->status = 'verified';
                    $fakeAtt->attendance_type = 'leave';
                    $fakeAtt->notes = "Izin: " . $leave->reason;
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

    public function exportPdf(Request $request) {
        $user = Auth::user();
        $targetUser = $request->has('employeeId') ? User::find($request->employeeId) : $user;
        $data = $this->getHistoryData($targetUser, $request->month, $request->year);
        $pdf = Pdf::loadView('attendance.export_pdf', array_merge($data, ['user' => $targetUser]));
        return $pdf->download('Laporan.pdf');
    }

    public function updateByAudit(Request $request, $id) {
        $attendance = Attendance::findOrFail($id);
        $attendance->update($request->all());
        return back();
    }
}
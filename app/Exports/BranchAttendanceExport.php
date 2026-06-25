<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class BranchAttendanceExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $branchId;
    protected $month;
    protected $year;

    public function __construct($branchId, $month, $year)
    {
        $this->branchId = $branchId;
        $this->month = $month;
        $this->year = $year;
    }

    public function view(): View
    {
        $dateObj = Carbon::createFromDate($this->year, $this->month, 1);
        $startDate = $dateObj->copy()->subMonth()->day(26)->startOfDay();
        $endDate = $dateObj->copy()->day(25)->endOfDay();
        $periodName = $startDate->translatedFormat('d F Y') . ' - ' . $endDate->translatedFormat('d F Y');

        // Get Employees in this branch
        $employees = User::where('branch_id', $this->branchId)
            ->where('role', '!=', 'admin') // Exclude generic admin
            ->orderBy('name', 'asc')
            ->get();

        $branch = \App\Models\Branch::find($this->branchId);
        $branchTimezone = $branch->timezone ?? 'Asia/Jakarta';
        $todayInBranch = Carbon::now($branchTimezone)->startOfDay();
        $limitDate = ($endDate->gt($todayInBranch)) ? $todayInBranch : $endDate;
        $period = \Carbon\CarbonPeriod::create($startDate->copy()->startOfDay(), $limitDate->copy()->startOfDay());

        $data = [];

        foreach ($employees as $employee) {
            $attendances = Attendance::where('user_id', $employee->id)
                ->whereBetween('check_in_time', [$startDate->copy()->subDay(), $endDate->copy()->addDay()])
                ->get();
                
            $leaves = \App\Models\LeaveRequest::where('user_id', $employee->id)
                ->where('status', 'approved')
                ->where('is_active', true)
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->where('start_date', '<=', $endDate->format('Y-m-d'))
                        ->where(function ($q) use ($startDate) {
                            $q->whereNull('end_date')
                                ->orWhere('end_date', '>=', $startDate->format('Y-m-d'));
                        });
                })->get();

            $summary = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alfa' => 0, 'libur' => 0, 'telat' => 0, 'total_jam' => 0];

            foreach ($period as $date) {
                $currentDateStr = $date->format('Y-m-d');
                $att = $attendances->filter(function ($a) use ($currentDateStr, $branchTimezone) {
                    if ($a->attendance_type === 'system' && strtolower($a->presence_status) === 'alpha') return false;
                    if ($a->status === 'rejected') return false;
                    return Carbon::parse($a->check_in_time)->timezone($branchTimezone)->format('Y-m-d') === $currentDateStr;
                })->sortBy(function($a) {
                    return strtolower($a->presence_status) === 'masuk' ? 0 : 1;
                })->first();

                $leave = $leaves->filter(function ($l) use ($date, $branchTimezone, $att) {
                    if ($l->type === 'telat' && !$att) return false;
                    $lStart = Carbon::parse($l->start_date, $branchTimezone)->startOfDay();
                    $lEnd = Carbon::parse($l->end_date ?? $l->start_date, $branchTimezone)->endOfDay();
                    return $date->between($lStart, $lEnd);
                })->first();

                if ($att) {
                    $status = strtolower(trim($att->presence_status));
                    $isHadir = in_array($status, ['hadir', 'tepat waktu', 'masuk', 'wfh', 'work from home', 'dinas luar', 'kunjungan rutin', 'lembur']);
                    if ($isHadir || empty($status)) $summary['hadir']++;
                    elseif ($status == 'sakit') $summary['sakit']++;
                    elseif (in_array($status, ['izin', 'cuti'])) $summary['izin']++;
                    elseif ($status == 'libur') $summary['libur']++;

                    if ($att->is_late_checkin) $summary['telat']++;
                    if ($att->check_in_time && $att->check_out_time) {
                        $summary['total_jam'] += Carbon::parse($att->check_in_time)->diffInHours(Carbon::parse($att->check_out_time));
                    }
                } elseif ($leave) {
                    if ($leave->type == 'sakit') $summary['sakit']++;
                    elseif (in_array($leave->type, ['izin', 'cuti'])) $summary['izin']++;
                    elseif ($leave->type == 'libur') $summary['libur']++;
                    elseif (in_array($leave->type, ['wfh', 'dinas', 'telat'])) $summary['hadir']++;
                } else {
                    $summary['alfa']++;
                }
            }

            $data[] = ['user' => $employee, 'summary' => $summary];
        }

        return view('branch.export_excel', [
            'data' => $data,
            'month' => $periodName,
            'branch' => \App\Models\Branch::find($this->branchId)
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true, 'size' => 14]],
            3 => ['font' => ['bold' => true]], // Table Header
        ];
    }
}

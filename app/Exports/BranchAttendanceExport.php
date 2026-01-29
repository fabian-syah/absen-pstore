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
        $startDate = Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Get Employees in this branch
        $employees = User::where('branch_id', $this->branchId)
            ->where('role', '!=', 'admin') // Exclude generic admin
            ->orderBy('name', 'asc')
            ->get();

        $data = [];

        foreach ($employees as $employee) {
            // Count statuses
            $attendances = Attendance::where('user_id', $employee->id)
                ->whereBetween('check_in_time', [$startDate, $endDate])
                ->get();

            $summary = [
                'hadir' => 0,
                'sakit' => 0,
                'izin' => 0,
                'alfa' => 0,
                'libur' => 0,
                'telat' => 0,
                'total_jam' => 0
            ];

            foreach ($attendances as $atten) {
                $status = strtolower(trim($atten->presence_status));

                // Logic MATCHING UI & Controller
                $isHadir = in_array($status, [
                    'hadir',
                    'tepat waktu',
                    'masuk',
                    'wfh',
                    'work from home',
                    'dinas luar',
                    'kunjungan rutin',
                    'lembur'
                ]);

                if ($isHadir) {
                    $summary['hadir']++;
                } elseif ($status == 'sakit') {
                    $summary['sakit']++;
                } elseif (in_array($status, ['izin', 'cuti'])) {
                    $summary['izin']++;
                } elseif ($status == 'alpha') {
                    $summary['alfa']++;
                } elseif ($status == 'libur') {
                    $summary['libur']++;
                }

                if ($atten->is_late_checkin) {
                    $summary['telat']++;
                }

                // Calculate Work Duration if checkout exists working
                if ($atten->check_in_time && $atten->check_out_time) {
                    $duration = $atten->check_in_time->diffInHours($atten->check_out_time);
                    $summary['total_jam'] += $duration;
                }
            }

            $data[] = [
                'user' => $employee,
                'summary' => $summary
            ];
        }

        return view('branch.export_excel', [
            'data' => $data,
            'month' => $startDate->translatedFormat('F Y'),
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

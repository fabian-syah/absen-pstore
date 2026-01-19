<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EmployeeSalaryExport implements WithMultipleSheets
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        $sheets = [];

        // 1. Sheet Semua Data (Gabungan)
        $sheets[] = new EmployeeSalarySheetExport('all', $this->filters, 'Semua Data');

        // 2. Sheet Karyawan Tetap
        $sheets[] = new EmployeeSalarySheetExport('employee', $this->filters, 'Karyawan Tetap');

        // 3. Sheet Promotor
        $sheets[] = new EmployeeSalarySheetExport('promotor', $this->filters, 'Promotor');

        // 4. Sheet Freelance
        $sheets[] = new EmployeeSalarySheetExport('freelance', $this->filters, 'Freelance');

        // 5. Sheet Belum Diatur
        $sheets[] = new EmployeeSalarySheetExport('unset', $this->filters, 'Belum Diatur');

        return $sheets;
    }
}
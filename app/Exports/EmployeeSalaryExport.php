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
        $categoryFilter = $this->filters['category'] ?? null;

        // Jika user memfilter di Web (Misal pilih 'Freelance'), 
        // Excel cuma akan menampilkan 1 sheet Freelance.
        if ($categoryFilter && $categoryFilter !== '') {
            if ($categoryFilter == 'employee') {
                $sheets[] = new EmployeeSalarySheetExport('employee', $this->filters, 'Karyawan Tetap');
            } elseif ($categoryFilter == 'promotor') {
                $sheets[] = new EmployeeSalarySheetExport('promotor', $this->filters, 'Promotor');
            } elseif ($categoryFilter == 'freelance') {
                $sheets[] = new EmployeeSalarySheetExport('freelance', $this->filters, 'Freelance');
            } elseif ($categoryFilter == 'unset') {
                $sheets[] = new EmployeeSalarySheetExport('unset', $this->filters, 'Belum Diatur');
            } else {
                $sheets[] = new EmployeeSalarySheetExport($categoryFilter, $this->filters, 'Data Terfilter');
            }
            return $sheets;
        }

        // DEFAULT: Tampilkan 3 Sheet "Semua Data", "Pusat", dan "Cabang"
        $sheets[] = new EmployeeSalarySheetExport('all', $this->filters, 'Semua Data', 'all');
        $sheets[] = new EmployeeSalarySheetExport('all', $this->filters, 'Pusat', 'pusat');
        $sheets[] = new EmployeeSalarySheetExport('all', $this->filters, 'Cabang', 'cabang');

        return $sheets;
    }
}
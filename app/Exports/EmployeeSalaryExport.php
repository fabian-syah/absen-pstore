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

        // LOGIKA BARU: 
        // Jika User memilih kategori spesifik di Filter, Export HANYA sheet itu saja.
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
                // Fallback jika value tidak dikenal, tampilkan sebagai "Data Filtered"
                $sheets[] = new EmployeeSalarySheetExport($categoryFilter, $this->filters, 'Data Terfilter');
            }

            return $sheets;
        }

        // JIKA TIDAK ADA FILTER (Default): Tampilkan Semua Sheet seperti sebelumnya
        
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
<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeSalaryExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = User::with(['branch', 'division', 'employeeSalary'])
            ->where('is_active', true)
            ->whereNotIn('role', ['admin', 'admin_gaji']); 

        // Filter: Search
        if (isset($this->request['search']) && $this->request['search'] != null) {
            $search = $this->request['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('login_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter: Branch
        if (isset($this->request['branch_id']) && $this->request['branch_id'] != null) {
            $query->where('branch_id', $this->request['branch_id']);
        }

        // Filter: Division
        if (isset($this->request['division_id']) && $this->request['division_id'] != null) {
            $query->where('division_id', $this->request['division_id']);
        }

        // Filter: Category
        if (isset($this->request['category']) && $this->request['category'] != null) {
            if ($this->request['category'] == 'unset') {
                $query->doesntHave('employeeSalary');
            } else {
                $query->whereHas('employeeSalary', function($q) {
                    $q->where('category', $this->request['category']);
                });
            }
        }

        return $query->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'Nama Karyawan',
            'Login ID',
            'Email',
            'Divisi',
            'Cabang',
            'Kategori Gaji',
            'Gaji Pokok (Bulanan)',
            'Tunjangan Jabatan',
            'Privilege Owner',
            'Gaji Harian (Freelance)',
            'Insentif (Promotor)',
            'Total Master Gaji (Estimasi)',
            'Nama Bank',
            'No. Rekening',
            'Catatan',
        ];
    }

    public function map($user): array
    {
        $salary = $user->employeeSalary;
        
        $categoryLabel = 'Belum Diatur';
        $basicSalary = 0;
        $positionAllowance = 0;
        $ownerPrivilege = 0;
        $dailySalary = 0;
        $promotorBonus = 0;
        $totalMaster = 0;
        $bankName = '-';
        $accountNumber = '-';
        $notes = '-';

        if ($salary) {
            $bankName = $salary->bank_name;
            $accountNumber = $salary->bank_account_number;
            $notes = $salary->notes;

            if ($salary->category == 'employee') {
                $categoryLabel = 'Karyawan Tetap';
                $basicSalary = $salary->basic_salary;
                $positionAllowance = $salary->position_allowance;
                $ownerPrivilege = $salary->owner_privilege;
                $totalMaster = $basicSalary + $positionAllowance + $ownerPrivilege;
            } elseif ($salary->category == 'freelance') {
                $categoryLabel = 'Freelance';
                $dailySalary = $salary->daily_salary;
                $totalMaster = $dailySalary; // Per hari
            } elseif ($salary->category == 'promotor') {
                $categoryLabel = 'Promotor';
                $promotorBonus = $salary->promotor_bonus;
                $totalMaster = $promotorBonus;
            }
        }

        return [
            $user->name,
            $user->login_id ?? '-',
            $user->email,
            $user->division->name ?? '-',
            $user->branch->name ?? '-',
            $categoryLabel,
            $basicSalary,
            $positionAllowance,
            $ownerPrivilege,
            $dailySalary,
            $promotorBonus,
            $totalMaster,
            $bankName,
            $accountNumber . ' ', // Trick to force string in Excel
            $notes,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4B49AC']]],
        ];
    }
}
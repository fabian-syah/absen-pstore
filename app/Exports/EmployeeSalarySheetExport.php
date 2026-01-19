<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents; 
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Table; // Import untuk Tabel Excel
use PhpOffice\PhpSpreadsheet\Worksheet\Table\TableStyle; // Import Style

class EmployeeSalarySheetExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle, WithEvents
{
    protected $category;
    protected $filters;
    protected $sheetTitle;

    public function __construct($category, $filters, $sheetTitle)
    {
        $this->category = $category;
        $this->filters = $filters;
        $this->sheetTitle = $sheetTitle;
    }

    public function query()
    {
        $query = User::with(['branch', 'division', 'employeeSalary'])
            ->where('is_active', true)
            ->whereNotIn('role', ['admin', 'admin_gaji']);

        // 1. Filter Pencarian Global (Search)
        if (isset($this->filters['search']) && $this->filters['search'] != null) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('login_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // 2. Filter Cabang
        if (isset($this->filters['branch_id']) && $this->filters['branch_id'] != null) {
            $query->where('branch_id', $this->filters['branch_id']);
        }

        // 3. Filter Divisi
        if (isset($this->filters['division_id']) && $this->filters['division_id'] != null) {
            $query->where('division_id', $this->filters['division_id']);
        }

        // 4. Logika Kategori
        if ($this->category === 'all') {
             if (isset($this->filters['category']) && $this->filters['category'] != null) {
                if ($this->filters['category'] == 'unset') {
                    $query->doesntHave('employeeSalary');
                } else {
                    $query->whereHas('employeeSalary', function($q) {
                        $q->where('category', $this->filters['category']);
                    });
                }
            }
        } elseif ($this->category === 'unset') {
            $query->doesntHave('employeeSalary');
        } else {
            $query->whereHas('employeeSalary', function($q) {
                $q->where('category', $this->category);
            });
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
            $accountNumber . ' ', 
            $notes,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Kita kosongkan bagian ini karena styling akan dihandle oleh TableStyle di registerEvents
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                
                // 1. Ambil Dimensi Data (Misal A1:O100)
                $dimension = $event->sheet->getDelegate()->calculateWorksheetDimension();
                
                // 2. Buat Nama Tabel yang Unik (Menghindari error jika nama tabel duplikat)
                // Hapus spasi dari judul sheet dan tambahkan angka random
                $tableName = str_replace(' ', '', $this->sheetTitle) . '_' . uniqid(); 

                // 3. Buat Object Tabel Excel
                $table = new Table();
                $table->setName($tableName);
                $table->setRange($dimension);
                $table->setShowTotalsRow(false); // Matikan baris Total di bawah
                
                // 4. Pilih Gaya Tabel (Biru Medium adalah standar profesional)
                $tableStyle = new TableStyle();
                $tableStyle->setTheme(TableStyle::TABLE_STYLE_MEDIUM2); // Style Biru
                $table->setStyle($tableStyle);

                // 5. Masukkan Tabel ke Sheet
                $event->sheet->getDelegate()->addTable($table);

                // 6. Freeze Pane (Kunci Baris Header agar tetap terlihat saat scroll)
                // 'A2' berarti baris 1 dikunci, scroll mulai dari baris 2
                $event->sheet->getDelegate()->freezePane('A2');
                
                // 7. Auto Size Kolom (Agar tulisan tidak terpotong)
                foreach(range('A', 'O') as $columnID) {
                    $event->sheet->getDelegate()->getColumnDimension($columnID)->setAutoSize(true);
                }
            },
        ];
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }
}
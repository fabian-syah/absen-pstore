<?php

namespace App\Exports;

use App\Models\Salary;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Table;
use PhpOffice\PhpSpreadsheet\Worksheet\Table\TableStyle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SalaryExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents, WithColumnFormatting
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Salary::with(['user.branch', 'user.division'])
            ->where('status', '!=', 'draft');

        // Filter berdasarkan Month
        if (!empty($this->filters['month'])) {
            $query->where('month', $this->filters['month']);
        }

        // Filter berdasarkan Year
        if (!empty($this->filters['year'])) {
            $query->where('year', $this->filters['year']);
        }

        // Filter Cabang (Hanya jika Admin/Admin Gaji yang minta filter ini)
        if (!empty($this->filters['branch_id'])) {
            $query->whereHas('user', function ($q) {
                $q->where('branch_id', $this->filters['branch_id']);
            });
        }

        // Filter Search (Nama Karyawan)
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Jika BUKAN admin/admin_gaji, paksa filter user_id sendiri (security layer di export)
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'admin_gaji'])) {
            $query->where('user_id', $user->id);
        }

        return $query->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->orderBy('id', 'desc');
    }

    public function headings(): array
    {
        return [
            'Periode',
            'Selesai Periode',
            'Nama Karyawan',
            'Cabang',
            'Divisi',
            'Status',
            'Tanggal Terbit',
            'Total Diterima (THP)',
            'Gaji Pokok',
            'Tunjangan Jabatan',
            'Lembur',
            'Bonus',
            'Potongan',
            'Kasbon',
        ];
    }

    public function map($salary): array
    {
        $user = $salary->user;
        $periode = Carbon::createFromDate($salary->year, $salary->month, 1)->isoFormat('MMMM Y');

        return [
            $periode,
            Carbon::parse($salary->period_end)->format('d M Y'),
            $user->name ?? '-',
            $user->branch->name ?? '-',
            $user->division->name ?? '-',
            ucfirst($salary->status),
            $salary->published_at ? Carbon::parse($salary->published_at)->format('d M Y H:i') : '-',
            $salary->total_amount,
            $salary->basic_salary,
            $salary->position_allowance,
            $salary->overtime_amount,
            $salary->bonus_amount,
            $salary->deduction_amount,
            $salary->kasbon_amount,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'H' => '"Rp " #,##0', // THP
            'I' => '"Rp " #,##0',
            'J' => '"Rp " #,##0',
            'K' => '"Rp " #,##0',
            'L' => '"Rp " #,##0',
            'M' => '"Rp " #,##0',
            'N' => '"Rp " #,##0',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $dimension = $event->sheet->getDelegate()->calculateWorksheetDimension();

                // Jika data kosong, calculateWorksheetDimension bisa return simple range
                // Kita cek dulu row count
                $rowCount = $event->sheet->getDelegate()->getHighestRow();
                if ($rowCount < 2)
                    return; // Hanya header
    
                $tableName = 'Salaries_' . uniqid();
                $table = new Table();
                $table->setName($tableName);
                $table->setRange($dimension);
                $table->setShowTotalsRow(false);

                $tableStyle = new TableStyle();
                $tableStyle->setTheme(TableStyle::TABLE_STYLE_MEDIUM2);
                $table->setStyle($tableStyle);

                $event->sheet->getDelegate()->addTable($table);
                $event->sheet->getDelegate()->freezePane('A2');
            },
        ];
    }
}

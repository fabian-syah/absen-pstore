<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ResignNonKaryawanExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithColumnFormatting, WithCustomValueBinder
{
    protected $startDate;
    protected $endDate;
    protected $category;
    protected $data;

    public function __construct($startDate = null, $endDate = null, $category = 'all', $data = null)
    {
        $this->startDate = $startDate ?: '2026-09-01';
        $this->endDate = $endDate ?: '2026-10-31';
        $this->category = $category ?: 'all';
        $this->data = $data;
    }

    public function collection()
    {
        if ($this->data !== null) {
            return collect($this->data);
        }

        return \App\Http\Controllers\ResignReportController::fetchReportData(
            $this->startDate,
            $this->endDate,
            $this->category
        );
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Lengkap',
            'Login ID / NIK',
            'Kategori Status',
            'Cabang Asal / Lokasi',
            'Divisi / Jabatan',
            'Role Akses',
            'Tanggal (Resign / Dibuat)',
            'Keterangan'
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        $row = (array) $row;

        return [
            $no,
            $row['name'] ?? '-',
            $row['login_id'] ?? '-',
            $row['category'] ?? '-',
            $row['branch'] ?? '-',
            $row['division'] ?? '-',
            $row['role'] ?? '-',
            $row['date'] ?? '-',
            $row['notes'] ?? '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // Style Header baris 1
        $sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E293B'], // Dark Navy / Slate 800
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(28);

        // Styling data cells
        if ($highestRow > 1) {
            $sheet->getStyle('A2:' . $highestColumn . $highestRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CBD5E1'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Alignment spesifik
            $sheet->getStyle('A2:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C2:D' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G2:H' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        return [];
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT,
            'H' => NumberFormat::FORMAT_DATE_YYYYMMDD,
        ];
    }

    public function bindValue(Cell $cell, $value)
    {
        if ($cell->getColumn() === 'C') {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }
}

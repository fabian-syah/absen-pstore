<?php

namespace App\Exports;

use App\Models\Inventory;
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

class InventoryExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithColumnFormatting, WithCustomValueBinder
{
    private $inventoryData;

    public function __construct($group = 'all')
    {
        $query = Inventory::with(['user', 'user.division', 'user.branch'])
            ->whereNotNull('user_id');

        $pusatList = [
            'AppleLux',
            'Arcis & Debs',
            'Cleaning service',
            'Dokter Pstore',
            'Driver pstore',
            'Finance',
            'Inventory',
            'keluarga Pstore',
            'Managament',
            'Marketing Creative',
            'Masjid abdurrohman bin auf',
            'Mega pstore',
            'Ps arwana',
            'PS bakery',
            'PS big jakarta',
            'PS catering',
            'PS new jakarta',
            'Pskontraktor',
            'Pstore Lenteng Agung',
            'Pstore Peduli',
            'Pstore Qcell jakarta',
            'Shopee',
            'Security Jakarta',
            'Team Audit',
            'Team Creative',
            'Tim Elite',
            'Tiktok',
            'Operator',
        ];

        if ($group === 'pusat') {
            $query->whereHas('user.branch', function ($q) use ($pusatList) {
                $q->whereIn('name', $pusatList);
            });
        } elseif ($group === 'cabang') {
            $query->whereHas('user.branch', function ($q) use ($pusatList) {
                $q->whereNotIn('name', $pusatList);
            });
        }

        $this->inventoryData = $query->latest()->get();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->inventoryData;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Barang',
            'Serial Number',
            'Kategori',
            'Kondisi',
            'Pemegang (User)',
            'Divisi',
            'Cabang',
            'Tanggal Terima',
            'Status'
        ];
    }

    public function map($inventory): array
    {
        static $no = 0;
        $no++;

        // Helper untuk mapping timezone ke singkatan (WIB/WITA/WIT)
        $timezoneMap = [
            'Asia/Jakarta' => 'WIB',
            'Asia/Makassar' => 'WITA',
            'Asia/Jayapura' => 'WIT',
        ];

        // Ambil timezone user (jika ada)
        $userTimezone = $inventory->user->branch?->timezone;
        $tzSuffix = $timezoneMap[$userTimezone] ?? '';

        // Format Tanggal: d-m-Y (Suffix)
        $receivedDate = $inventory->received_date
            ? $inventory->received_date->format('Y-m-d') . ($tzSuffix ? ' ' . $tzSuffix : '')
            : '-';

        return [
            $no,
            $inventory->item_name,
            $inventory->serial_number ?? '-',
            $inventory->category,
            $inventory->condition,
            $inventory->user->name ?? 'Tanpa Pemilik',
            $inventory->user->division?->name ?? '-',
            $inventory->user->branch?->name ?? '-',
            $receivedDate,
            'Aktif'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT,
        ];
    }

    /**
     * Custom Binder untuk memaksa Column C (Serial Number) jadi String
     */
    public function bindValue(Cell $cell, $value)
    {
        if ($cell->getColumn() === 'C') {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }

        // Default behavior for other columns
        return parent::bindValue($cell, $value);
    }
}

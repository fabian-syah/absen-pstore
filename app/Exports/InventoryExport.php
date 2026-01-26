<?php

namespace App\Exports;

use App\Models\Inventory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class InventoryExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithColumnFormatting
{
    private $inventoryData;

    public function __construct()
    {
        $this->inventoryData = Inventory::with(['user', 'user.division', 'user.branch'])
            ->whereNotNull('user_id')
            ->latest()
            ->get();
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
            (string) ($inventory->serial_number ?? '-'), // Cast string explisit
            $inventory->category,
            $inventory->condition,
            $inventory->user->name ?? 'Tanpa Pemilik',
            $inventory->user->division?->name ?? '-',
            $inventory->user->branch?->name ?? '-',
            $receivedDate, // Menggunakan formatting baru
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
            'C' => NumberFormat::FORMAT_TEXT, // Paksa Kolom C (Serial Number) jadi Text
        ];
    }
}

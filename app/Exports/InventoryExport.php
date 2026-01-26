<?php

namespace App\Exports;

use App\Models\Inventory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
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

        return [
            $no,
            $inventory->item_name,
            $inventory->serial_number ?? '-',
            $inventory->category,
            $inventory->condition,
            $inventory->user->name ?? 'Tanpa Pemilik',
            $inventory->user->division?->name ?? '-',
            $inventory->user->branch?->name ?? '-',
            $inventory->received_date,
            'Aktif'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

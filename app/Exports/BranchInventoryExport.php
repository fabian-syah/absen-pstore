<?php

namespace App\Exports;

use App\Models\Inventory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BranchInventoryExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $branchId;
    private $inventoryData;

    public function __construct($branchId)
    {
        $this->branchId = $branchId;

        // Cache data
        $this->inventoryData = Inventory::whereHas('user', function ($q) {
            $q->where('branch_id', $this->branchId);
        })->with(['user', 'user.division', 'user.branch'])->latest()->get();
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
            $inventory->user->name ?? 'Tanpa Pemilik (Gudang)',
            $inventory->user->division?->name ?? '-',
            $inventory->received_date,
            $inventory->user_id ? 'Aktif' : 'Gudang'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

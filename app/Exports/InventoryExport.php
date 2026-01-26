<?php

namespace App\Exports;

use App\Models\Inventory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithDrawings, WithEvents, WithColumnWidths
{
    private $inventoryData;

    public function __construct()
    {
        // Cache data agar konsisten antara collection() dan drawings()
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
            'Foto Barang',     // Kolom B
            'Bukti Serah Terima', // Kolom C
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
            '', // Placeholder untuk Foto Barang (B)
            '', // Placeholder untuk Foto User (C)
            $inventory->item_name,
            $inventory->serial_number ?? '-',
            $inventory->category,
            $inventory->condition,
            $inventory->user->name ?? 'Tanpa Pemilik',
            $inventory->user->division->name ?? '-',
            $inventory->user->branch->name ?? '-',
            $inventory->received_date,
            'Aktif'
        ];
    }

    public function drawings()
    {
        $drawings = [];
        $row = 2; // Mulai dari baris ke-2 (Baris 1 Header)

        foreach ($this->inventoryData as $item) {
            // 1. Gambar Barang (Kolom B)
            if ($item->item_photo_path) {
                $path = public_path('storage/' . $item->item_photo_path);
                if (file_exists($path)) {
                    $drawing = new Drawing();
                    $drawing->setName('Foto Barang');
                    $drawing->setDescription('Foto Barang');
                    $drawing->setPath($path);
                    $drawing->setHeight(50);
                    $drawing->setCoordinates('B' . $row);
                    $drawing->setOffsetX(5);
                    $drawing->setOffsetY(5);
                    $drawings[] = $drawing;
                }
            }

            // 2. Gambar User (Kolom C)
            if ($item->user_item_photo_path) {
                $path = public_path('storage/' . $item->user_item_photo_path);
                if (file_exists($path)) {
                    $drawing = new Drawing();
                    $drawing->setName('Foto User');
                    $drawing->setDescription('Foto User');
                    $drawing->setPath($path);
                    $drawing->setHeight(50);
                    $drawing->setCoordinates('C' . $row);
                    $drawing->setOffsetX(5);
                    $drawing->setOffsetY(5);
                    $drawings[] = $drawing;
                }
            }

            $row++;
        }

        return $drawings;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'B' => 15, // Lebar kolom Foto Barang
            'C' => 15, // Lebar kolom Foto User
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Set tinggi baris untuk semua data menjadi 60px agar gambar muat
                $lastRow = $event->sheet->getHighestRow();
                for ($i = 2; $i <= $lastRow; $i++) {
                    $event->sheet->getDelegate()->getRowDimension($i)->setRowHeight(60);
                }

                // Alignment middle vertical untuk semua sel
                $event->sheet->getDelegate()->getStyle('A1:L' . $lastRow)
                    ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            },
        ];
    }
}

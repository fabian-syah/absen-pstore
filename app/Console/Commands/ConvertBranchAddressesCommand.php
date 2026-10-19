<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Branch;

class ConvertBranchAddressesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'branches:convert-addresses {--branch_id= : ID cabang spesifik yang ingin dikonversi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Konversi link Google Maps di kolom alamat cabang menjadi teks alamat resmi';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $branchId = $this->option('branch_id');

        $query = Branch::query();
        if ($branchId) {
            $query->where('id', $branchId);
        } else {
            $query->where(function ($q) {
                $q->where('address', 'LIKE', '%http%')
                  ->orWhere('address', 'LIKE', '%maps%')
                  ->orWhere('address', 'LIKE', '%goo.gl%');
            });
        }

        $branches = $query->get();

        if ($branches->isEmpty()) {
            $this->info('Tidak ditemukan cabang dengan link Google Maps di kolom alamat.');
            return 0;
        }

        $this->info("Menemukan {$branches->count()} cabang untuk diproses...");

        $converted = 0;
        foreach ($branches as $branch) {
            $this->line("Memproses Cabang [{$branch->id}] {$branch->name}...");
            $oldAddress = $branch->address;

            if ($branch->convertAddressToText()) {
                $branch->refresh();
                $this->info(" -> Berhasil! Alamat baru: {$branch->address}");
                $converted++;
            } else {
                $this->warn(" -> Dilewati atau gagal dikonversi.");
            }
        }

        $this->info("Selesai! Berhasil mengonversi {$converted} dari {$branches->count()} cabang.");
        return 0;
    }
}

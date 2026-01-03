<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class MarkAbsentEmployees extends Command
{
    /**
     * Signature sekarang menerima parameter opsional bulan dan tahun.
     * Format: php artisan attendance:mark-absent {--month=} {--year=}
     */
    protected $signature = 'attendance:mark-absent {--month= : Bulan dalam angka 1-12} {--year= : Tahun contoh 2023}';
    
    protected $description = 'Cek user yang tidak absen pada periode tertentu dan tandai Alpha secara otomatis';

    public function handle()
    {
        // 1. Ambil input dari user atau gunakan default (bulan/tahun sekarang)
        $month = $this->option('month') ?: Carbon::now()->month;
        $year  = $this->option('year') ?: Carbon::now()->year;

        try {
            // Tentukan range: Awal bulan s/d akhir bulan yang dipilih
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate   = $startDate->copy()->endOfMonth();

            // Proteksi: Jika mengecek bulan berjalan, jangan sampai melewati hari kemarin
            if ($startDate->isCurrentMonth()) {
                $endDate = Carbon::yesterday();
            }
            
            // Proteksi: Jika mencoba cek masa depan
            if ($startDate->isFuture()) {
                $this->error("Anda tidak bisa mengecek absen untuk bulan di masa depan!");
                return;
            }

        } catch (\Exception $e) {
            $this->error("Format bulan atau tahun salah.");
            return;
        }

        // Buat periode tanggal
        $period = CarbonPeriod::create($startDate, $endDate);

        $this->info("=== PROSES AUTO-ALPHA ===");
        $this->info("Periode: " . $startDate->format('F Y'));
        $this->info("Range  : " . $startDate->format('d-m-Y') . " s/d " . $endDate->format('d-m-Y'));
        $this->newLine();

        $users = User::where('role', '!=', 'super_admin')->get();
        $totalAlphaCreated = 0;

        foreach ($users as $user) {
            $this->line("Mengecek User: <info>{$user->name}</info>");
            $userAlphaCount = 0;

            foreach ($period as $date) {
                $currentDate = $date->copy();

                // --- CEK 1: Apakah Hari Libur (Sabtu/Minggu)? ---
                // Biasanya Alpha tidak dihitung di hari libur.
                if ($currentDate->isWeekend()) {
                    continue;
                }

                // --- CEK 2: Apakah sudah ada data kehadiran/alpha? ---
                $existingAttendance = Attendance::where('user_id', $user->id)
                    ->whereDate('check_in_time', $currentDate)
                    ->exists();

                if ($existingAttendance) {
                    continue; 
                }

                // --- CEK 3: Apakah sedang Cuti / Izin? ---
                $isOnLeave = LeaveRequest::where('user_id', $user->id)
                    ->where('status', 'approved')
                    ->where('type', '!=', 'telat')
                    ->where(function($query) use ($currentDate) {
                        $query->whereDate('start_date', '<=', $currentDate)
                              ->whereDate('end_date', '>=', $currentDate);
                    })
                    ->exists();

                if ($isOnLeave) {
                    continue;
                }

                // --- EKSEKUSI: BUAT DATA ALPHA ---
                try {
                    Attendance::create([
                        'user_id'           => $user->id,
                        'branch_id'         => $user->branch_id ?? 1,
                        'check_in_time'     => $currentDate->copy()->setTime(0, 0, 0),
                        'check_out_time'    => $currentDate->copy()->setTime(0, 0, 0),
                        'status'            => 'verified',
                        'presence_status'   => 'Alpha',
                        'attendance_type'   => 'system',
                        'audit_note'        => "System Auto-Generate: Backfill Alpha for " . $currentDate->format('M Y'),
                        'photo_path'        => null,
                        'is_late_checkin'   => false,
                        'is_early_checkout' => false,
                    ]);

                    $userAlphaCount++;
                    $totalAlphaCreated++;

                } catch (\Exception $e) {
                    $this->error("  -> Error tgl " . $currentDate->format('d-m-Y') . ": " . $e->getMessage());
                }
            }
            
            if ($userAlphaCount > 0) {
                $this->comment("  -> Berhasil menambahkan $userAlphaCount hari Alpha.");
            }
        }

        $this->newLine();
        $this->info("Selesai! Total record Alpha baru dibuat: $totalAlphaCreated");
    }
}
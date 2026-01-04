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
    
    protected $description = 'Cek user yang tidak absen pada periode tertentu dan tandai Alpha secara otomatis (Hanya sampai H-1)';

    public function handle()
    {
        // 1. Ambil input dari user atau gunakan default (bulan/tahun sekarang)
        $month = $this->option('month') ?: Carbon::now()->month;
        $year  = $this->option('year') ?: Carbon::now()->year;

        try {
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $yesterday = Carbon::yesterday(); // Batas maksimal adalah kemarin
            
            // Tentukan akhir pencarian: akhir bulan atau kemarin (mana yang lebih dulu)
            $endOfMonth = $startDate->copy()->endOfMonth();
            
            if ($endOfMonth->isFuture()) {
                $endDate = $yesterday;
            } else {
                $endDate = $endOfMonth;
            }

            // Proteksi: Jika mencoba cek bulan di masa depan yang bahkan tanggal 1-nya belum lewat
            if ($startDate->gt($yesterday)) {
                $this->error("Gagal: Anda tidak bisa mengecek absen untuk periode masa depan atau hari ini!");
                return;
            }

        } catch (\Exception $e) {
            $this->error("Format bulan atau tahun salah.");
            return;
        }

        // Buat periode tanggal dari tgl 1 sampai $endDate (maksimal kemarin)
        $period = CarbonPeriod::create($startDate, $endDate);

        $this->info("=== PROSES AUTO-ALPHA (SISTEM) ===");
        $this->info("Periode : " . $startDate->format('F Y'));
        $this->info("Range   : " . $startDate->format('d-m-Y') . " s/d " . $endDate->format('d-m-Y'));
        $this->info("Catatan : Hari ini (" . Carbon::now()->format('d-m-Y') . ") tidak diproses.");
        $this->newLine();

        // Ambil user non-admin (sesuaikan logic role di aplikasi Anda)
        $users = User::where('role', '!=', 'super_admin')->get();
        $totalAlphaCreated = 0;

        foreach ($users as $user) {
            $this->line("Mengecek User: <info>{$user->name}</info>");
            $userAlphaCount = 0;

            foreach ($period as $date) {
                $currentDate = $date->copy();

                // --- CEK 1: Apakah Hari Libur (Sabtu/Minggu)? ---
                // Aktifkan kode di bawah jika Sabtu/Minggu TIDAK boleh dianggap Alpha
                /*
                if ($currentDate->isWeekend()) {
                    continue;
                }
                */

                // --- CEK 2: Apakah sudah ada data kehadiran/alpha di database? ---
                $existingAttendance = Attendance::where('user_id', $user->id)
                    ->whereDate('check_in_time', $currentDate)
                    ->exists();

                if ($existingAttendance) {
                    continue; 
                }

                // --- CEK 3: Apakah sedang Cuti / Izin yang sudah disetujui? ---
                $isOnLeave = LeaveRequest::where('user_id', $user->id)
                    ->where('status', 'approved')
                    ->where('type', '!=', 'telat') // 'telat' biasanya tetap harus absen, sesuaikan
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
                        // Set jam ke 00:00:00 untuk menandakan record sistem
                        'check_in_time'     => $currentDate->copy()->startOfDay(),
                        'check_out_time'    => $currentDate->copy()->startOfDay(),
                        'status'            => 'verified',
                        'presence_status'   => 'Alpha',
                        'attendance_type'   => 'system',
                        'audit_note'        => "Auto-Alpha: Tidak ada record hingga " . $yesterday->format('d-m-Y'),
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
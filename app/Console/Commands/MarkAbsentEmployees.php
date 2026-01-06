<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Carbon\CarbonPeriod; // Import ini penting untuk looping tanggal

class MarkAbsentEmployees extends Command
{
    protected $signature = 'attendance:mark-absent';
    protected $description = 'Cek user yang tidak absen dari awal bulan sampai kemarin dan tandai Alpha';

    public function handle()
    {
        // 1. Tentukan Range Tanggal (Dari Awal Bulan s/d Kemarin)
        // Contoh: Sekarang tgl 21, loop dari tgl 1 s/d tgl 20.
        $startDate = Carbon::now()->startOfMonth(); 
        $endDate   = Carbon::yesterday();

        // Validasi: Jika script jalan tanggal 1, kemarin adalah bulan lalu.
        // Opsional: Jika mau handle bulan lalu juga, logic start-nya bisa disesuaikan.
        // Untuk sekarang kita asumsikan cek bulan berjalan.
        if ($endDate->lt($startDate)) {
             $this->info("Belum ada tanggal yang perlu dicek bulan ini.");
             return;
        }

        // Buat periode tanggal untuk diloop
        $period = CarbonPeriod::create($startDate, $endDate);

        $this->info("Memulai proses Auto-Alpha dari: " . $startDate->format('d-m-Y') . " s/d " . $endDate->format('d-m-Y'));

        $users = User::where('role', '!=', 'super_admin')->get();
        $totalAlphaCreated = 0;

        foreach ($users as $user) {
            $this->info("Mengecek User: {$user->name}");

            // --- LOOPING TANGGAL (1, 2, 3 ... 20) ---
            foreach ($period as $date) {
                $currentDate = $date->copy(); // Copy agar object carbon tidak berubah

                // --- CEK 1: Apakah SUDAH ADA data absensi di tanggal ini? ---
                // Baik itu Hadir, Telat, atau BAHKAN SUDAH ALPHA (dari run sebelumnya)
                $existingAttendance = Attendance::where('user_id', $user->id)
                    ->whereDate('check_in_time', $currentDate)
                    ->exists();

                if ($existingAttendance) {
                    // Skip, karena data hari itu sudah terisi (entah dia masuk, atau script ini sudah pernah jalan sebelumnya)
                    continue; 
                }

                // --- CEK 2: Apakah user SEDANG CUTI / IZIN di tanggal ini? ---
                $isOnLeave = LeaveRequest::where('user_id', $user->id)
                    ->where('status', 'approved')
                    ->where('type', '!=', 'telat')
                    ->where(function($query) use ($currentDate) {
                        $query->whereDate('start_date', '<=', $currentDate)
                              ->whereDate('end_date', '>=', $currentDate);
                    })
                    ->exists();

                if ($isOnLeave) {
                    continue; // Skip, dia izin resmi
                }

                // --- CEK 3: Apakah Hari Libur (Sabtu/Minggu)? ---
                // Hapus komentar di bawah jika Alpha tidak berlaku di weekend
                /*
                if ($currentDate->isWeekend()) {
                     continue;
                }
                */

                // --- EKSEKUSI: BUAT DATA ALPHA ---
                // Data kosong & tidak izin = ALPHA
                try {
                    Attendance::create([
                        'user_id'           => $user->id,
                        'branch_id'         => $user->branch_id ?? 1, // Default 1 jika null
                        'check_in_time'     => $currentDate->setTime(0, 0, 0),
                        'check_out_time'    => $currentDate->setTime(0, 0, 0),
                        'status'            => 'verified',
                        'presence_status'   => 'Alpha',
                        'attendance_type'   => 'system',
                        'audit_note'        => 'System Auto-Generate: Backfill Alpha check.',
                        // Field lain set default/null
                        'photo_path'        => null,
                        'is_late_checkin'   => false,
                        'is_early_checkout' => false,
                    ]);

                    $this->comment("  -> Tanggal " . $currentDate->format('d-m-Y') . ": Ditetapkan ALPHA.");
                    $totalAlphaCreated++;

                } catch (\Exception $e) {
                    $this->error("  -> Error tgl " . $currentDate->format('d-m-Y') . ": " . $e->getMessage());
                }
            }
        }

        $this->info("Selesai. Total record Alpha baru dibuat: $totalAlphaCreated");
    }
}
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
    protected $description = 'Cek user yang tidak absen, tandai Alpha, dan bersihkan record Alpha yang salah (Repair Mode)';

    public function handle()
    {
        // 1. Tentukan Range Tanggal (Dari Awal Bulan s/d Kemarin)
        // Contoh: Sekarang tgl 21, loop dari tgl 1 s/d tgl 20.
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::yesterday();

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

        $users = User::where('is_active', true)->where('role', '!=', 'super_admin')->with('branch')->get();
        $totalAlphaCreated = 0;

        foreach ($users as $user) {
            $this->info("Mengecek User: {$user->name}");

            // --- LOOPING TANGGAL (1, 2, 3 ... 20) ---
            foreach ($period as $date) {
                $currentDate = $date->copy(); // Copy agar object carbon tidak berubah

                // --- CEK 1: Apakah SUDAH ADA data absensi di tanggal ini? ---
                // Baik itu Hadir, Telat, atau BAHKAN SUDAH ALPHA (dari run sebelumnya)
                $branchTimezone = $user->branch->timezone ?? 'Asia/Jakarta';
                $branchOffset = Carbon::now($branchTimezone)->format('P');
                $storageOffset = Carbon::now(config('app.timezone'))->format('P');

                // --- CEK 1: Apakah SUDAH ADA data absensi di tanggal ini? ---
                $allAttendances = Attendance::where('user_id', $user->id)
                    ->whereRaw("DATE(CONVERT_TZ(check_in_time, ?, ?)) = ?", [$storageOffset, $branchOffset, $currentDate->format('Y-m-d')])
                    ->get();

                $existingAttendance = $allAttendances->first();

                // --- REPAIR LOGIC: Bersihkan Alpha yang "nyelip" padahal ada absen real ---
                if ($allAttendances->count() > 1) {
                    $hasRealPresence = $allAttendances->contains(function ($att) {
                        return strtolower($att->presence_status) !== 'alpha';
                    });

                    if ($hasRealPresence) {
                        foreach ($allAttendances as $att) {
                            if (strtolower($att->presence_status) === 'alpha') {
                                $att->delete();
                                $this->warn("  -> Tanggal " . $currentDate->format('d-m-Y') . ": Alpha tidak valid ditemukan (padahal ada absensi), Telah dihapus.");
                            }
                        }
                        // Refresh data setelah delete
                        $existingAttendance = Attendance::where('user_id', $user->id)
                            ->whereRaw("DATE(CONVERT_TZ(check_in_time, ?, ?)) = ?", [$storageOffset, $branchOffset, $currentDate->format('Y-m-d')])
                            ->first();
                    }
                }

                // --- CEK 2: Apakah user SEDANG CUTI / IZIN di tanggal ini? ---
                $isOnLeave = LeaveRequest::where('user_id', $user->id)
                    ->where('status', 'approved')
                    ->where('type', '!=', 'telat')
                    ->where(function ($query) use ($currentDate) {
                        $query->whereDate('start_date', '<=', $currentDate)
                            ->where(function ($q) use ($currentDate) {
                                $q->whereNull('end_date')
                                    ->orWhere('end_date', '>=', $currentDate);
                            });
                    })
                    ->first();

                if ($existingAttendance) {
                    // Jika ada attendance dan itu ALPHA, tapi sekarang ternyata ada IZIN/CUTI yang di-acc
                    // Maka update record Alpha tersebut menjadi status Izin/Cuti
                    if (strtolower($existingAttendance->presence_status) === 'alpha' && $isOnLeave) {
                        $presenceStatusMap = [
                            'wfh' => 'WFH',
                            'izin' => 'Izin',
                            'sakit' => 'Sakit',
                            'cuti' => 'Cuti',
                            'libur' => 'Libur',
                            'dinas' => 'Dinas Luar',
                        ];
                        $newStatus = $presenceStatusMap[$isOnLeave->type] ?? ucfirst($isOnLeave->type);

                        $existingAttendance->update([
                            'presence_status' => $newStatus,
                            'attendance_type' => 'leave',
                            'notes' => 'Auto-Update: Leave approved after Alpha generation. Reason: ' . $isOnLeave->reason
                        ]);
                        $this->comment("  -> Tanggal " . $currentDate->format('d-m-Y') . ": Alpha diperbarui menjadi " . $newStatus);
                    }
                    continue; // Skip ke tanggal berikutnya
                }

                if ($isOnLeave) {
                    continue; // Skip, dia izin resmi (dan belum ada record attendance)
                }

                // --- EKSEKUSI: BUAT DATA ALPHA ---
                try {
                    Attendance::create([
                        'user_id' => $user->id,
                        'branch_id' => $user->branch_id ?? 1,
                        'check_in_time' => $currentDate->copy()->startOfDay(),
                        'check_out_time' => $currentDate->copy()->startOfDay(),
                        'status' => 'verified',
                        'presence_status' => 'Alpha',
                        'attendance_type' => 'system',
                        'audit_note' => 'System Auto-Generate: Backfill Alpha check.',
                        'photo_path' => null,
                        'is_late_checkin' => false,
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
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;
use App\Models\LeaderboardHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CloseMonthlyLeaderboard extends Command
{
    /**
     * Signature diubah agar bisa menerima input manual (opsional).
     * Contoh manual: php artisan leaderboard:close-month --month=11 --year=2025
     * Contoh otomatis: php artisan leaderboard:close-month
     */
    protected $signature = 'leaderboard:close-month {--month= : Bulan (angka 1-12)} {--year= : Tahun (4 digit)}';

    protected $description = 'Simpan Top 3 Pemenang Bulan Lalu (Otomatis) atau Bulan Tertentu (Manual)';

    public function handle()
    {
        // 1. TENTUKAN PERIODE WAKTU
        $inputMonth = $this->option('month');
        $inputYear = $this->option('year');

        if ($inputMonth && $inputYear) {
            // MODE MANUAL: Jika admin memasukkan bulan & tahun
            $month = (int) $inputMonth;
            $year = (int) $inputYear;
            $this->info("MODE MANUAL: Memproses Leaderboard untuk periode $month - $year...");
        } else {
            // MODE OTOMATIS (CRON JOB): Ambil bulan lalu dari hari ini
            $lastMonthDate = Carbon::now()->subMonth();
            $month = $lastMonthDate->month;
            $year = $lastMonthDate->year;
            $this->info("MODE OTOMATIS: Memproses Leaderboard untuk bulan lalu ($month - $year)...");
        }

        // Validasi input manual biar ga error
        if ($month < 1 || $month > 12 || $year < 2020 || $year > 2030) {
            $this->error('Error: Format Bulan (1-12) atau Tahun salah.');
            return;
        }

        // 2. HITUNG JUARA (LOGIKA SAMA SEPERTI DASHBOARD)
        $winners = Attendance::select('user_id', DB::raw('count(*) as total_attendance'))
            ->whereMonth('check_in_time', $month)
            ->whereYear('check_in_time', $year)
            ->whereNotNull('check_out_time')
            ->whereIn('presence_status', ['Masuk', 'WFH', 'WFH / Dinas Luar']) // Sesuaikan dengan status di DB Anda
            ->where('status', 'verified') // Pastikan hanya yang verified
            ->whereHas('user', function($q) {
                // Filter User Aktif & Bukan Admin (Sesuai request: Admin tidak ikut)
                $q->where('is_active', true)
                  ->whereNotIn('role', ['admin']); 
            })
            ->groupBy('user_id')
            ->orderByDesc('total_attendance')
            // Tambahan sorting kedua: Siapa yang rata-rata jam masuknya paling pagi (opsional, biar adil kalau seri)
            ->orderBy(DB::raw('MIN(TIME(check_in_time))'), 'asc') 
            ->take(3) // HANYA AMBIL JUARA 1, 2, 3
            ->get();

        if ($winners->isEmpty()) {
            $this->warn("Tidak ada data absensi yang memenuhi syarat untuk periode ini.");
            return;
        }

        $this->info("Ditemukan " . $winners->count() . " kandidat pemenang.");

        // 3. SIMPAN KE HISTORY DATABASE
        foreach ($winners as $index => $winner) {
            $rank = $index + 1;
            
            // Cek apakah data sudah ada sebelumnya (Mencegah duplikasi jika command dijalankan 2x)
            $existingRecord = LeaderboardHistory::where('user_id', $winner->user_id)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            if ($existingRecord) {
                // Opsi A: Skip jika sudah ada
                // $this->warn("Juara $rank (User ID: {$winner->user_id}) sudah ada di database. Skip.");
                
                // Opsi B: Update jika ada perubahan (Lebih fleksibel untuk manual fix)
                $existingRecord->update([
                    'rank' => $rank,
                    'total_attendance' => $winner->total_attendance
                ]);
                $this->comment("Juara $rank (User ID: {$winner->user_id}) diperbarui datanya.");
            } else {
                // Create Baru
                LeaderboardHistory::create([
                    'user_id' => $winner->user_id,
                    'rank' => $rank,
                    'month' => $month,
                    'year' => $year,
                    'total_attendance' => $winner->total_attendance
                ]);
                $this->info("Juara $rank (User ID: {$winner->user_id}) BERHASIL DISIMPAN! Total Hadir: {$winner->total_attendance}");
            }
        }

        $this->info('------------------------------------------------');
        $this->info(" Selesai! Leaderboard periode $month-$year telah diperbarui.");
        $this->info('------------------------------------------------');
    }
}
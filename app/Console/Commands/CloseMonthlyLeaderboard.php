<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;
use App\Models\LeaderboardHistory;
use App\Models\Branch; // Tambahkan ini
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CloseMonthlyLeaderboard extends Command
{
    protected $signature = 'leaderboard:close-month {--month= : Bulan (angka 1-12)} {--year= : Tahun (4 digit)}';
    protected $description = 'Simpan Top 3 Pemenang per Cabang';

    public function handle()
    {
        // 1. TENTUKAN PERIODE WAKTU
        $inputMonth = $this->option('month');
        $inputYear = $this->option('year');

        if ($inputMonth && $inputYear) {
            $month = (int) $inputMonth;
            $year = (int) $inputYear;
        } else {
            $lastMonthDate = Carbon::now()->subMonth();
            $month = $lastMonthDate->month;
            $year = $lastMonthDate->year;
        }

        if ($month < 1 || $month > 12 || $year < 2020 || $year > 2030) {
            $this->error('Error: Format Bulan atau Tahun salah.');
            return;
        }

        $this->info("Memproses Leaderboard Per Cabang untuk periode $month - $year...");

        // 2. AMBIL SEMUA CABANG
        $branches = Branch::all();

        foreach ($branches as $branch) {
            $this->line("Mengolah Cabang: {$branch->name}...");

            // 3. HITUNG JUARA PER CABANG
            $winners = Attendance::select('user_id', DB::raw('count(*) as total_attendance'))
                ->where('branch_id', $branch->id) // FILTER PER CABANG
                ->whereMonth('check_in_time', $month)
                ->whereYear('check_in_time', $year)
                ->whereNotNull('check_out_time')
                ->whereIn('presence_status', ['Masuk', 'WFH', 'WFH / Dinas Luar'])
                ->where('status', 'verified')
                ->whereHas('user', function($q) {
                    $q->where('is_active', true)
                      ->whereNotIn('role', ['admin', 'security']); // Admin & Security tidak ikut
                })
                ->groupBy('user_id')
                ->orderByDesc('total_attendance')
                // Tie breaker: yang paling rajin/pagi (opsional)
                ->orderBy(DB::raw('MIN(TIME(check_in_time))'), 'asc') 
                ->take(3)
                ->get();

            if ($winners->isEmpty()) {
                $this->warn("- Tidak ada data untuk cabang {$branch->name}.");
                continue;
            }

            // 4. SIMPAN KE HISTORY
            foreach ($winners as $index => $winner) {
                $rank = $index + 1;
                
                // Update atau Create data history
                LeaderboardHistory::updateOrCreate(
                    [
                        'user_id' => $winner->user_id,
                        'month'   => $month,
                        'year'    => $year,
                        'branch_id' => $branch->id, // Pastikan tabel LeaderboardHistory punya kolom branch_id
                    ],
                    [
                        'rank'             => $rank,
                        'total_attendance' => $winner->total_attendance
                    ]
                );
            }
            $this->info("- Berhasil menyimpan Top " . $winners->count() . " untuk cabang {$branch->name}.");
        }

        $this->info('Selesai! Leaderboard semua cabang telah diperbarui.');
    }
}
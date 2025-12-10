<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;
use App\Models\LeaderboardHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CloseMonthlyLeaderboard extends Command
{
    protected $signature = 'leaderboard:close-month';
    protected $description = 'Simpan Top 3 Pemenang Bulan Lalu dan Reset untuk Bulan Baru';

    public function handle()
    {
        // 1. Ambil Bulan Lalu (Karena perintah jalan di tgl 1 bulan baru)
        $lastMonthDate = Carbon::now()->subMonth();
        $month = $lastMonthDate->month;
        $year = $lastMonthDate->year;

        $this->info("Memproses Leaderboard untuk periode: $month - $year");

        // 2. Hitung Top 3 (Logic sama persis dengan Dashboard)
        $winners = Attendance::select('user_id', DB::raw('count(*) as total'))
            ->whereMonth('check_in_time', $month)
            ->whereYear('check_in_time', $year)
            ->whereNotNull('check_out_time')
            ->whereIn('presence_status', ['Masuk', 'WFH', 'WFH / Dinas Luar'])
            ->where('status', 'verified')
            ->whereHas('user', function($q) {
                $q->where('is_active', true)
                  ->whereNotIn('role', ['admin']); 
            })
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->take(3) // HANYA AMBIL JUARA 1, 2, 3
            ->get();

        // 3. Simpan ke History
        foreach ($winners as $index => $winner) {
            $rank = $index + 1;
            
            // Cek duplikasi agar tidak double save
            $exists = LeaderboardHistory::where('user_id', $winner->user_id)
                ->where('month', $month)
                ->where('year', $year)
                ->exists();

            if (!$exists) {
                LeaderboardHistory::create([
                    'user_id' => $winner->user_id,
                    'rank' => $rank,
                    'month' => $month,
                    'year' => $year,
                    'total_attendance' => $winner->total
                ]);
                $this->info("Juara $rank: User ID {$winner->user_id} disimpan.");
            }
        }

        $this->info('Selesai. Leaderboard bulan lalu telah diarsipkan.');
    }
}
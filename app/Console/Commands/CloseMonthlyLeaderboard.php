<?php

namespace App\Console\Commands;

use App\Models\User;
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
        // 1. TENTUKAN PERIODE WAKTU (Logika subMonth tetap sama)
        $inputMonth = $this->option('month');
        $inputYear = $this->option('year');
        if ($inputMonth && $inputYear) {
            $month = (int) $inputMonth; $year = (int) $inputYear;
        } else {
            $lastMonthDate = Carbon::now()->subMonth();
            $month = $lastMonthDate->month; $year = $lastMonthDate->year;
        }

        $branches = Branch::all();

        foreach ($branches as $branch) {
            $this->info("=== Mengolah Cabang: {$branch->name} ===");

            // --- A. SIMPAN TOP 3 ABSENSI ---
            $winners = Attendance::select('user_id', DB::raw('count(*) as total_attendance'))
                ->where('branch_id', $branch->id)
                ->whereMonth('check_in_time', $month)->whereYear('check_in_time', $year)
                ->where('status', 'verified')
                ->whereIn('presence_status', ['Masuk', 'WFH', 'WFH / Dinas Luar'])
                ->whereHas('user', function($q) {
                    $q->where('is_active', true)->whereNotIn('role', ['admin', 'security']);
                })
                ->groupBy('user_id')->orderByDesc('total_attendance')
                ->take(3)->get();

            foreach ($winners as $index => $winner) {
                LeaderboardHistory::updateOrCreate(
                    ['user_id' => $winner->user_id, 'month' => $month, 'year' => $year, 'branch_id' => $branch->id, 'type' => 'attendance'],
                    ['rank' => $index + 1, 'total_attendance' => $winner->total_attendance]
                );
            }

            // --- B. SIMPAN TOP 3 SCANNER (SECURITY) ---
            $securityUsers = User::where('branch_id', $branch->id)->whereIn('role', ['security', 'admin'])->get();
            
            $scanners = $securityUsers->map(function ($sec) use ($month, $year) {
                $scanIn = Attendance::where('scanned_by_user_id', $sec->id)
                    ->whereMonth('check_in_time', $month)->whereYear('check_in_time', $year)->count();
                $scanOut = Attendance::where('scanned_out_by_user_id', $sec->id)
                    ->whereMonth('check_in_time', $month)->whereYear('check_in_time', $year)->count();
                $sec->temp_total = $scanIn + $scanOut;
                return $sec;
            })->sortByDesc('temp_total')->filter(fn($u) => $u->temp_total > 0)->take(3);

            foreach ($scanners->values() as $index => $sec) {
                LeaderboardHistory::updateOrCreate(
                    ['user_id' => $sec->id, 'month' => $month, 'year' => $year, 'branch_id' => $branch->id, 'type' => 'scanner'],
                    ['rank' => $index + 1, 'total_attendance' => $sec->temp_total]
                );
            }
            $this->info("- Sukses simpan data absensi & scanner.");
        }
    }
}
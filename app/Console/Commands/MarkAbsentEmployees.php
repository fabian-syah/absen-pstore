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
    protected $signature = 'attendance:mark-absent {--month= : Bulan 1-12} {--year= : Tahun contoh 2026}';
    protected $description = 'Tandai Alpha otomatis dengan proteksi H-1';

    public function handle()
    {
        $month = $this->option('month') ?: Carbon::now()->month;
        $year  = $this->option('year') ?: Carbon::now()->year;

        try {
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $now = Carbon::now();
            
            // --- LOGIKA PROTEKSI UTAMA ---
            // Jika mengecek bulan berjalan:
            // 1. Kita hanya boleh absenkan sampai H-1 (Kemarin).
            // 2. Jika dipaksa cek hari ini, hanya boleh dilakukan jika sudah lewat jam 21:00 (Hampir ganti hari).
            
            $yesterday = Carbon::yesterday();
            $endOfMonth = $startDate->copy()->endOfMonth();

            if ($startDate->isCurrentMonth()) {
                // Jangan pernah sentuh hari ini jika belum jam 9 malam
                $endDate = ($now->hour >= 21) ? Carbon::today() : $yesterday;
            } else {
                // Jika cek bulan lalu, ambil sampai akhir bulan tersebut
                $endDate = $endOfMonth->isFuture() ? $yesterday : $endOfMonth;
            }

            if ($startDate->gt($endDate)) {
                $this->error("Belum saatnya mengecek absensi untuk periode ini.");
                return;
            }

        } catch (\Exception $e) {
            $this->error("Format salah.");
            return;
        }

        $period = CarbonPeriod::create($startDate, $endDate);
        $users = User::where('role', '!=', 'super_admin')->where('status', 'active')->get();
        $count = 0;

        foreach ($users as $user) {
            foreach ($period as $date) {
                $currentDate = $date->copy();

                // Cek apakah sudah ada data (Masuk/Alpha)
                $exists = Attendance::where('user_id', $user->id)
                    ->whereDate('check_in_time', $currentDate)
                    ->exists();

                if ($exists) continue;

                // Cek Cuti/Izin
                $onLeave = LeaveRequest::where('user_id', $user->id)
                    ->where('status', 'approved')
                    ->where('type', '!=', 'telat')
                    ->whereDate('start_date', '<=', $currentDate)
                    ->whereDate('end_date', '>=', $currentDate)
                    ->exists();

                if ($onLeave) continue;

                Attendance::create([
                    'user_id'           => $user->id,
                    'branch_id'         => $user->branch_id ?? 1,
                    'check_in_time'     => $currentDate->copy()->startOfDay(),
                    'check_out_time'    => $currentDate->copy()->startOfDay(),
                    'status'            => 'verified',
                    'presence_status'   => 'Alpha',
                    'attendance_type'   => 'system',
                    'audit_note'        => "Auto-Alpha System (H-1 Protection)",
                ]);
                $count++;
            }
        }

        $this->info("Selesai! $count data Alpha dibuat.");
    }
}
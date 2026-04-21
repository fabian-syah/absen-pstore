<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class MarkAbsentEmployees extends Command
{
    protected $signature = 'attendance:mark-absent';
    protected $description = 'Cek kehadiran karyawan dengan dukungan Shift Malam (Operational Day)';

    public function handle()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $yesterday = Carbon::yesterday();

        $this->info("Memulai proses Auto-Alpha (Shift-Aware) dari: " . $startOfMonth->format('d-m-Y') . " s/d " . $yesterday->format('d-m-Y'));

        $users = User::where('is_active', true)
            ->whereNotIn('role', ['admin', 'super_admin'])
            ->with('branch')
            ->orderBy('name')
            ->get();

        $totalAlphaCreated = 0;

        foreach ($users as $user) {
            $this->info("Mengecek User: {$user->name}");

            for ($currentDate = $startOfMonth->copy(); $currentDate->lte($yesterday); $currentDate->addDay()) {
                
                // --- DEFINISI OPERATIONAL DAY ---
                // Kita cari absen mulai dari jam 16:00 (4 sore) hari sebelumnya 
                // sampai jam 23:59 hari berjalan. 
                // Ini mencakup karyawan shift malam yang masuk di sore/malam hari sebelumnya.
                $startRange = $currentDate->copy()->subHours(8); // Jam 4 sore kemarin
                $endRange = $currentDate->copy()->endOfDay();

                $allAttendances = Attendance::where('user_id', $user->id)
                    ->where('check_in_time', '>=', $startRange)
                    ->where('check_in_time', '<=', $endRange)
                    ->get();

                // Cari apakah ada absen "Real" (bukan Alpha) di range tersebut
                $hasRealPresence = $allAttendances->contains(function ($att) {
                    return strtolower($att->presence_status) !== 'alpha';
                });

                // --- REPAIR LOGIC ---
                // Jika sudah ada record Alpha tapi ternyata ada absen Real di range operational day, HAPUS ALPHANYA.
                if ($hasRealPresence) {
                    foreach ($allAttendances as $att) {
                        if (strtolower($att->presence_status) === 'alpha') {
                            $att->delete();
                            $this->warn("  -> Tanggal " . $currentDate->format('d-m-Y') . ": Alpha dihapus karena ditemukan Absen Shift Malam (Security/Scan).");
                        }
                    }
                    continue; // Dia hadir, aman.
                }

                // Jika sudah ada record Alpha (dan benar-benar tidak ada absen real), lewati.
                $existingAlpha = $allAttendances->where('presence_status', 'Alpha')->first();
                if ($existingAlpha) {
                    continue;
                }

                // --- CEK IZIN/CUTI ---
                $isOnLeave = LeaveRequest::where('user_id', $user->id)
                    ->where('status', 'approved')
                    ->where('type', '!=', 'telat')
                    ->whereDate('start_date', '<=', $currentDate)
                    ->where(function ($q) use ($currentDate) {
                        $q->whereNull('end_date')->orWhere('end_date', '>=', $currentDate);
                    })
                    ->first();

                try {
                    if ($isOnLeave) {
                        $presenceStatusMap = [
                            'wfh' => 'WFH', 'izin' => 'Izin', 'sakit' => 'Sakit',
                            'cuti' => 'Cuti', 'libur' => 'Libur', 'dinas' => 'Dinas Luar',
                        ];
                        $status = $presenceStatusMap[$isOnLeave->type] ?? 'Izin';

                        Attendance::create([
                            'user_id' => $user->id,
                            'branch_id' => $user->branch_id ?? 1,
                            'check_in_time' => $currentDate->copy()->setHour(8), // Set jam standar
                            'status' => 'verified',
                            'presence_status' => $status,
                            'attendance_type' => 'leave',
                            'notes' => 'Auto-Generated: ' . $isOnLeave->reason
                        ]);
                        $this->info("  -> Tanggal " . $currentDate->format('d-m-Y') . ": Ditetapkan " . strtoupper($status));
                        continue;
                    }

                    // --- TETAPKAN ALPHA (BENAR-BENAR TIDAK ADA ABSEN) ---
                    Attendance::create([
                        'user_id' => $user->id,
                        'branch_id' => $user->branch_id ?? 1,
                        'check_in_time' => $currentDate->copy()->startOfDay(),
                        'status' => 'verified',
                        'presence_status' => 'Alpha',
                        'attendance_type' => 'system',
                        'notes' => 'Auto-Generated: Tidak ada aktivitas ditemukan dalam 24 jam operasional.'
                    ]);

                    $this->error("  -> Tanggal " . $currentDate->format('d-m-Y') . ": Ditetapkan ALPHA.");
                    $totalAlphaCreated++;

                } catch (\Exception $e) {
                    $this->error("  -> Error tgl " . $currentDate->format('d-m-Y') . ": " . $e->getMessage());
                }
            }
        }

        $this->info("Selesai. Total record Alpha baru dibuat: {$totalAlphaCreated}");
    }
}
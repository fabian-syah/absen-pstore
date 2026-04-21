<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MarkAbsentEmployees extends Command
{
    protected $signature = 'attendance:mark-absent';
    protected $description = 'Cek user yang tidak absen, tandai Alpha, dan bersihkan record Alpha yang salah (Repair Mode + Shift Aware)';

    public function handle()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $yesterday = Carbon::yesterday();

        $this->info("Memulai proses Auto-Alpha dari: " . $startOfMonth->format('d-m-Y') . " s/d " . $yesterday->format('d-m-Y'));

        // 1. Ambil Semua User (Kecuali Admin/Security)
        $users = User::where('is_active', true)
            ->whereNotIn('role', ['admin', 'security', 'super_admin'])
            ->with('branch')
            ->orderBy('name')
            ->get();

        $totalAlphaCreated = 0;

        foreach ($users as $user) {
            $this->info("Mengecek User: {$user->name}");

            // Loop dari awal bulan sampai kemarin
            for ($currentDate = $startOfMonth->copy(); $currentDate->lte($yesterday); $currentDate->addDay()) {

                // Setup Offset untuk query timezone-aware
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
                    // Perbarui status jika ada izin yang baru di-approve
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
                    continue; 
                }

                // --- NEW: CEK CROSS-DAY SHIFT (SHIFT MALAM) ---
                $yesterdayShift = Attendance::where('user_id', $user->id)
                    ->whereRaw("DATE(CONVERT_TZ(check_in_time, ?, ?)) = ?", [$storageOffset, $branchOffset, $currentDate->copy()->subDay()->format('Y-m-d')])
                    ->whereNotNull('check_out_time')
                    ->first();

                if ($yesterdayShift) {
                    $checkOutLocal = Carbon::parse($yesterdayShift->check_out_time)->timezone($branchTimezone);
                    // Jika pulang setelah jam 4 subuh hari ini, anggap dia hadir pagi ini
                    if ($checkOutLocal->isSameDay($currentDate) && $checkOutLocal->hour >= 4) {
                        $this->info("  -> Tanggal " . $currentDate->format('d-m-Y') . ": Skip (Karyawan Shift Malam dari kemarin)");
                        continue;
                    }
                }

                try {
                    if ($isOnLeave) {
                        $presenceStatusMap = [
                            'wfh' => 'WFH',
                            'izin' => 'Izin',
                            'sakit' => 'Sakit',
                            'cuti' => 'Cuti',
                            'libur' => 'Libur',
                            'dinas' => 'Dinas Luar',
                        ];
                        $status = $presenceStatusMap[$isOnLeave->type] ?? 'Izin';

                        Attendance::create([
                            'user_id' => $user->id,
                            'branch_id' => $user->branch_id ?? 1,
                            'check_in_time' => $currentDate->copy()->startOfDay(),
                            'status' => 'verified',
                            'presence_status' => $status,
                            'attendance_type' => 'leave',
                            'notes' => 'Auto-Generated based on Leave Request: ' . $isOnLeave->reason
                        ]);
                        $this->info("  -> Tanggal " . $currentDate->format('d-m-Y') . ": Ditetapkan " . strtoupper($status) . " (Approved Leave)");
                        continue;
                    }

                    // ALPHA
                    Attendance::create([
                        'user_id' => $user->id,
                        'branch_id' => $user->branch_id ?? 1,
                        'check_in_time' => $currentDate->copy()->startOfDay(),
                        'check_out_time' => $currentDate->copy()->startOfDay(),
                        'status' => 'verified',
                        'presence_status' => 'Alpha',
                        'attendance_type' => 'system',
                        'notes' => 'Auto-Generated: No attendance or leave request found.'
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
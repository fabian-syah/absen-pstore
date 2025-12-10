<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceRecapController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $year = 2025; // Tahun target
        
        // ==========================================================
        // LOGIKA FILTER DIPERKETAT DI SINI
        // ==========================================================
        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('check_in_time', $year)
            ->whereNotNull('check_in_time')   // Wajib sudah absen masuk
            ->whereNotNull('check_out_time')  // Wajib sudah absen pulang (Selesai kerja)
            ->where('status', 'verified')     // WAJIB TERVERIFIKASI (Kunci utamanya)
            // Filter tambahan untuk memastikan bukan Alpha/Izin yang terinput tidak sengaja
            ->where(function($q) {
                $q->where('presence_status', 'Masuk')
                  ->orWhere('presence_status', 'like', '%WFH%') // Hitung WFH jika ada
                  ->orWhere('presence_status', 'like', '%Dinas%'); // Hitung Dinas Luar
            })
            ->get();

        // Jika tidak ada data yang valid (Hanya Alpha/Belum Verified semua)
        if ($attendances->isEmpty()) {
            return redirect()->route('dashboard')->with('warning', 'Belum ada data absensi valid (Masuk, Pulang & Terverifikasi) di tahun ini.');
        }

        // 1. Total Kehadiran (Hanya hari yang valid)
        $totalPresent = $attendances->count();

        // 2. Total Jam Kerja (Real Durasi)
        $totalSeconds = 0;
        foreach ($attendances as $att) {
            $start = Carbon::parse($att->check_in_time);
            $end = Carbon::parse($att->check_out_time);
            
            // Hitung selisih detik
            $totalSeconds += $end->diffInSeconds($start);
        }
        $totalHours = $totalSeconds > 0 ? round($totalSeconds / 3600) : 0;

        // 3. Rekor Paling Pagi (Dari data yang valid saja)
        $earliestCheckIn = $attendances->sortBy(function($att) {
            return Carbon::parse($att->check_in_time)->format('H:i:s');
        })->first();
            
        // 4. Statistik Telat (Dari data yang valid)
        $totalLate = $attendances->where('is_late_checkin', true)->count();
        
        // Hitung persentase (Cegah division by zero)
        $onTimePercentage = $totalPresent > 0 
            ? round((($totalPresent - $totalLate) / $totalPresent) * 100) 
            : 100;

        // 5. Tentukan "Persona" Karyawan
        $persona = $this->determinePersona($totalPresent, $totalLate, $earliestCheckIn);

        return view('attendance.recap', compact(
            'user', 
            'year', 
            'totalPresent', 
            'totalHours', 
            'earliestCheckIn', 
            'onTimePercentage',
            'persona'
        ));
    }

    private function determinePersona($total, $late, $earliest)
    {
        $earliestTime = $earliest ? Carbon::parse($earliest->check_in_time)->format('H:i') : '08:00';

        if ($total > 250 && $late == 0) {
            return [
                'title' => 'The Perfectionist',
                'desc' => 'Konsistensi adalah nama tengahmu. Tidak pernah telat, selalu hadir & terverifikasi.',
                'icon' => 'mdi-diamond-stone'
            ];
        } elseif ($earliestTime < '06:30') {
            return [
                'title' => 'The Morning Glory',
                'desc' => 'Kamu menyapa matahari sebelum ayam berkokok. Rajin luar biasa!',
                'icon' => 'mdi-weather-sunset'
            ];
        } elseif ($late > 15) { // Sedikit toleransi
            return [
                'title' => 'The Flash Sprinter',
                'desc' => 'Mungkin sering mepet waktu, tapi pekerjaanmu selalu tuntas!',
                'icon' => 'mdi-run-fast'
            ];
        } elseif ($totalHours > 2000) { // Jika jam kerja sangat tinggi
            return [
                'title' => 'The Workaholic',
                'desc' => 'Dedikasi tanpa batas. Kantor adalah rumah keduamu.',
                'icon' => 'mdi-briefcase-clock'
            ];
        } else {
            return [
                'title' => 'The Reliable Hero',
                'desc' => 'Tulang punggung tim. Selalu hadir, valid, dan terpercaya.',
                'icon' => 'mdi-shield-check'
            ];
        }
    }
}
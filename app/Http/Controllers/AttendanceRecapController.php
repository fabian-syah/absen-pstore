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
        $year = 2025; // Atau date('Y') jika ingin dinamis tahun berjalan
        
        // Query Data Setahun
        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('check_in_time', $year)
            ->whereNotNull('check_out_time')
            ->get();

        if ($attendances->isEmpty()) {
            return redirect()->route('dashboard')->with('error', 'Belum ada data absensi untuk tahun ini.');
        }

        // 1. Total Kehadiran
        $totalPresent = $attendances->count();

        // 2. Total Jam Kerja (Dalam Jam)
        $totalSeconds = 0;
        foreach ($attendances as $att) {
            $start = Carbon::parse($att->check_in_time);
            $end = Carbon::parse($att->check_out_time);
            $totalSeconds += $end->diffInSeconds($start);
        }
        $totalHours = round($totalSeconds / 3600);

        // 3. Paling Rajin (Datang Paling Pagi)
        $earliestCheckIn = Attendance::where('user_id', $user->id)
            ->whereYear('check_in_time', $year)
            ->orderBy(DB::raw('TIME(check_in_time)'), 'asc')
            ->first();
            
        // 4. Statistik Telat
        $totalLate = $attendances->where('is_late_checkin', true)->count();
        $onTimePercentage = $totalPresent > 0 ? round((($totalPresent - $totalLate) / $totalPresent) * 100) : 0;

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
        // Logika sederhana penentuan karakter
        if ($total > 250 && $late == 0) {
            return [
                'title' => 'The Perfectionist',
                'desc' => 'Konsistensi adalah nama tengahmu. Tidak pernah telat, selalu hadir.',
                'icon' => 'mdi-diamond-stone'
            ];
        } elseif ($earliest && Carbon::parse($earliest->check_in_time)->format('H:i') < '06:30') {
            return [
                'title' => 'The Morning Glory',
                'desc' => 'Kamu menyapa matahari sebelum ayam berkokok. Rajin luar biasa!',
                'icon' => 'mdi-weather-sunset'
            ];
        } elseif ($late > 20) {
            return [
                'title' => 'The Fashionably Late',
                'desc' => 'Waktu adalah konsep relatif bagimu, tapi kerjamu tetap beres!',
                'icon' => 'mdi-run-fast'
            ];
        } else {
            return [
                'title' => 'The Reliable Hero',
                'desc' => 'Tulang punggung tim. Selalu ada saat dibutuhkan.',
                'icon' => 'mdi-shield-check'
            ];
        }
    }
}
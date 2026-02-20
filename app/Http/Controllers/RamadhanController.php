<?php

namespace App\Http\Controllers;

use App\Models\FastingLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class RamadhanController extends Controller
{
    /**
     * Halaman utama Ramadhan
     */
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $hijriYear = 1447; // Ramadhan 1447 H = 2026 M

        // Ambil semua log puasa user untuk Ramadhan ini
        $fastingLogs = FastingLog::where('user_id', $user->id)
            ->where('hijri_year', $hijriYear)
            ->orderBy('ramadan_day')
            ->get()
            ->keyBy('ramadan_day');

        // Cek apakah user sudah jawab hari ini
        $todayLog = FastingLog::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        // Hitung statistik
        $totalFasting = FastingLog::where('user_id', $user->id)
            ->where('hijri_year', $hijriYear)
            ->where('is_fasting', true)
            ->count();

        $totalMissed = FastingLog::where('user_id', $user->id)
            ->where('hijri_year', $hijriYear)
            ->where('is_fasting', false)
            ->count();

        // Hitung hari ke-berapa Ramadhan (1 Ramadhan 1447 H ≈ 20 Feb 2026)
        $ramadanStart = Carbon::parse('2026-02-20');
        $ramadanDay = $ramadanStart->diffInDays($today) + 1;
        if ($ramadanDay < 1)
            $ramadanDay = 1;
        if ($ramadanDay > 30)
            $ramadanDay = 30;

        // Hitung awal minggu ini di Ramadhan (kelipatan 7)
        $weekStart = (int) (floor(($ramadanDay - 1) / 7) * 7) + 1;
        $weekEnd = min($weekStart + 6, 30);

        return view('ramadhan.index', compact(
            'fastingLogs',
            'todayLog',
            'totalFasting',
            'totalMissed',
            'ramadanDay',
            'weekStart',
            'weekEnd',
            'hijriYear',
            'ramadanStart'
        ));
    }

    /**
     * Simpan jawaban puasa hari ini
     */
    public function storeFasting(Request $request)
    {
        $request->validate([
            'is_fasting' => 'required|boolean',
        ]);

        $user = Auth::user();
        $today = Carbon::today();

        // Hitung hari Ramadhan
        $ramadanStart = Carbon::parse('2026-02-20');
        $ramadanDay = $ramadanStart->diffInDays($today) + 1;
        if ($ramadanDay < 1 || $ramadanDay > 30) {
            return response()->json(['success' => false, 'message' => 'Bukan bulan Ramadhan'], 400);
        }

        $log = FastingLog::updateOrCreate(
            [
                'user_id' => $user->id,
                'date' => $today->toDateString(),
            ],
            [
                'is_fasting' => $request->is_fasting,
                'ramadan_day' => $ramadanDay,
                'hijri_year' => 1447,
            ]
        );

        $totalFasting = FastingLog::where('user_id', $user->id)
            ->where('hijri_year', 1447)
            ->where('is_fasting', true)
            ->count();

        return response()->json([
            'success' => true,
            'is_fasting' => $log->is_fasting,
            'total_fasting' => $totalFasting,
            'ramadan_day' => $ramadanDay,
            'message' => $log->is_fasting ? 'Mabrouk! Semoga puasa Anda diterima 🤲' : 'Semoga bisa berpuasa esok hari 🤲',
        ]);
    }

    /**
     * Proxy API jadwal sholat dari aladhan.com
     */
    public function getPrayerTimes(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $lat = $request->latitude;
        $lng = $request->longitude;
        $today = Carbon::today();

        try {
            // API Aladhan — method 20 = Kemenag RI
            $response = Http::timeout(10)->get('https://api.aladhan.com/v1/timings/' . $today->timestamp, [
                'latitude' => $lat,
                'longitude' => $lng,
                'method' => 20, // Kemenag RI
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $timings = $data['data']['timings'] ?? [];
                $hijri = $data['data']['date']['hijri'] ?? [];
                $readable = $data['data']['date']['readable'] ?? '';

                // Reverse geocode untuk nama kota (simple)
                $locationName = null;
                try {
                    $geoResponse = Http::timeout(5)->get('https://nominatim.openstreetmap.org/reverse', [
                        'format' => 'json',
                        'lat' => $lat,
                        'lon' => $lng,
                        'zoom' => 10,
                        'accept-language' => 'id',
                    ]);
                    if ($geoResponse->successful()) {
                        $geoData = $geoResponse->json();
                        $locationName = $geoData['address']['city']
                            ?? $geoData['address']['town']
                            ?? $geoData['address']['county']
                            ?? $geoData['address']['state']
                            ?? $geoData['display_name'] ?? null;
                    }
                } catch (\Exception $e) {
                    // Fallback — lokasi tidak bisa di-resolve
                }

                return response()->json([
                    'success' => true,
                    'timings' => [
                        'Imsak' => $timings['Imsak'] ?? '',
                        'Fajr' => $timings['Fajr'] ?? '',
                        'Dhuhr' => $timings['Dhuhr'] ?? '',
                        'Asr' => $timings['Asr'] ?? '',
                        'Maghrib' => $timings['Maghrib'] ?? '',
                        'Isha' => $timings['Isha'] ?? '',
                    ],
                    'hijri' => $hijri,
                    'readable' => $readable,
                    'location' => $locationName,
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Gagal mengambil jadwal sholat'], 500);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}

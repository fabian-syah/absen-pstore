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
            ->where('date', $today->toDateString())
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

        // Hitung hari ke-berapa Ramadhan (1 Ramadhan 1447 H ≈ 19 Feb 2026)
        $ramadanStart = Carbon::parse('2026-02-19');
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
            'date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $date = $request->date ? Carbon::parse($request->date) : Carbon::today();

        // Hitung hari Ramadhan
        $ramadanStart = Carbon::parse('2026-02-19');
        $ramadanDay = $ramadanStart->diffInDays($date) + 1;

        // Batasi hanya bisa isi untuk hari ke 1-30, dan tidak bisa isi masa depan (jika perlu)
        if ($ramadanDay < 1 || $ramadanDay > 30) {
            return response()->json(['success' => false, 'message' => 'Hanya bisa mengisi untuk hari Ramadhan'], 400);
        }

        // Opsional: Batasi agar tidak bisa isi masa depan
        if ($date->isAfter(Carbon::today())) {
            return response()->json(['success' => false, 'message' => 'Belum waktunya mengisi untuk hari esok'], 400);
        }

        $log = FastingLog::updateOrCreate(
            [
                'user_id' => $user->id,
                'date' => $date->toDateString(),
            ],
            [
                'is_fasting' => $request->is_fasting,
                'ramadan_day' => $ramadanDay,
                'hijri_year' => 1447,
                'notes' => $request->notes,
            ]
        );

        $totalFasting = FastingLog::where('user_id', $user->id)
            ->where('hijri_year', 1447)
            ->where('is_fasting', true)
            ->count();

        $totalMissed = FastingLog::where('user_id', $user->id)
            ->where('hijri_year', 1447)
            ->where('is_fasting', false)
            ->count();

        return response()->json([
            'success' => true,
            'is_fasting' => $log->is_fasting,
            'total_fasting' => $totalFasting,
            'total_missed' => $totalMissed,
            'ramadan_day' => $ramadanDay,
            'date' => $date->toDateString(),
            'notes' => $log->notes,
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

                // Reverse geocode untuk nama kota (lebih detail: Kota/Kab, Provinsi, Negara)
                $locationName = null;
                try {
                    $geoResponse = Http::timeout(5)->withHeaders([
                        'User-Agent' => 'PStore-Absensi-App/1.0'
                    ])->get('https://nominatim.openstreetmap.org/reverse', [
                                'format' => 'json',
                                'lat' => $lat,
                                'lon' => $lng,
                                'zoom' => 10,
                                'accept-language' => 'id',
                            ]);
                    if ($geoResponse->successful()) {
                        $geoData = $geoResponse->json();
                        $addr = $geoData['address'] ?? [];

                        // Coba ambil City/District, State/Province, and Country
                        $city = $addr['city_district'] ?? $addr['city'] ?? $addr['town'] ?? $addr['suburb'] ?? $addr['county'] ?? null;
                        $province = $addr['state'] ?? $addr['province'] ?? null;
                        $country = $addr['country'] ?? 'Indonesia';

                        $parts = array_filter([$city, $province, $country]);
                        $locationName = implode(', ', $parts);

                        if (empty($locationName)) {
                            $locationName = $geoData['display_name'] ?? 'Indonesia';
                        }
                    }
                } catch (\Exception $e) {
                    // Fallback — lokasi tidak bisa di-resolve
                }

                $timezone = $data['data']['meta']['timezone'] ?? null;

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
                    'timezone' => $timezone,
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Gagal mengambil jadwal sholat'], 500);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Halaman Riwayat/Pelacak Puasa
     */
    public function history()
    {
        $user = Auth::user();
        $hijriYear = 1447;
        $ramadanStart = Carbon::parse('2026-02-19');
        $today = Carbon::today();

        // Ambil SEMUA log puasa user untuk Ramadhan ini
        $logs = FastingLog::where('user_id', $user->id)
            ->where('hijri_year', $hijriYear)
            ->orderBy('ramadan_day', 'asc')
            ->get()
            ->keyBy('ramadan_day');

        // Hitung statistik
        $totalFasting = $logs->where('is_fasting', true)->count();
        $totalMissed = $logs->where('is_fasting', false)->count();

        // Prediksi sisa hari (Ramadan 30 hari)
        $totalDays = 30;
        $remaining = $totalDays - ($totalFasting + $totalMissed);

        // Cari hari ramadhan saat ini
        $currentRamadanDay = $ramadanStart->diffInDays($today) + 1;
        if ($currentRamadanDay < 1)
            $currentRamadanDay = 1;
        if ($currentRamadanDay > 30)
            $currentRamadanDay = 30;

        return view('ramadhan.history', compact(
            'logs',
            'totalFasting',
            'totalMissed',
            'hijriYear',
            'ramadanStart',
            'currentRamadanDay',
            'remaining',
            'totalDays'
        ));
    }
}

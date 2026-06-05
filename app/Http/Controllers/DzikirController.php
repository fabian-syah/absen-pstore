<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Zikir;
use App\Models\ZikirCampaign;
use App\Models\UserZikirFavorite;
use App\Models\UserZikirActivity;
use Illuminate\Support\Facades\Auth;

class DzikirController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get Zikir counts
        $totalZikir = Zikir::count();
        $zikirUmum = Zikir::where('category', 'umum')->count();
        $zikirPagi = Zikir::where('category', 'pagi')->count();
        $zikirPetang = Zikir::where('category', 'petang')->count();
        $zikirSholat = Zikir::where('category', 'sholat')->count();

        // Prayer Time Logic (Kemenag API via MyQuran)
        $cityId = $user->branch ? $user->branch->kemenag_city_id : '1301'; // Default 1301 = Jakarta
        if (!$cityId) $cityId = '1301';

        $date = date('Y/m/d');
        
        $prayerSchedule = \Illuminate\Support\Facades\Cache::remember("prayer_schedule_{$cityId}_{$date}", 86400, function () use ($cityId, $date) {
            try {
                $response = \Illuminate\Support\Facades\Http::get("https://api.myquran.com/v2/sholat/jadwal/{$cityId}/{$date}");
                if ($response->successful() && $response->json('status')) {
                    return $response->json('data.jadwal');
                }
            } catch (\Exception $e) {}
            return null;
        });

        $currentPrayerName = 'Sholat 5 Waktu';
        $currentPrayerTime = null;
        
        if ($prayerSchedule) {
            $now = date('H:i');
            $prayers = [
                'Subuh' => $prayerSchedule['subuh'] ?? null,
                'Dzuhur' => $prayerSchedule['dzuhur'] ?? null,
                'Ashar' => $prayerSchedule['ashar'] ?? null,
                'Maghrib' => $prayerSchedule['maghrib'] ?? null,
                'Isya' => $prayerSchedule['isya'] ?? null,
            ];

            $lastPassed = null;
            $lastTime = null;
            foreach ($prayers as $name => $time) {
                if ($time && $now >= $time) {
                    $lastPassed = $name;
                    $lastTime = $time;
                }
            }
            
            if ($lastPassed) {
                $currentPrayerName = "Sholat " . $lastPassed;
                $currentPrayerTime = $lastTime;
            } else {
                $currentPrayerName = "Sholat Isya";
                $currentPrayerTime = $prayerSchedule['isya'] ?? null;
            }
        }

        // Get User favorites
        $totalFavorites = UserZikirFavorite::where('user_id', $user->id)->count();

        // Get Recent Activity
        $recentActivity = UserZikirActivity::with('zikir')
            ->where('user_id', $user->id)
            ->orderBy('last_read_at', 'desc')
            ->first();

        // Total Collection (how many distinct zikir the user has read)
        $totalCollection = UserZikirActivity::where('user_id', $user->id)->count();

        return view('dzikir.index', compact(
            'totalZikir', 
            'zikirUmum',
            'zikirPagi', 
            'zikirPetang', 
            'zikirSholat',
            'currentPrayerName',
            'currentPrayerTime',
            'totalFavorites', 
            'recentActivity',
            'totalCollection'
        ));
    }

    public function umum()
    {
        $user = Auth::user();

        // Ambil semua zikir kategori umum
        $zikirs = Zikir::where('category', 'umum')->get();
        
        // Ambil progress target zikir user ini
        $activities = UserZikirActivity::where('user_id', $user->id)
                        ->whereIn('zikir_id', $zikirs->pluck('id'))
                        ->get()
                        ->keyBy('zikir_id');
        
        // Ambil data favorite user
        $favorites = UserZikirFavorite::where('user_id', $user->id)
                        ->whereIn('zikir_id', $zikirs->pluck('id'))
                        ->pluck('zikir_id')
                        ->toArray();

        // Ambil active campaigns untuk carousel
        $campaigns = ZikirCampaign::where('is_active', true)
                        ->orderBy('created_at', 'desc')
                        ->get();

        return view('dzikir.umum', compact('zikirs', 'activities', 'favorites', 'campaigns'));
    }
}

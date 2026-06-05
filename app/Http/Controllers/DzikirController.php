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

    public function play($category, $id = null)
    {
        $user = Auth::user();

        // Ambil semua zikir berdasarkan kategori (contoh: umum)
        $zikirs = Zikir::where('category', $category)->orderBy('id')->get();
        
        if ($zikirs->isEmpty()) {
            return redirect()->route('dzikir.index')->with('error', 'Kategori tidak ditemukan');
        }

        // Tentukan initial index berdasarkan ID (jika ada)
        $initialIndex = 0;
        if ($id) {
            $findIndex = $zikirs->search(function ($zikir) use ($id) {
                return $zikir->id == $id;
            });
            if ($findIndex !== false) {
                $initialIndex = $findIndex;
            }
        }

        // Ambil data progres user untuk zikir-zikir tersebut
        $activities = UserZikirActivity::where('user_id', $user->id)
                        ->whereIn('zikir_id', $zikirs->pluck('id'))
                        ->get()
                        ->each(function($activity) {
                            if ($activity->last_read_at && !$activity->last_read_at->isToday()) {
                                $activity->total_count = 0;
                                $activity->save();
                            }
                        })
                        ->keyBy('zikir_id');

        // Ambil data favorite user
        $favorites = UserZikirFavorite::where('user_id', $user->id)
                        ->whereIn('zikir_id', $zikirs->pluck('id'))
                        ->pluck('zikir_id')
                        ->toArray();

        return view('dzikir.play', compact('zikirs', 'activities', 'favorites', 'category', 'initialIndex'));
    }

    public function saveProgress(Request $request)
    {
        $request->validate([
            'zikir_id' => 'required|exists:zikirs,id',
            'count' => 'required|integer|min:0'
        ]);

        $user = Auth::user();
        
        $activity = UserZikirActivity::firstOrNew([
            'user_id' => $user->id, 
            'zikir_id' => $request->zikir_id
        ]);

        // Hitung selisih untuk all_time_count
        $diff = $request->count - ($activity->total_count ?? 0);
        if ($diff > 0) {
            $activity->all_time_count = ($activity->all_time_count ?? 0) + $diff;
        }

        $activity->total_count = $request->count;
        $activity->last_read_at = now();
        $activity->save();

        return response()->json(['success' => true, 'activity' => $activity]);
    }
    public function updateTarget(Request $request)
    {
        $request->validate([
            'zikir_id' => 'required|exists:zikirs,id',
            'target_count' => 'required|integer|min:1'
        ]);

        $user = Auth::user();
        
        $activity = UserZikirActivity::updateOrCreate(
            ['user_id' => $user->id, 'zikir_id' => $request->zikir_id],
            ['target_count' => $request->target_count]
        );

        return response()->json(['success' => true, 'activity' => $activity]);
    }

    public function resetProgress(Request $request)
    {
        $request->validate([
            'zikir_id' => 'required|exists:zikirs,id'
        ]);

        $user = Auth::user();
        
        $activity = UserZikirActivity::where('user_id', $user->id)
            ->where('zikir_id', $request->zikir_id)
            ->first();

        if ($activity) {
            $activity->update(['total_count' => 0]);
        } else {
            $activity = UserZikirActivity::create([
                'user_id' => $user->id,
                'zikir_id' => $request->zikir_id,
                'total_count' => 0
            ]);
        }

        return response()->json(['success' => true, 'activity' => $activity]);
    }
}

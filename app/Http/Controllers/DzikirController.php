<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Zikir;
use App\Models\ZikirCampaign;
use App\Models\UserZikirFavorite;
use App\Models\UserZikirActivity;
use App\Models\UserZikirLog;
use Illuminate\Support\Facades\Auth;

class DzikirController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get Zikir counts
        $totalZikir = Zikir::count();
        $zikirUmum = Zikir::whereJsonContains('category', 'umum')->count();
        $zikirPagi = Zikir::whereJsonContains('category', 'pagi')->count();
        $zikirPetang = Zikir::whereJsonContains('category', 'petang')->count();
        $zikirSholat = Zikir::whereJsonContains('category', 'sholat')->count();

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

    public function category($category)
    {
        $user = Auth::user();

        // Validasi kategori yang dibolehkan (optional, tapi baik untuk keamanan)
        if (!in_array($category, ['umum', 'pagi', 'petang', 'sholat'])) {
            return redirect()->route('dzikir.index')->with('error', 'Kategori tidak valid.');
        }

        // Ambil semua zikir kategori yang diminta
        $zikirs = Zikir::whereJsonContains('category', $category)->get();
        
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

        $categoryName = ucfirst($category);
        if ($category === 'sholat') {
            $categoryName = 'Sholat 5 Waktu';
        }

        return view('dzikir.category', compact('zikirs', 'activities', 'favorites', 'campaigns', 'category', 'categoryName'));
    }

    public function play($category, $id = null)
    {
        $user = Auth::user();

        // Ambil semua zikir berdasarkan kategori (contoh: umum)
        $zikirs = Zikir::whereJsonContains('category', $category)->orderBy('id')->get();
        
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

        $increment = (int) $request->count;

        $activity->total_count = ($activity->total_count ?? 0) + $increment;
        $activity->all_time_count = ($activity->all_time_count ?? 0) + $increment;

        $activity->last_read_at = now();
        $activity->save();

        // Save to UserZikirLog for statistics
        $log = UserZikirLog::firstOrNew([
            'user_id' => $user->id,
            'zikir_id' => $request->zikir_id,
            'read_date' => now()->toDateString()
        ]);
        $log->count = ($log->count ?? 0) + $increment;
        $log->save();

        // Increment related active campaigns
        $campaigns = ZikirCampaign::where('zikir_id', $request->zikir_id)
                        ->where('is_active', true)
                        ->get();
        foreach ($campaigns as $campaign) {
            // Check if target is reached based on the dynamic current_count
            if ($campaign->current_count >= $campaign->target) {
                $campaign->is_active = false;
                $campaign->save();
            }
        }

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

    public function favorites()
    {
        $user = Auth::user();

        // Ambil semua zikir yang difavoritkan oleh user
        $favoriteIds = UserZikirFavorite::where('user_id', $user->id)->pluck('zikir_id');
        $zikirs = Zikir::whereIn('id', $favoriteIds)->get();

        // Ambil data progres user untuk zikir-zikir tersebut
        $activities = UserZikirActivity::where('user_id', $user->id)
                        ->whereIn('zikir_id', $favoriteIds)
                        ->get()
                        ->keyBy('zikir_id');

        $favorites = $favoriteIds->toArray();

        // Tidak ada campaign khusus di halaman favorit untuk saat ini
        $campaigns = collect([]);
        $category = 'favorit';
        $categoryName = 'Favorit';

        return view('dzikir.favorites', compact('zikirs', 'activities', 'favorites', 'campaigns', 'category', 'categoryName'));
    }

    public function toggleFavorite(Request $request)
    {
        $request->validate([
            'zikir_id' => 'required|exists:zikirs,id'
        ]);

        $user = Auth::user();
        $zikirId = $request->zikir_id;

        $favorite = UserZikirFavorite::where('user_id', $user->id)
                                    ->where('zikir_id', $zikirId)
                                    ->first();

        if ($favorite) {
            $favorite->delete();
            $isFavorite = false;
        } else {
            UserZikirFavorite::create([
                'user_id' => $user->id,
                'zikir_id' => $zikirId
            ]);
            $isFavorite = true;
        }

        return response()->json([
            'success' => true, 
            'is_favorite' => $isFavorite,
            'message' => $isFavorite ? 'Ditambahkan ke favorit' : 'Dihapus dari favorit'
        ]);
    }
}

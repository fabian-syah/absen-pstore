<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Zikir;
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
        $zikirPagi = Zikir::where('category', 'pagi')->count();
        $zikirPetang = Zikir::where('category', 'petang')->count();

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
            'zikirPagi', 
            'zikirPetang', 
            'totalFavorites', 
            'recentActivity',
            'totalCollection'
        ));
    }
}

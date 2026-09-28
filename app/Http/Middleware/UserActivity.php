<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class UserActivity
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            // Validasi batas waktu ingat sesi (maksimal 7 hari / 1 minggu)
            $isExpired = false;
            if (session()->has('remember_login_expires_at')) {
                if (now()->timestamp > session('remember_login_expires_at')) {
                    $isExpired = true;
                }
            } elseif (Auth::viaRemember()) {
                $lastLogin = Auth::user()->last_login_at ? \Carbon\Carbon::parse(Auth::user()->last_login_at)->timestamp : null;
                if ($lastLogin && (now()->timestamp - $lastLogin > (7 * 86400))) {
                    $isExpired = true;
                }
            }

            if ($isExpired) {
                $userId = Auth::id();
                Cache::forget('user-is-online-' . $userId);
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->withErrors([
                    'login_id' => 'Sesi login 1 minggu Anda telah berakhir demi keamanan. Silakan masuk kembali.'
                ]);
            }

            // Simpan status online di cache selama 60 detik
            $expiresAt = now()->addSeconds(10);
            Cache::put('user-is-online-' . Auth::user()->id, true, $expiresAt);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login_id' => 'required|string',
            'password' => 'required|string',
        ]);

        $start = microtime(true);

        // Blokir IP jika > 20 percobaan dalam 5 menit (bukan hanya per user)
        $ipKey = 'login-attempt-ip:' . $request->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($ipKey, 20)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($ipKey);
            return back()->withErrors([
                'login_id' => "Terlalu banyak percobaan dari IP Anda. Silakan coba lagi dalam $seconds detik."
            ])->withInput();
        }

        // Key untuk throttling (kombinasi login_id dan IP)
        $throttleKey = strtolower($request->login_id) . '|' . $request->ip();

        // Cek apakah user sedang diblokir sementara
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'login_id' => "Terlalu banyak percobaan login. Silakan coba lagi dalam $seconds detik."
            ])->withInput();
        }

        // Case-insensitive login_id lookup
        $user = User::whereRaw('LOWER(login_id) = ?', [strtolower($request->login_id)])->first();

        // Validasi User: Apakah tidak ada, password salah, atau tidak aktif
        if (!$user || !Hash::check($request->password, $user->password) || $user->is_active == 0) {
            // Catat percobaan gagal
            \Illuminate\Support\Facades\RateLimiter::hit($ipKey, 300); // Decay 300 detik (5 menit)
            \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 60); // Decay 60 detik

            // Cegah timing attack
            $elapsed = microtime(true) - $start;
            if ($elapsed < 1.0) {
                usleep((int) ((1.0 - $elapsed) * 1000000));
            }

            // Universal error message (Zero Enumeration)
            return back()->withErrors([
                'login_id' => 'ID Login atau Password salah. Pastikan kredensial benar.'
            ])->withInput();
        }

        // Bersihkan percobaan gagal jika berhasil
        \Illuminate\Support\Facades\RateLimiter::clear($ipKey);
        \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);

        // --- LOGIKA BARU: Update Last Login ---
        $user->update([
            'last_login_at' => now()
        ]);
        // --------------------------------------

        Auth::login($user, $request->remember);
        $request->session()->regenerate();
        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            // --- TAMBAHKAN BARIS INI ---
            // Hapus status online dari cache seketika saat logout
            \Illuminate\Support\Facades\Cache::forget('user-is-online-' . $user->id);

            $user->fcm_token = null;
            $user->save();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}

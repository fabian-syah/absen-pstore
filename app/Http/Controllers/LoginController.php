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

        // Case-insensitive login_id lookup
        $user = User::whereRaw('LOWER(login_id) = ?', [strtolower($request->login_id)])->first();

        // Case-insensitive password check
        if ($user && Hash::check($request->password, $user->password)) {

            if ($user->is_active == 0) {
                return back()->withErrors(['login_id' => 'Akun Anda dinonaktifkan.'])->withInput();
            }

            // --- LOGIKA BARU: Update Last Login ---
            $user->update([
                'last_login_at' => now()
            ]);
            // --------------------------------------

            Auth::login($user, $request->remember);
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        return back()->withErrors(['login_id' => 'ID Login atau Password salah.'])->withInput();
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

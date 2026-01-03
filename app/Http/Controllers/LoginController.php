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

    $user = User::where('login_id', $request->login_id)->first();

    if ($user && (Hash::check($request->password, $user->password))) {

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
        
        // [TAMBAHAN] Hapus FCM Token di database saat logout
        // Agar saat login nanti dia memaksa simpan ulang
        if ($user) {
            $user->fcm_token = null;
            $user->save();
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}

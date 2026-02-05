<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class FingerprintAuthController extends Controller
{
    public function index()
    {
        return view('auth.fingerprint');
    }

    public function authenticate(Request $request)
    {
        // TARGET: User ID 47 (bianajah5)
        $userId = 47;

        $user = User::find($userId);

        if (!$user) {
            return back()->with('error', 'User ID 47 tidak ditemukan.');
        }

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Login Fingerprint Berhasil! Selamat datang, ' . $user->name);
    }
}

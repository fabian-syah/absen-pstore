<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Cek apakah user sudah login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Debug: Log user role dan roles yang diizinkan
        \Illuminate\Support\Facades\Log::info('CheckRole Middleware', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'allowed_roles' => $roles
        ]);

        // Cek apakah role user termasuk dalam roles yang diizinkan (Case Insensitive)
        if (in_array(strtolower($user->role), array_map('strtolower', $roles))) {
            return $next($request);
        }

        // Jika role tidak diizinkan
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        
        abort(403, 'Akses ditolak. Role tidak memenuhi.');
    }
}
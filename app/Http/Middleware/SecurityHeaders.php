<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // 1. HSTS (Strict-Transport-Security)
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

        // 2. X-Frame-Options (Mencegah Clickjacking)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // 3. X-Content-Type-Options (Mencegah MIME Sniffing)
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // 4. Referrer-Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // 5. Permissions-Policy
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self)');

        // 6. X-XSS-Protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // 7. Menghapus X-Powered-By (Opsional lewat PHP, tapi lebih baik di sini juga)
        header_remove('X-Powered-By');

        return $response;
    }
}

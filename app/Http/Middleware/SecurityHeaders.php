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

        // 2. Content Security Policy (CSP) - Level Ultimate
        // Kebijakan ini mengizinkan script dari domain sendiri dan CDN terpercaya saja.
        $csp = "default-src 'self'; ";
        $csp .= "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://maps.googleapis.com; ";
        $csp .= "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; ";
        $csp .= "img-src 'self' data: https:; ";
        $csp .= "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net; ";
        $csp .= "connect-src 'self' https://maps.googleapis.com; ";
        $csp .= "frame-src 'self'; ";
        $csp .= "object-src 'none'; "; // Mematikan plugin seperti Flash
        $csp .= "base-uri 'self'; ";
        $csp .= "form-action 'self'; ";
        $csp .= "upgrade-insecure-requests;";
        $response->headers->set('Content-Security-Policy', $csp);

        // 3. Header Cross-Origin (Mencegah Side-Channel Attacks)
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Embedder-Policy', 'require-corp');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');

        // 4. Header Standar Lainnya
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self)');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        return $response;
    }
}

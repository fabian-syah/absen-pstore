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
        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);
 
        // 1. HSTS (Strict-Transport-Security)
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
 
        // 2. Clickjacking (X-Frame-Options)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
 
        // 3. Content-Type Options (nosniff)
        $response->headers->set('X-Content-Type-Options', 'nosniff');
 
        // 4. Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
 
        // 5. Permissions Policy
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self), payment=()');
 
        // 6. XSS Protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');
 
        // 7. Content Security Policy (CSP)
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://maps.googleapis.com; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; " .
               "font-src 'self' https://fonts.gstatic.com data:; " .
               "img-src 'self' data: https:; " .
               "connect-src 'self' https://maps.googleapis.com; " .
               "object-src 'none'; " .
               "frame-ancestors 'self'; " .
               "base-uri 'self'; " .
               "form-action 'self'; " .
               "upgrade-insecure-requests";
        $response->headers->set('Content-Security-Policy', $csp);

        // 8. Cross-Origin Isolation (Paranoid Mode)
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Embedder-Policy', 'require-corp');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');

        // 9. Remove Information Leaks
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');
        if (function_exists('header_remove')) {
            header_remove('X-Powered-By');
        }

        return $response;
    }
}

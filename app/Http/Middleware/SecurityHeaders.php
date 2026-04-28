<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * LESSON: Security Headers Middleware
 *
 * These HTTP headers are invisible to users but tell browsers
 * how to behave securely. They protect against common attacks:
 *
 * X-Frame-Options        prevents Clickjacking (your site in an iframe)
 * X-Content-Type-Options  prevents MIME sniffing attacks
 * Referrer-Policy        controls what URL info is sent to other sites
 * Permissions-Policy     disables browser features you don't use
 * Content-Security-Policy  controls which resources can load
 *
 * Test your headers at: https://securityheaders.com
 * Industry standard is an A or A+ rating.
 *
 * Register in bootstrap/app.php:
 *   ->withMiddleware(function (Middleware $m) {
 *       $m->append(SecurityHeaders::class);
 *   })
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        /**
         * Content Security Policy — controls allowed resource origins.
         * LESSON: We allow:
         *   - fonts.googleapis.com  Google Fonts CSS
         *   - fonts.gstatic.com    Google Fonts files
         *   - js.stripe.com        Stripe payment JS (required!)
         *   - cdnjs.cloudflare.com  Three.js CDN
         *   'unsafe-inline'        needed for Alpine.js x-data inline scripts
         *   'unsafe-eval'          needed for Vite dev mode (remove in prod)
         */
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://js.stripe.com https://cdnjs.cloudflare.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com data:",
            "img-src 'self' data: https: blob:",
            "connect-src 'self' https://api.stripe.com",
            "frame-src https://js.stripe.com https://hooks.stripe.com",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
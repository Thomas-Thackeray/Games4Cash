<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent MIME-type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Deny framing from other origins
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Limit referrer information sent to third parties
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Disable browser features the site doesn't use
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');

        // HSTS — only safe to send over HTTPS
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Content Security Policy
        // - unsafe-inline required for Blade inline <script> blocks and style attributes
        // - fonts.googleapis.com / fonts.gstatic.com for Google Fonts
        // - youtube.com for embedded game trailers
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline'",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com",
            "img-src 'self' data:",
            "frame-src https://www.youtube.com https://www.youtube-nocookie.com",
            "connect-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        // Remove headers that reveal implementation details
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}

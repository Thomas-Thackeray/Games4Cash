<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackPageView
{
    // Path prefixes that should never be tracked
    private const SKIP_PREFIXES = [
        'admin',
        'img/',
        'up',
    ];

    // User-agent substrings that indicate bots/crawlers
    private const BOT_STRINGS = [
        'bot', 'crawl', 'spider', 'slurp', 'mediapartners',
        'facebookexternalhit', 'twitterbot', 'linkedinbot',
        'whatsapp', 'googlebot', 'bingbot', 'yandex',
        'semrush', 'ahref', 'mj12bot', 'dotbot',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track successful GET requests
        if (! $request->isMethod('GET') || $response->getStatusCode() !== 200) {
            return $response;
        }

        $path = ltrim($request->path(), '/');

        // Skip admin, asset proxy, and health routes
        foreach (self::SKIP_PREFIXES as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return $response;
            }
        }

        // Skip obvious bots
        $ua = strtolower($request->userAgent() ?? '');
        foreach (self::BOT_STRINGS as $bot) {
            if (str_contains($ua, $bot)) {
                return $response;
            }
        }

        try {
            PageView::create([
                'session_id' => session()->getId(),
                'ip_hash'    => hash('sha256', $request->ip()),
                'path'       => '/' . $path,
                'referrer'   => $this->cleanReferrer($request->header('referer')),
            ]);
        } catch (\Throwable) {
            // Table may not exist yet — degrade silently
        }

        return $response;
    }

    private function cleanReferrer(?string $referrer): ?string
    {
        if (! $referrer) {
            return null;
        }

        // Strip own domain so we only record external referrers
        $appUrl = rtrim(config('app.url'), '/');
        if (str_starts_with($referrer, $appUrl)) {
            return null;
        }

        // Keep only scheme + host (drop path/query for privacy)
        $parts = parse_url($referrer);
        if (empty($parts['host'])) {
            return null;
        }

        return ($parts['scheme'] ?? 'https') . '://' . $parts['host'];
    }
}

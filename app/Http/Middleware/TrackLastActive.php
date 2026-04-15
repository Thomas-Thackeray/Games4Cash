<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackLastActive
{
    // Only write to DB at most once every 5 minutes per session
    private const TTL = 300;

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $key = 'last_active_updated';

            if (! $request->session()->has($key) ||
                (time() - $request->session()->get($key)) > self::TTL) {
                $request->user()->update(['last_active_at' => now()]);
                $request->session()->put($key, time());
            }
        }

        return $next($request);
    }
}

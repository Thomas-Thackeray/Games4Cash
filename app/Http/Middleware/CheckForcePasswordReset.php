<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckForcePasswordReset
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() &&
            $request->user()->force_password_reset &&
            ! $request->routeIs('password.force-reset*') &&
            ! $request->routeIs('logout')
        ) {
            return redirect()->route('password.force-reset');
        }

        return $next($request);
    }
}

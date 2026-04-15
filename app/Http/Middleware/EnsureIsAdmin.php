<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isAdmin()) {
            $identity = $user
                ? "user #{$user->id} ({$user->username})"
                : 'unauthenticated visitor';

            ActivityLogger::security(
                "Unauthorised admin access attempt by {$identity} — {$request->method()} {$request->path()}",
                $request
            );

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('home')
                ->with('flash_error', 'You do not have permission to access that area.');
        }

        return $next($request);
    }
}

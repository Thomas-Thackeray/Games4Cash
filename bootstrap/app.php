<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin'        => \App\Http\Middleware\EnsureIsAdmin::class,
            'track.active' => \App\Http\Middleware\TrackLastActive::class,
            'force.reset'  => \App\Http\Middleware\CheckForcePasswordReset::class,
        ]);

        // Track public page views for analytics
        $middleware->appendToGroup('web', \App\Http\Middleware\TrackPageView::class);

        // Security headers on every web response
        $middleware->appendToGroup('web', \App\Http\Middleware\SecurityHeaders::class);

        // Log suspicious inputs (SQLi, XSS, path traversal, etc.)
        $middleware->appendToGroup('web', \App\Http\Middleware\DetectSuspiciousInput::class);

        // Run force-reset check on every web request so no page is bypassed
        $middleware->appendToGroup('web', \App\Http\Middleware\CheckForcePasswordReset::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

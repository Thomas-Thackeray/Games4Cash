<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoApiTransport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Harden PHP native session cookie settings.
        // Laravel uses its own session, but this covers any native session_start()
        // call from third-party packages and ensures settings survive server changes.
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', '1');

        // Only mark cookies Secure when the connection is actually HTTPS.
        // Set SESSION_SECURE_COOKIE=true in your production .env.
        if (env('SESSION_SECURE_COOKIE', false)) {
            ini_set('session.cookie_secure', '1');
        }

        // Brevo HTTP API mail transport (bypasses SMTP port restrictions)
        Mail::extend('brevo', function (array $config = []) {
            return new BrevoApiTransport($config['key'] ?? config('services.brevo.key'));
        });

        // Rate limiters for auth routes
        RateLimiter::for('login', function (Request $request) {
            // 5 attempts per minute keyed by IP + username/email combo
            return Limit::perMinute(5)
                ->by(strtolower((string) $request->input('username')) . '|' . $request->ip());
        });

        RateLimiter::for('register', function (Request $request) {
            // 3 registrations per hour per IP
            return Limit::perHour(3)->by($request->ip());
        });

        RateLimiter::for('password-reset', function (Request $request) {
            // 5 reset link requests per 15 minutes per IP
            return Limit::perMinutes(15, 5)->by($request->ip());
        });
    }
}

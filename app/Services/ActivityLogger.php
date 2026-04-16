<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function search(string $description, Request $request): void
    {
        self::write('search', $description, $request);
    }

    public static function login(string $description, Request $request): void
    {
        self::write('login', $description, $request);
    }

    public static function filter(string $description, Request $request): void
    {
        self::write('filter', $description, $request);
    }

    public static function quote(string $description, Request $request): void
    {
        self::write('quote', $description, $request);
    }

    public static function security(string $description, Request $request): void
    {
        self::write('security', $description, $request);
    }

    private static function write(string $type, string $description, Request $request): void
    {
        ActivityLog::create([
            'user_id'     => Auth::id(),
            'type'        => $type,
            'description' => $description,
            'ip_address'  => $request->ip(),
        ]);
    }
}

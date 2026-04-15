<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ImageProxyController extends Controller
{
    private const CACHE_DAYS = 30;
    private const ORIGIN     = 'https://images.igdb.com/igdb/image/upload';

    public function show(string $encoded): Response
    {
        // Decode base64url → image path  e.g. /t_cover_big/cobkt6.jpg
        $path = base64_decode(strtr($encoded, '-_', '+/'));

        // Strict allow-list: only valid IGDB image paths
        if (! preg_match('#^/t_[a-z0-9_]+/[a-z0-9]+\.jpg$#i', $path)) {
            abort(404);
        }

        $cacheFile = 'img-cache/' . md5($path) . '.jpg';

        if (Storage::disk('local')->exists($cacheFile)) {
            $data = Storage::disk('local')->get($cacheFile);
        } else {
            $response = Http::timeout(10)->get(self::ORIGIN . $path);

            if (! $response->successful()) {
                abort(404);
            }

            $data = $response->body();
            Storage::disk('local')->put($cacheFile, $data);
        }

        return response($data, 200)
            ->header('Content-Type', 'image/jpeg')
            ->header('Cache-Control', 'public, max-age=' . (self::CACHE_DAYS * 86400))
            ->header('X-Content-Type-Options', 'nosniff');
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogger;
use App\Services\IgdbService;
use App\Services\PriceSyncService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlatformController extends Controller
{
    public function show(Request $request, int $id, string $name): View
    {
        $page   = max(1, (int) $request->input('page', 1));
        $limit  = 24;
        $offset = ($page - 1) * $limit;

        $games        = [];
        $platform     = null;
        $error        = null;
        $platformName = html_entity_decode($name, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Find matching config entry by ID
        $platformIcon   = '🎮';
        $platformSlug   = $name; // fallback to whatever came in the URL
        $platformConfig = [];
        foreach (config('igdb.platforms') as $pName => $data) {
            if ($data['id'] === $id) {
                $platformIcon   = $data['icon'];
                $platformSlug   = $data['slug'] ?? $pName;
                $platformConfig = $data;
                break;
            }
        }

        if ($page === 1 && !$request->attributes->get('security_logged')) {
            ActivityLogger::filter('Browsed platform: ' . $platformName, $request);
        }

        try {
            $igdb     = new IgdbService();
            $games    = $igdb->getGamesByPlatform($id, $limit, $offset);
            $platform = $igdb->getPlatform($id);
        } catch (\RuntimeException $e) {
            $error = $e->getMessage();
        }

        PriceSyncService::ensureForGames($games);

        return view('platform', compact(
            'games', 'platform', 'error', 'id',
            'platformName', 'platformIcon', 'platformConfig', 'platformSlug', 'page', 'limit'
        ));
    }
}

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

        // Find matching icon from config
        $platformIcon = '🎮';
        foreach (config('igdb.platforms') as $pName => $data) {
            if ($data['id'] === $id) {
                $platformIcon = $data['icon'];
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
            'platformName', 'platformIcon', 'page', 'limit'
        ));
    }
}

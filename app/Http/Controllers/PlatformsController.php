<?php

namespace App\Http\Controllers;

use App\Models\GamePrice;
use App\Models\HiddenGame;
use App\Models\NoPriceReview;
use App\Services\IgdbService;
use App\Services\PriceSyncService;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PlatformsController extends Controller
{
    public function index(): View
    {
        $platformsConfig = config('igdb.platforms');
        $igdb            = new IgdbService();
        $platformGames   = [];

        foreach ($platformsConfig as $pName => $pData) {
            $cacheKey = 'platforms_overview_' . $pData['id'];

            $games = Cache::remember($cacheKey, now()->addHours(6), function () use ($igdb, $pData) {
                try {
                    return $igdb->getGamesByPlatform($pData['id'], 6);
                } catch (\Throwable) {
                    return [];
                }
            });

            $games = GamePrice::stripFreeGames($games);
            $games = HiddenGame::strip($games);
            $games = NoPriceReview::strip($games);
            PriceSyncService::ensureForGames($games);

            // Keep only games that have a price after stripping
            $priced = array_filter($games, function ($game) {
                $gp = GamePrice::where('igdb_game_id', $game['id'])->first();
                if (! $gp) return false;
                $pricing = $gp->getComputedPrice(array_column($game['franchises'] ?? [], 'name'), $game['name'] ?? '');
                return $pricing && ! $pricing['is_free'];
            });

            $platformGames[$pName] = array_values(array_slice($priced, 0, 5));
        }

        return view('platforms', compact('platformsConfig', 'platformGames'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\CustomGame;
use App\Models\GamePrice;
use App\Models\HiddenGame;
use App\Models\NoPriceReview;
use App\Services\IgdbService;
use App\Services\PriceSyncService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PlatformSellController extends Controller
{
    public function show(string $slug): View
    {
        $platformId = $this->resolveSlug($slug);

        if (!$platformId) {
            abort(404);
        }

        $allPlatforms   = config('igdb.all_platforms', []);
        $platformName   = $allPlatforms[$platformId] ?? 'Unknown';
        $platformConfig = collect(config('igdb.platforms', []))->first(fn($p) => $p['id'] === $platformId);

        // Fetch top-priced games for this platform using IGDB
        $games = Cache::remember("sell_platform_{$platformId}", now()->addMinutes(20), function () use ($platformId) {
            try {
                $igdb = new IgdbService();
                return $igdb->getGamesByPlatform($platformId, 24, 0);
            } catch (\Throwable) {
                return [];
            }
        });

        $games = GamePrice::stripFreeGames($games);
        $games = HiddenGame::strip($games);
        $games = NoPriceReview::strip($games);
        PriceSyncService::ensureForGames($games);

        // Custom games with a price for this platform
        $customGames = CustomGame::where('published', true)->get()
            ->filter(fn($g) => $g->priceForPlatform($platformId) !== null)
            ->values();

        return view('sell-platform', compact(
            'slug', 'platformId', 'platformName', 'platformConfig',
            'games', 'customGames'
        ));
    }

    private function resolveSlug(string $slug): ?int
    {
        $map = [];
        foreach (config('igdb.all_platforms', []) as $id => $name) {
            $map[Str::slug($name)] = $id;
        }
        // Short aliases
        $map['ps5']         = 167;
        $map['ps4']         = 48;
        $map['ps3']         = 9;
        $map['ps2']         = 8;
        $map['xbox-series'] = 169;

        return $map[$slug] ?? null;
    }
}

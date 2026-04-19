<?php

namespace App\Http\Controllers;

use App\Models\CustomGame;
use App\Models\GamePrice;
use App\Services\IgdbService;
use App\Services\PricingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GameController extends Controller
{
    /**
     * Legacy numeric-ID route: /game/1942
     * Redirects permanently to the slug URL when known, renders directly otherwise.
     */
    public function show(int $id): RedirectResponse|View
    {
        // Fast path: slug already stored in DB
        $slug = GamePrice::where('igdb_game_id', $id)->value('slug');
        if ($slug) {
            return redirect()->route('game.show', ['slug' => $slug], 301);
        }

        // Fetch from IGDB to get the slug, then redirect
        $igdb = new IgdbService();
        try {
            $game = $igdb->getGame($id);
        } catch (\RuntimeException) {
            $game = null;
        }

        if ($game && !empty($game['slug'])) {
            return redirect()->route('game.show', ['slug' => $game['slug']], 301);
        }

        // Slug not available — render the page in-place (rare/fallback)
        return $this->render($id, $game);
    }

    /**
     * Canonical slug route: /game/elden-ring
     */
    public function showBySlug(string $slug): View
    {
        $igdb = new IgdbService();
        $game = null;
        $id   = null;

        // Try DB first for known slug → IGDB ID mapping
        $gp = GamePrice::where('slug', $slug)->first();
        if ($gp) {
            $id = $gp->igdb_game_id;
            try {
                $game = $igdb->getGame($id);
            } catch (\RuntimeException) {}
        }

        // Fallback: query IGDB directly by slug
        if (!$game) {
            try {
                $game = $igdb->getGameBySlug($slug);
            } catch (\RuntimeException) {}

            // Fall back to a published custom game with this slug
            if (!$game) {
                $customGame = CustomGame::where('slug', $slug)->where('published', true)->first();
                if ($customGame) {
                    return $this->renderCustomGame($customGame);
                }
                abort(404);
            }
            $id = $game['id'];
        }

        return $this->render($id, $game);
    }

    // ── Shared rendering logic ────────────────────────────────────────────────

    private function render(int $id, ?array $game): View
    {
        $error   = null;
        $pricing = null;

        if (!$game) {
            $error = 'Could not load game data.';
        }

        $steamAppId     = null;
        $franchiseNames = [];
        $gamePrice      = null;

        if ($game) {
            $franchiseNames = array_values(array_filter(
                array_column($game['franchises'] ?? [], 'name')
            ));
        }

        if ($game) {
            try {
                $steamAppId = (new IgdbService())->getSteamAppId($id);
                if ($steamAppId) {
                    $releaseTimestamp = $game['first_release_date'] ?? null;

                    PricingService::getForSteamApp($steamAppId, $releaseTimestamp);

                    $raw = PricingService::getRawCached($steamAppId);
                    if ($raw !== null) {
                        $platformIds = array_values(array_filter(
                            array_column($game['platforms'] ?? [], 'id')
                        ));
                        $isBundle = ($game['category'] ?? 0) === 3;
                        GamePrice::record(
                            $id,
                            $steamAppId,
                            $releaseTimestamp,
                            $raw['is_free'] ?? false,
                            $raw['steam_gbp'] ?? null,
                            $raw['cheapshark_usd'] ?? null,
                            $platformIds,
                            $franchiseNames,
                            $isBundle,
                            $game['slug'] ?? null,
                        );
                    }
                }
            } catch (\Throwable) {
                // Pricing is best-effort — never break the page
            }

            // Also store slug even when no Steam ID exists
            if (!empty($game['slug'])) {
                GamePrice::where('igdb_game_id', $id)
                    ->whereNull('slug')
                    ->update(['slug' => $game['slug']]);
            }
        }

        if ($game) {
            $gamePrice = GamePrice::where('igdb_game_id', $id)->first();
            if ($gamePrice) {
                $pricing = $gamePrice->getComputedPrice($franchiseNames, $game['name'] ?? null);
            }
        }

        $inWishlist   = false;
        $inCashBasket = false;
        if (auth()->check() && $game) {
            $user         = auth()->user();
            $inWishlist   = $user->wishlistItems()->where('igdb_game_id', $id)->exists();
            $inCashBasket = $user->cashBasketItems()->where('igdb_game_id', $id)->exists();
        }

        if ($game) {
            $viewed = session('recently_viewed', []);
            $viewed = array_values(array_filter($viewed, fn($v) => $v !== $id));
            array_unshift($viewed, $id);
            session(['recently_viewed' => array_slice($viewed, 0, 20)]);
        }

        return view('game', compact('game', 'error', 'pricing', 'steamAppId', 'inWishlist', 'inCashBasket', 'gamePrice', 'franchiseNames'));
    }

    private function renderCustomGame(CustomGame $game): View
    {
        $platforms   = config('igdb.all_platforms', []);
        $pricingRows = [];

        foreach ($platforms as $platformId => $platformName) {
            $price = $game->priceForPlatform($platformId);
            if ($price !== null) {
                $pricingRows[] = [
                    'platform_id'   => $platformId,
                    'platform_name' => $platformName,
                    'display_price' => '£' . number_format($price, 2),
                    'price_numeric' => $price,
                ];
            }
        }

        return view('custom-game', compact('game', 'pricingRows', 'platforms'));
    }
}

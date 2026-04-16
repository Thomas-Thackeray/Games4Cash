<?php

namespace App\Http\Controllers;

use App\Models\GamePrice;
use App\Models\Setting;
use App\Services\IgdbService;
use App\Services\PricingService;
use Illuminate\View\View;

class GameController extends Controller
{
    public function show(int $id): View
    {
        $igdb    = new IgdbService();
        $game    = null;
        $error   = null;
        $pricing = null;

        try {
            $game = $igdb->getGame($id);
        } catch (\RuntimeException $e) {
            $error = $e->getMessage();
        }

        if (! $game && ! $error) {
            abort(404);
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
                $steamAppId = $igdb->getSteamAppId($id);
                if ($steamAppId) {
                    $releaseTimestamp = $game['first_release_date'] ?? null;

                    // Populate the raw-price cache (6-hour TTL)
                    PricingService::getForSteamApp($steamAppId, $releaseTimestamp);

                    // Persist raw prices so game cards can display them without API calls
                    $raw = PricingService::getRawCached($steamAppId);
                    if ($raw !== null) {
                        $platformIds = array_values(array_filter(
                            array_column($game['platforms'] ?? [], 'id')
                        ));
                        GamePrice::record(
                            $id,
                            $steamAppId,
                            $releaseTimestamp,
                            $raw['is_free'] ?? false,
                            $raw['steam_gbp'] ?? null,
                            $raw['cheapshark_usd'] ?? null,
                            $platformIds,
                            $franchiseNames,
                        );
                    }
                }
            } catch (\Throwable) {
                // Pricing is best-effort — never break the page
            }
        }

        // Always compute display pricing from the DB record so the game detail page,
        // game cards, and the cash basket all use the same formula and data.
        if ($game) {
            $gamePrice = GamePrice::where('igdb_game_id', $id)->first();
            if ($gamePrice) {
                $pricing = $gamePrice->getComputedPrice($franchiseNames);
            } else {
                // No DB record yet — compute from base price setting directly
                $basePriceGbp = (float) Setting::get('base_price_gbp', 0);
                if ($basePriceGbp > 0) {
                    $discountPct        = (float) Setting::get('pricing_discount_percent', 85);
                    $discountMultiplier = 1 - ($discountPct / 100);
                    $ageMultiplier      = 1.0;
                    $releaseTs          = $game['first_release_date'] ?? null;
                    if ($releaseTs !== null) {
                        $ageReductionPerYear = (float) Setting::get('age_reduction_per_year', 1);
                        if ($ageReductionPerYear > 0) {
                            $ageYears      = max(0, (int) floor((time() - $releaseTs) / (365.25 * 86400)));
                            $agePct        = min($ageReductionPerYear * $ageYears, 99.0);
                            $ageMultiplier = 1 - ($agePct / 100);
                        }
                    }
                    $computed = max(0.01, round($basePriceGbp * $discountMultiplier * $ageMultiplier, 2));
                    $pricing  = [
                        'is_free'       => false,
                        'display_price' => '£' . number_format($computed, 2),
                        'price_numeric' => $computed,
                    ];
                }
            }
        }

        $inWishlist   = false;
        $inCashBasket = false;
        if (auth()->check() && $game) {
            $user         = auth()->user();
            $inWishlist   = $user->wishlistItems()->where('igdb_game_id', $id)->exists();
            $inCashBasket = $user->cashBasketItems()->where('igdb_game_id', $id)->exists();
        }

        // Track recently viewed (session array of IDs, most recent first, max 20)
        if ($game) {
            $viewed = session('recently_viewed', []);
            $viewed = array_values(array_filter($viewed, fn($v) => $v !== $id));
            array_unshift($viewed, $id);
            session(['recently_viewed' => array_slice($viewed, 0, 20)]);
        }

        return view('game', compact('game', 'error', 'pricing', 'steamAppId', 'inWishlist', 'inCashBasket', 'gamePrice', 'franchiseNames'));
    }
}

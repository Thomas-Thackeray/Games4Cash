<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PricingService
{
    // Fallback rate used only if the DB setting is missing
    private const USD_TO_GBP_FALLBACK = 1.36;

    /**
     * Returns a display-ready pricing array for the game page, or null if
     * no pricing data is available.
     *
     * Raw API numbers are cached for 6 hours to avoid hammering Steam/CheapShark.
     * The admin-configured discount percentage is read fresh from the DB every
     * time so changes take immediate effect without clearing the cache.
     */
    public static function getForSteamApp(int $steamAppId, ?int $releaseTimestamp = null): ?array
    {
        // Cache only raw numbers — not the computed display price
        $raw = Cache::remember("pricing_raw_{$steamAppId}", now()->addHours(6), function () use ($steamAppId) {
            $steam      = self::fetchSteam($steamAppId);
            $cheapShark = self::fetchCheapShark($steamAppId);

            if ($steam === null && $cheapShark === null) {
                return null;
            }

            return [
                'is_free'        => $steam['is_free'] ?? false,
                'steam_gbp'      => $steam['gbp'] ?? null,       // float, already GBP
                'cheapshark_usd' => $cheapShark['usd'] ?? null,  // float, USD
                'steam_url'      => "https://store.steampowered.com/app/{$steamAppId}",
            ];
        });

        if ($raw === null) {
            return null;
        }

        if ($raw['is_free']) {
            return [
                'is_free'       => true,
                'display_price' => 'Free to Play',
                'steam_url'     => $raw['steam_url'],
            ];
        }

        // Read all settings fresh from DB so admin changes apply immediately
        $discountPct        = (float) Setting::get('pricing_discount_percent', 85);
        $discountMultiplier = 1 - ($discountPct / 100);
        $usdToGbp           = (float) Setting::get('usd_to_gbp_rate', self::USD_TO_GBP_FALLBACK);

        // Prefer CheapShark historical low (convert USD → GBP), fall back to Steam
        if ($raw['cheapshark_usd'] !== null) {
            $baseGbp = $raw['cheapshark_usd'] / $usdToGbp;
        } elseif ($raw['steam_gbp'] !== null) {
            $baseGbp = $raw['steam_gbp'];
        } else {
            return null;
        }

        // Age-based additional reduction: N% per full year since release
        $ageMultiplier = 1.0;
        if ($releaseTimestamp !== null) {
            $ageReductionPerYear = (float) Setting::get('age_reduction_per_year', 1);
            if ($ageReductionPerYear > 0) {
                $ageYears      = max(0, (int) floor((time() - $releaseTimestamp) / (365.25 * 86400)));
                $agePct        = min($ageReductionPerYear * $ageYears, 99.0);
                $ageMultiplier = 1 - ($agePct / 100);
            }
        }

        $computed = max(0.01, round($baseGbp * $discountMultiplier * $ageMultiplier, 2));

        return [
            'is_free'       => false,
            'display_price' => '£' . number_format($computed, 2),
            'price_numeric' => $computed,
            'steam_url'     => $raw['steam_url'],
        ];
    }

    /**
     * Returns the raw cached price data (steam_gbp, cheapshark_usd, is_free) for a Steam app,
     * or null if the cache hasn't been populated yet (i.e. getForSteamApp hasn't been called).
     */
    public static function getRawCached(int $steamAppId): ?array
    {
        return Cache::get("pricing_raw_{$steamAppId}");
    }

    // ------------------------------------------------------------------
    //  Steam Store API — returns current price in GBP (cc=gb)
    //  Uses raw pence value to avoid parsing formatted strings
    // ------------------------------------------------------------------
    private static function fetchSteam(int $appId): ?array
    {
        try {
            $response = Http::timeout(4)->get('https://store.steampowered.com/api/appdetails', [
                'appids' => $appId,
                'cc'     => 'gb',
            ]);

            $body    = $response->json();
            $appData = $body[(string) $appId] ?? null;

            if (! $appData || ! ($appData['success'] ?? false)) {
                return null;
            }

            $data = $appData['data'] ?? [];

            if (! empty($data['is_free'])) {
                return ['is_free' => true, 'gbp' => 0.0];
            }

            $po = $data['price_overview'] ?? null;
            if (! $po) {
                return null;
            }

            // 'final' is price in pence (e.g. 4999 = £49.99)
            return [
                'is_free' => false,
                'gbp'     => ($po['final'] ?? 0) / 100.0,
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    // ------------------------------------------------------------------
    //  CheapShark API — returns historical all-time low in USD
    //  Requires two calls: search by Steam App ID → fetch detail by gameID
    // ------------------------------------------------------------------
    private static function fetchCheapShark(int $steamAppId): ?array
    {
        try {
            // Step 1 — resolve Steam App ID to CheapShark's internal gameID
            $search  = Http::timeout(4)->get('https://www.cheapshark.com/api/1.0/games', [
                'steamAppID' => $steamAppId,
                'limit'      => 1,
            ]);
            $gameId  = $search->json()[0]['gameID'] ?? null;
            if (! $gameId) {
                return null;
            }

            // Step 2 — fetch game detail which includes cheapestPriceEver
            $detail = Http::timeout(4)->get('https://www.cheapshark.com/api/1.0/games', [
                'id' => $gameId,
            ]);
            $ever = $detail->json()['cheapestPriceEver'] ?? null;
            if (! $ever) {
                return null;
            }

            return ['usd' => (float) $ever['price']];
        } catch (\Throwable) {
            return null;
        }
    }
}

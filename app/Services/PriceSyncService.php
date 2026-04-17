<?php

namespace App\Services;

use App\Models\GamePrice;
use App\Services\CexService;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PriceSyncService
{
    /**
     * Ensure every game in $igdbGames has a game_prices record.
     *
     * A record is considered "done" if it has no Steam App ID (not on Steam),
     * is marked free, has actual price data, OR was attempted within the last
     * 6 hours. Everything else is (re-)fetched so transient API failures heal
     * automatically on the next page load after 6 hours.
     */
    public static function ensureForGames(array $igdbGames): void
    {
        if (empty($igdbGames)) {
            return;
        }

        try {
            self::sync($igdbGames);
        } catch (\Throwable) {
            // Pricing is best-effort
        }
    }

    // -----------------------------------------------------------------------

    private static function sync(array $igdbGames): void
    {
        $allIds = array_column($igdbGames, 'id');

        // A record counts as "done" only when it definitively has no Steam
        // presence, is free, has price data, or was last tried < 6 hours ago.
        // Everything else (null-price records with a steam_app_id that are
        // stale) is retried so transient Steam / CheapShark failures recover.
        $doneIds = GamePrice::whereIn('igdb_game_id', $allIds)
            ->where(function ($q) {
                $q->whereNull('steam_app_id')                                          // No Steam presence
                  ->orWhere('is_free', true)                                           // Free game
                  ->orWhere(function ($q2) {                                           // Has a price
                      $q2->whereNotNull('steam_gbp')->orWhereNotNull('cheapshark_usd');
                  })
                  ->orWhere('updated_at', '>', now()->subHours(6));                    // Tried recently
            })
            ->pluck('igdb_game_id')
            ->all();

        $missingIds = array_values(array_diff($allIds, $doneIds));

        // Build per-game lookup maps from the IGDB data we already have
        $releaseDates = [];
        $platformMap  = [];
        foreach ($igdbGames as $g) {
            $releaseDates[$g['id']] = $g['first_release_date'] ?? null;
            $platformMap[$g['id']]  = array_values(array_filter(
                array_column($g['platforms'] ?? [], 'id')
            ));
        }

        // Backfill platform_ids for "done" records that are missing them.
        // This heals records created before platforms.id was added to IGDB queries.
        if (! empty($doneIds)) {
            $needingPlatforms = GamePrice::whereIn('igdb_game_id', $doneIds)
                ->whereNull('platform_ids')
                ->pluck('igdb_game_id')
                ->all();
            foreach ($needingPlatforms as $igdbId) {
                $ids = $platformMap[$igdbId] ?? [];
                if (! empty($ids)) {
                    GamePrice::where('igdb_game_id', $igdbId)
                        ->update(['platform_ids' => self::encodePlatformIds($ids)]);
                }
            }
        }

        if (empty($missingIds)) {
            self::syncCex($igdbGames);
            return;
        }

        // Single batched IGDB query to resolve Steam App IDs for all missing games
        $igdb     = new IgdbService();
        $steamMap = $igdb->getSteamAppIds($missingIds); // [igdbId => steamAppId]

        // Games with no Steam App ID — store a placeholder so we don't retry
        // until 6 hours have passed
        $noSteamIds = array_diff($missingIds, array_keys($steamMap));
        foreach ($noSteamIds as $igdbId) {
            GamePrice::updateOrCreate(
                ['igdb_game_id' => (int) $igdbId],
                [
                    'steam_app_id'   => null,
                    'release_date'   => $releaseDates[$igdbId] ?? null,
                    'platform_ids'   => self::encodePlatformIds($platformMap[$igdbId] ?? []),
                    'is_free'        => false,
                    'steam_gbp'      => null,
                    'cheapshark_usd' => null,
                    'updated_at'     => now(),
                ]
            );
        }

        if (empty($steamMap)) {
            self::syncCex($igdbGames);
            return;
        }

        // Parallel-fetch raw prices for every Steam App ID found
        $rawPrices = self::fetchRawBatch(array_values($steamMap));

        foreach ($steamMap as $igdbId => $steamAppId) {
            $raw = $rawPrices[$steamAppId] ?? null;
            GamePrice::updateOrCreate(
                ['igdb_game_id' => (int) $igdbId],
                [
                    'steam_app_id'   => $steamAppId,
                    'release_date'   => $releaseDates[$igdbId] ?? null,
                    'platform_ids'   => self::encodePlatformIds($platformMap[$igdbId] ?? []),
                    'is_free'        => $raw['is_free']        ?? false,
                    'steam_gbp'      => $raw['steam_gbp']      ?? null,
                    'cheapshark_usd' => $raw['cheapshark_usd'] ?? null,
                    'updated_at'     => now(),
                ]
            );
        }

        self::syncCex($igdbGames);
    }

    // -----------------------------------------------------------------------

    /**
     * Parallel-fetch CeX cash prices for all games whose data is missing or stale (>24 h).
     * Runs after the Steam/CheapShark sync so game_prices records exist.
     */
    private static function syncCex(array $igdbGames): void
    {
        if (empty($igdbGames)) {
            return;
        }

        // Build name map from IGDB data
        $nameMap = [];
        foreach ($igdbGames as $g) {
            if (! empty($g['name'])) {
                $nameMap[(int) $g['id']] = $g['name'];
            }
        }

        if (empty($nameMap)) {
            return;
        }

        $allIds = array_keys($nameMap);

        // Only re-fetch when cex_fetched_at is null or older than 24 hours
        $freshIds = GamePrice::whereIn('igdb_game_id', $allIds)
            ->where('cex_fetched_at', '>', now()->subHours(24))
            ->pluck('igdb_game_id')
            ->all();

        $staleIds = array_values(array_diff($allIds, $freshIds));

        if (empty($staleIds)) {
            return;
        }

        // Parallel CeX search requests
        $responses = Http::pool(function (Pool $pool) use ($staleIds, $nameMap) {
            foreach ($staleIds as $igdbId) {
                $pool->as((string) $igdbId)
                    ->timeout(8)
                    ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                    ->get('https://wss2.cex.io/api/search', ['q' => $nameMap[$igdbId]]);
            }
        });

        foreach ($staleIds as $igdbId) {
            $name = $nameMap[$igdbId];
            try {
                $boxes  = $responses[(string) $igdbId]?->json('response.data.boxes') ?? [];
                $prices = CexService::parseBoxes($name, $boxes);
            } catch (\Throwable) {
                $prices = [];
            }

            GamePrice::where('igdb_game_id', $igdbId)->update([
                'cex_prices'     => empty($prices) ? null : json_encode($prices),
                'cex_fetched_at' => now(),
            ]);
        }
    }

    // -----------------------------------------------------------------------

    private static function encodePlatformIds(array $ids): ?string
    {
        return empty($ids) ? null : json_encode(array_values($ids));
    }

    // -----------------------------------------------------------------------

    /**
     * Fetch raw price data for multiple Steam App IDs in parallel.
     * Returns [steamAppId => ['is_free', 'steam_gbp', 'cheapshark_usd', 'steam_url']].
     */
    private static function fetchRawBatch(array $steamAppIds): array
    {
        $results  = [];
        $uncached = [];

        foreach ($steamAppIds as $appId) {
            $cached = Cache::get("pricing_raw_{$appId}");
            if ($cached !== null) {
                $results[$appId] = $cached;
            } else {
                $uncached[] = $appId;
            }
        }

        if (empty($uncached)) {
            return $results;
        }

        // ── Round 1: parallel Steam price calls ────────────────────────────
        $steamResponses = Http::pool(function (Pool $pool) use ($uncached) {
            foreach ($uncached as $appId) {
                $pool->as((string) $appId)->timeout(6)->get(
                    'https://store.steampowered.com/api/appdetails',
                    ['appids' => $appId, 'cc' => 'gb']
                );
            }
        });

        $steamData = [];
        $paidIds   = [];

        foreach ($uncached as $appId) {
            try {
                $body    = $steamResponses[(string) $appId]?->json() ?? [];
                $appData = $body[(string) $appId] ?? null;
                if (! $appData || ! ($appData['success'] ?? false)) {
                    continue;
                }
                $data = $appData['data'] ?? [];
                if (! empty($data['is_free'])) {
                    $steamData[$appId] = ['is_free' => true, 'gbp' => 0.0];
                } elseif ($po = ($data['price_overview'] ?? null)) {
                    $steamData[$appId] = ['is_free' => false, 'gbp' => ($po['final'] ?? 0) / 100.0];
                    $paidIds[]         = $appId;
                }
            } catch (\Throwable) {
            }
        }

        // ── Round 2: parallel CheapShark search calls (paid games only) ───
        $cheapsharkUsd = [];

        if (! empty($paidIds)) {
            $searchResponses = Http::pool(function (Pool $pool) use ($paidIds) {
                foreach ($paidIds as $appId) {
                    $pool->as((string) $appId)->timeout(6)->get(
                        'https://www.cheapshark.com/api/1.0/games',
                        ['steamAppID' => $appId, 'limit' => 1]
                    );
                }
            });

            $csGameIds = [];
            foreach ($paidIds as $appId) {
                try {
                    $gameId = $searchResponses[(string) $appId]?->json()[0]['gameID'] ?? null;
                    if ($gameId) {
                        $csGameIds[$appId] = $gameId;
                    }
                } catch (\Throwable) {
                }
            }

            // ── Round 3: parallel CheapShark detail calls ─────────────────
            if (! empty($csGameIds)) {
                $detailResponses = Http::pool(function (Pool $pool) use ($csGameIds) {
                    foreach ($csGameIds as $appId => $gameId) {
                        $pool->as((string) $appId)->timeout(6)->get(
                            'https://www.cheapshark.com/api/1.0/games',
                            ['id' => $gameId]
                        );
                    }
                });

                foreach ($csGameIds as $appId => $_gameId) {
                    try {
                        $ever = $detailResponses[(string) $appId]?->json()['cheapestPriceEver'] ?? null;
                        if ($ever) {
                            $cheapsharkUsd[$appId] = (float) $ever['price'];
                        }
                    } catch (\Throwable) {
                    }
                }
            }
        }

        // ── Assemble, cache (reusing PricingService's cache key), return ───
        foreach ($uncached as $appId) {
            $entry = $steamData[$appId] ?? null;
            if ($entry === null) {
                continue;
            }

            $raw = [
                'is_free'        => $entry['is_free'],
                'steam_gbp'      => $entry['is_free'] ? null : ($entry['gbp'] ?? null),
                'cheapshark_usd' => $cheapsharkUsd[$appId] ?? null,
                'steam_url'      => "https://store.steampowered.com/app/{$appId}",
            ];

            Cache::put("pricing_raw_{$appId}", $raw, now()->addHours(6));
            $results[$appId] = $raw;
        }

        return $results;
    }
}

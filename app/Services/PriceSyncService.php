<?php

namespace App\Services;

use App\Models\GamePrice;
use App\Models\NoPriceReview;
use App\Models\Setting;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

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
                  ->orWhere(function ($q2) {                                           // Fully synced (both prices)
                      $q2->whereNotNull('steam_gbp')->whereNotNull('cheapshark_usd');
                  })
                  ->orWhereNotNull('cheapshark_usd')                                   // CheapShark data present
                  ->orWhere('updated_at', '>', now()->subHours(6));                    // Tried recently
            })
            ->pluck('igdb_game_id')
            ->all();

        $missingIds = array_values(array_diff($allIds, $doneIds));

        // Build per-game lookup maps from the IGDB data we already have
        $releaseDates = [];
        $platformMap  = [];
        $titleMap     = [];
        foreach ($igdbGames as $g) {
            $releaseDates[$g['id']] = $g['first_release_date'] ?? null;
            $platformMap[$g['id']]  = array_values(array_filter(
                array_column($g['platforms'] ?? [], 'id')
            ));
            $titleMap[$g['id']] = $g['name'] ?? null;
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
            return;
        }

        // Single batched IGDB query to resolve Steam App IDs for all missing games
        $igdb     = new IgdbService();
        $steamMap = $igdb->getSteamAppIds($missingIds); // [igdbId => steamAppId]

        $hasnoPriceTable  = Schema::hasTable('no_price_reviews');
        $hasBasePriceCol  = Schema::hasColumn('game_prices', 'base_price_gbp');
        $usdToGbp         = (float) Setting::get('usd_to_gbp_rate', 1.36);
        // Only queue review entries for platforms the shop actually buys
        $knownPlatformIds = array_keys(config('igdb.all_platforms', []));

        // Games with no Steam App ID — store a placeholder so we don't retry
        // until 6 hours have passed; flag as no-price for admin review
        $noSteamIds = array_diff($missingIds, array_keys($steamMap));
        foreach ($noSteamIds as $igdbId) {
            $values = [
                'steam_app_id'   => null,
                'release_date'   => $releaseDates[$igdbId] ?? null,
                'platform_ids'   => self::encodePlatformIds($platformMap[$igdbId] ?? []),
                'is_free'        => false,
                'steam_gbp'      => null,
                'cheapshark_usd' => null,
                'updated_at'     => now(),
            ];
            if ($hasBasePriceCol) {
                $values['base_price_gbp'] = null;
            }
            if (! empty($titleMap[$igdbId])) {
                $values['game_title'] = $titleMap[$igdbId];
            }
            GamePrice::updateOrCreate(['igdb_game_id' => (int) $igdbId], $values);

            // Queue for admin no-price review (known platforms only)
            if ($hasnoPriceTable) {
                foreach ($platformMap[$igdbId] ?? [] as $platformId) {
                    if (in_array((int) $platformId, $knownPlatformIds, true)) {
                        NoPriceReview::firstOrCreate([
                            'igdb_game_id' => (int) $igdbId,
                            'platform_id'  => (int) $platformId,
                        ]);
                    }
                }
            }
        }

        if (empty($steamMap)) {
            return;
        }

        // Parallel-fetch raw prices for every Steam App ID found
        $rawPrices = self::fetchRawBatch(array_values($steamMap));

        foreach ($steamMap as $igdbId => $steamAppId) {
            $raw           = $rawPrices[$steamAppId] ?? null;
            $isFree        = $raw['is_free']        ?? false;
            $steamGbp      = $raw['steam_gbp']      ?? null;
            $cheapsharkUsd = $raw['cheapshark_usd'] ?? null;

            // Compute base_price_gbp: CheapShark USD → GBP first, then Steam GBP
            if ($cheapsharkUsd !== null) {
                $basePriceGbp = round($cheapsharkUsd / $usdToGbp, 4);
            } elseif ($steamGbp !== null) {
                $basePriceGbp = round($steamGbp, 4);
            } else {
                $basePriceGbp = null;
            }

            $values = [
                'steam_app_id'   => $steamAppId,
                'release_date'   => $releaseDates[$igdbId] ?? null,
                'platform_ids'   => self::encodePlatformIds($platformMap[$igdbId] ?? []),
                'is_free'        => $isFree,
                'steam_gbp'      => $steamGbp,
                'cheapshark_usd' => $cheapsharkUsd,
                'updated_at'     => now(),
            ];
            if ($hasBasePriceCol) {
                $values['base_price_gbp'] = $basePriceGbp;
            }
            if (! empty($titleMap[$igdbId])) {
                $values['game_title'] = $titleMap[$igdbId];
            }
            GamePrice::updateOrCreate(['igdb_game_id' => (int) $igdbId], $values);

            if ($hasnoPriceTable) {
                if (! $isFree && $steamGbp === null && $cheapsharkUsd === null) {
                    // Still no price — ensure review entries exist for known platforms only
                    foreach ($platformMap[$igdbId] ?? [] as $platformId) {
                        if (in_array((int) $platformId, $knownPlatformIds, true)) {
                            NoPriceReview::firstOrCreate([
                                'igdb_game_id' => (int) $igdbId,
                                'platform_id'  => (int) $platformId,
                            ]);
                        }
                    }
                } else {
                    // We have a price now — clear any pending reviews
                    NoPriceReview::where('igdb_game_id', (int) $igdbId)->delete();
                }
            }
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

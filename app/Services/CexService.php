<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CexService
{
    /**
     * Hours to cache CeX results per game title.
     */
    const CACHE_TTL_HOURS = 24;

    /**
     * Maps substrings of CeX category names to IGDB platform IDs.
     * Ordered most-specific first so Xbox Series matches before Xbox.
     */
    const CATEGORY_MAP = [
        'PlayStation 5'   => 167,
        'PlayStation 4'   => 48,
        'PlayStation 3'   => 9,
        'PlayStation 2'   => 8,
        'Xbox Series'     => 169,
        'Xbox One'        => 49,
        'Xbox 360'        => 12,
        'Xbox Games'      => 11,
        'Nintendo Switch' => 130,
        'Wii U'           => 41,
        'Wii Games'       => 5,
        'PC Games'        => 6,
    ];

    /**
     * Return CeX cash prices keyed by IGDB platform ID.
     *
     * Example return value:
     *   [167 => ['cash' => 16.0, 'sell' => 27.0], 48 => ['cash' => 12.0, 'sell' => 20.0]]
     *
     * Returns an empty array if the API is unreachable or the game is not listed.
     * Results are cached for CACHE_TTL_HOURS hours.
     */
    public static function getPrices(string $gameTitle): array
    {
        if ($gameTitle === '') {
            return [];
        }

        $cacheKey = 'cex_v1_' . md5(strtolower(trim($gameTitle)));

        return Cache::remember($cacheKey, now()->addHours(self::CACHE_TTL_HOURS), function () use ($gameTitle) {
            return self::fetch($gameTitle);
        });
    }

    // -----------------------------------------------------------------------

    private static function fetch(string $gameTitle): array
    {
        try {
            $response = Http::timeout(8)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->get('https://wss2.cex.io/api/search', ['q' => $gameTitle]);

            if (! $response->successful()) {
                return [];
            }

            $boxes = $response->json('response.data.boxes') ?? [];

            return self::parseBoxes($gameTitle, $boxes);
        } catch (\Throwable) {
            return [];
        }
    }

    // -----------------------------------------------------------------------

    /**
     * Parse a CeX API boxes array into per-platform price data.
     * Exposed publicly so PriceSyncService can reuse it after batch HTTP calls.
     *
     * Returns: [igdbPlatformId => ['cash' => float, 'sell' => float], ...]
     */
    public static function parseBoxes(string $gameTitle, array $boxes): array
    {
        $prices = [];

        foreach ($boxes as $box) {
            $boxName = $box['boxName'] ?? '';

            // Only accept boxes whose name closely matches the searched title.
            // similar_text gives percentage overlap; 65% catches subtitles like
            // "Elden Ring: Shadow of the Erdtree" when searching "Elden Ring".
            similar_text(strtolower(trim($gameTitle)), strtolower(trim($boxName)), $pct);
            if ($pct < 65) {
                continue;
            }

            $categoryName = $box['categoryName'] ?? '';
            $platformId   = self::mapCategory($categoryName);
            if ($platformId === null) {
                continue;
            }

            $cashPrice = isset($box['cashPrice']) ? (float) $box['cashPrice'] : null;
            if ($cashPrice === null || $cashPrice <= 0) {
                continue;
            }

            // Keep the highest cash price per platform (handles duplicate listings)
            if (! isset($prices[$platformId]) || $cashPrice > $prices[$platformId]['cash']) {
                $prices[$platformId] = [
                    'cash' => $cashPrice,
                    'sell' => (float) ($box['sellPrice'] ?? 0),
                ];
            }
        }

        return $prices;
    }

    // -----------------------------------------------------------------------

    private static function mapCategory(string $categoryName): ?int
    {
        foreach (self::CATEGORY_MAP as $keyword => $platformId) {
            if (str_contains($categoryName, $keyword)) {
                return $platformId;
            }
        }
        return null;
    }
}

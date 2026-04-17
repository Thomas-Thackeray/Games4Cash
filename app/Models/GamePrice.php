<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\FranchiseAdjustment;
use App\Models\Setting;
use App\Services\CexService;

class GamePrice extends Model
{
    protected $table      = 'game_prices';
    protected $primaryKey = 'igdb_game_id';
    public    $incrementing = false;
    public    $timestamps   = false; // updated_at set explicitly so we control retry logic

    protected $fillable = [
        'igdb_game_id',
        'game_title',
        'slug',
        'cex_prices',
        'cex_fetched_at',
        'price_overrides',
        'steam_app_id',
        'release_date',
        'platform_ids',
        'franchise_names',
        'is_free',
        'is_bundle',
        'steam_gbp',
        'cheapshark_usd',
        'updated_at',
    ];

    protected $casts = [
        'is_free'         => 'boolean',
        'is_bundle'       => 'boolean',
        'steam_gbp'       => 'float',
        'cheapshark_usd'  => 'float',
        'franchise_names' => 'array',
        'cex_prices'      => 'array',
        'cex_fetched_at'  => 'datetime',
        'price_overrides' => 'array',
    ];

    /**
     * Human-readable display name, falling back to slug-derived title.
     */
    public function displayName(): string
    {
        if (! empty($this->game_title)) {
            return $this->game_title;
        }
        if (! empty($this->slug)) {
            return ucwords(str_replace('-', ' ', $this->slug));
        }
        return 'Game #' . $this->igdb_game_id;
    }

    /**
     * Remove any games from $igdbGames that are already known to be free-to-play.
     * One DB query regardless of list size; unknown games are left in (they'll be
     * checked on first price sync and removed from future listings once marked free).
     */
    public static function stripFreeGames(array $igdbGames): array
    {
        if (empty($igdbGames)) {
            return [];
        }

        $ids     = array_column($igdbGames, 'id');
        $freeIds = static::whereIn('igdb_game_id', $ids)
            ->where('is_free', true)
            ->pluck('igdb_game_id')
            ->flip() // use as a hash set for O(1) lookup
            ->all();

        return empty($freeIds)
            ? $igdbGames
            : array_values(array_filter($igdbGames, fn ($g) => ! isset($freeIds[$g['id']])));
    }

    /**
     * Upsert raw price data. Always sets updated_at so retry logic works correctly.
     */
    public static function record(
        int    $igdbGameId,
        int    $steamAppId,
        ?int   $releaseDate,
        bool   $isFree,
        ?float $steamGbp,
        ?float $cheapsharkUsd,
        array  $platformIds = [],
        array  $franchiseNames = [],
        bool   $isBundle = false,
        ?string $slug = null,
    ): void {
        $values = [
            'steam_app_id'    => $steamAppId,
            'release_date'    => $releaseDate,
            'platform_ids'    => empty($platformIds) ? null : json_encode(array_values($platformIds)),
            'franchise_names' => empty($franchiseNames) ? null : json_encode(array_values($franchiseNames)),
            'is_free'         => $isFree,
            'is_bundle'       => $isBundle,
            'steam_gbp'       => $steamGbp,
            'cheapshark_usd'  => $cheapsharkUsd,
            'updated_at'      => now(),
        ];

        // Only write slug when we have one — never overwrite with null
        if ($slug !== null) {
            $values['slug'] = $slug;
        }

        self::updateOrCreate(['igdb_game_id' => $igdbGameId], $values);
    }

    /**
     * Return the canonical public URL for a game given its IGDB ID.
     * Uses the stored slug when available, falls back to the numeric ID route.
     */
    public static function urlForId(int $igdbId): string
    {
        $slug = static::where('igdb_game_id', $igdbId)->value('slug');
        return $slug
            ? route('game.show', ['slug' => $slug])
            : url('/game/' . $igdbId);
    }

    /**
     * Return CeX prices for this game, fetching from the API if not yet stored.
     * Persists the result to cex_prices + cex_fetched_at so it can be listed in admin.
     */
    private function resolveCexPrices(?string $gameTitle): array
    {
        // Use what's already stored on this record
        if (! empty($this->cex_prices)) {
            return $this->cex_prices;
        }

        if ($gameTitle === null || $gameTitle === '') {
            return [];
        }

        $prices = CexService::getPrices($gameTitle);

        if (! empty($prices)) {
            try {
                static::where('igdb_game_id', $this->igdb_game_id)->update([
                    'cex_prices'     => json_encode($prices),
                    'cex_fetched_at' => now(),
                ]);
            } catch (\Throwable) {
                // Column may not exist yet if migration hasn't run on this environment
            }
            $this->cex_prices     = $prices;
            $this->cex_fetched_at = now();
        }

        return $prices;
    }

    /**
     * Compute the display price from stored raw values and current admin settings.
     *
     * Formula (in order):
     *   1. Base price: CeX cashPrice (if available) → Steam GBP → CheapShark USD → admin fallback
     *   2. If using CeX: apply cex_margin_pct. Otherwise: franchise adj + discount %.
     *   3. Apply age-based reduction
     *   4. Floor at £0.01; if under £0.10 add low-price boost
     *   5. Bundle bonus and high-price reduction
     */
    public function getComputedPrice(array $franchiseNames = [], ?string $gameTitle = null): ?array
    {
        if ($this->is_free) {
            return [
                'is_free'       => true,
                'display_price' => 'Free to Play',
                'price_numeric' => null,
            ];
        }

        // 1. Try CeX first when we have a game title
        $cexPrices = $this->resolveCexPrices($gameTitle);
        $usedCex   = false;

        if (! empty($cexPrices)) {
            // Use the highest CeX cash price across all platforms as the general price
            $maxCash  = max(array_column($cexPrices, 'cash'));
            $marginPct = (float) Setting::get('cex_margin_pct', 90);
            $computed  = $maxCash * ($marginPct / 100);
            $usedCex   = true;
        } else {
            // Fallback: CheapShark → Steam → admin base price
            $usdToGbp = (float) Setting::get('usd_to_gbp_rate', 1.36);
            if ($this->cheapshark_usd !== null) {
                $baseGbp = $this->cheapshark_usd / $usdToGbp;
            } elseif ($this->steam_gbp !== null) {
                $baseGbp = $this->steam_gbp;
            } else {
                $basePriceGbp = (float) Setting::get('base_price_gbp', 0);
                if ($basePriceGbp <= 0) {
                    return null;
                }
                $baseGbp = $basePriceGbp;
            }

            // Franchise adjustment
            $resolvedNames = !empty($franchiseNames) ? $franchiseNames : ($this->franchise_names ?? []);
            $baseGbp      += FranchiseAdjustment::getAdjustment($resolvedNames);

            // Discount
            $discountPct = (float) Setting::get('pricing_discount_percent', 85);
            $computed    = $baseGbp * (1 - ($discountPct / 100));
        }

        // Age-based reduction (flat £ per year)
        if ($this->release_date !== null) {
            $ageReductionGbp = (float) Setting::get('age_reduction_per_year', 0);
            if ($ageReductionGbp > 0) {
                $ageYears = max(0, (int) floor((time() - $this->release_date) / (365.25 * 86400)));
                $computed = max(0.01, $computed - ($ageYears * $ageReductionGbp));
            }
        }

        // Floor and low-price boost
        $computed = max(0.01, round($computed, 2));
        $lowPriceBoost = (float) Setting::get('low_price_boost_gbp', 0.20);
        if ($computed < 0.10 && $lowPriceBoost > 0) {
            $computed = round($computed + $lowPriceBoost, 2);
        }

        // Bundle bonus
        if ($this->is_bundle) {
            $bundleGbp = (float) Setting::get('bundle_price_increase_gbp', 0);
            if ($bundleGbp > 0) {
                $computed = round($computed + $bundleGbp, 2);
            }
        }

        // High-price reduction
        $highPricePct = (float) Setting::get('high_price_reduction_pct', 0);
        if ($highPricePct > 0 && $computed > 10.00) {
            $computed = round($computed * (1 - ($highPricePct / 100)), 2);
        }

        return [
            'is_free'       => false,
            'display_price' => '£' . number_format($computed, 2),
            'price_numeric' => $computed,
        ];
    }

    /**
     * Compute the display price for a specific platform.
     *
     * Priority: Override → CeX → CheapShark → Steam → base price.
     * Returns 'source' indicating which signal was used.
     */
    public function getComputedPriceForPlatform(int $platformId, array $franchiseNames = [], ?string $gameTitle = null): ?array
    {
        if ($this->is_free) {
            return ['is_free' => true, 'display_price' => 'Free to Play', 'price_numeric' => null, 'source' => null];
        }

        // 0. Manual override takes absolute priority
        $overrides = $this->price_overrides ?? [];
        if (isset($overrides[$platformId])) {
            $overridePrice = round((float) $overrides[$platformId], 2);
            return [
                'is_free'       => false,
                'display_price' => '£' . number_format($overridePrice, 2),
                'price_numeric' => $overridePrice,
                'source'        => 'Override',
            ];
        }

        // 1. Try CeX for this specific platform
        $cexPrices = $this->resolveCexPrices($gameTitle);

        if (isset($cexPrices[$platformId])) {
            $marginPct = (float) Setting::get('cex_margin_pct', 90);
            $computed  = $cexPrices[$platformId]['cash'] * ($marginPct / 100);
            $source    = 'CeX';
        } else {
            // Fallback: CheapShark → Steam → admin base price
            $usdToGbp = (float) Setting::get('usd_to_gbp_rate', 1.36);
            if ($this->cheapshark_usd !== null) {
                $baseGbp = $this->cheapshark_usd / $usdToGbp;
                $source  = 'CheapShark';
            } elseif ($this->steam_gbp !== null) {
                $baseGbp = $this->steam_gbp;
                $source  = 'Steam';
            } else {
                $basePriceGbp = (float) Setting::get('base_price_gbp', 0);
                if ($basePriceGbp <= 0) {
                    return null;
                }
                $baseGbp = $basePriceGbp;
                $source  = 'Base Price';
            }

            // Franchise adjustment
            $resolvedNames = !empty($franchiseNames) ? $franchiseNames : ($this->franchise_names ?? []);
            $baseGbp      += FranchiseAdjustment::getAdjustment($resolvedNames);

            // Discount
            $discountPct = (float) Setting::get('pricing_discount_percent', 85);
            $computed    = $baseGbp * (1 - ($discountPct / 100));

            // Platform modifier (skipped when using CeX since those prices are already platform-specific)
            $adjustment     = (float) Setting::get("platform_modifier_{$platformId}", 0);
            $adjustmentType = Setting::get("platform_modifier_type_{$platformId}", 'percent');
            if ($adjustment !== 0.0) {
                $computed = $adjustmentType === 'gbp'
                    ? $computed + $adjustment
                    : $computed * (1 + ($adjustment / 100));
            }
        }

        // Age-based reduction
        if ($this->release_date !== null) {
            $ageReductionGbp = (float) Setting::get('age_reduction_per_year', 0);
            if ($ageReductionGbp > 0) {
                $ageYears = max(0, (int) floor((time() - $this->release_date) / (365.25 * 86400)));
                $computed = max(0.01, $computed - ($ageYears * $ageReductionGbp));
            }
        }

        // Floor and low-price boost
        $computed      = max(0.01, round($computed, 2));
        $lowPriceBoost = (float) Setting::get('low_price_boost_gbp', 0.20);
        if ($computed < 0.10 && $lowPriceBoost > 0) {
            $computed = round($computed + $lowPriceBoost, 2);
        }

        // Bundle bonus
        if ($this->is_bundle) {
            $bundleGbp = (float) Setting::get('bundle_price_increase_gbp', 0);
            if ($bundleGbp > 0) {
                $computed = round($computed + $bundleGbp, 2);
            }
        }

        // High-price reduction
        $highPricePct = (float) Setting::get('high_price_reduction_pct', 0);
        if ($highPricePct > 0 && $computed > 10.00) {
            $computed = round($computed * (1 - ($highPricePct / 100)), 2);
        }

        return [
            'is_free'       => false,
            'display_price' => '£' . number_format($computed, 2),
            'price_numeric' => $computed,
            'source'        => $source,
        ];
    }

    /**
     * Compute price for admin listing using only already-stored data.
     * Never triggers CeX API calls or DB writes — safe to call in bulk.
     */
    public function adminPriceForPlatform(int $platformId): ?array
    {
        if ($this->is_free) {
            return ['display_price' => 'Free to Play', 'price_numeric' => null, 'source' => null];
        }

        // Override
        $overrides = $this->price_overrides ?? [];
        if (isset($overrides[$platformId])) {
            $p = round((float) $overrides[$platformId], 2);
            return ['display_price' => '£' . number_format($p, 2), 'price_numeric' => $p, 'source' => 'Override'];
        }

        // CeX (stored column only — no API call)
        $cexPrices = $this->cex_prices ?? [];
        if (isset($cexPrices[$platformId])) {
            $marginPct = (float) Setting::get('cex_margin_pct', 90);
            $computed  = $cexPrices[$platformId]['cash'] * ($marginPct / 100);
            $source    = 'CeX';
        } else {
            $usdToGbp = (float) Setting::get('usd_to_gbp_rate', 1.36);
            if ($this->cheapshark_usd !== null) {
                $baseGbp = $this->cheapshark_usd / $usdToGbp;
                $source  = 'CheapShark';
            } elseif ($this->steam_gbp !== null) {
                $baseGbp = $this->steam_gbp;
                $source  = 'Steam';
            } else {
                $basePriceGbp = (float) Setting::get('base_price_gbp', 0);
                if ($basePriceGbp <= 0) return null;
                $baseGbp = $basePriceGbp;
                $source  = 'Base Price';
            }

            $franchiseNames = $this->franchise_names ?? [];
            if (is_string($franchiseNames)) {
                $franchiseNames = json_decode($franchiseNames, true) ?? [];
            }
            $baseGbp    += FranchiseAdjustment::getAdjustment($franchiseNames);
            $discountPct = (float) Setting::get('pricing_discount_percent', 85);
            $computed    = $baseGbp * (1 - ($discountPct / 100));

            $adjustment     = (float) Setting::get("platform_modifier_{$platformId}", 0);
            $adjustmentType = Setting::get("platform_modifier_type_{$platformId}", 'percent');
            if ($adjustment !== 0.0) {
                $computed = $adjustmentType === 'gbp'
                    ? $computed + $adjustment
                    : $computed * (1 + ($adjustment / 100));
            }
        }

        if ($this->release_date !== null) {
            $ageReductionGbp = (float) Setting::get('age_reduction_per_year', 0);
            if ($ageReductionGbp > 0) {
                $ageYears = max(0, (int) floor((time() - $this->release_date) / (365.25 * 86400)));
                $computed = max(0.01, $computed - ($ageYears * $ageReductionGbp));
            }
        }

        $computed      = max(0.01, round($computed, 2));
        $lowPriceBoost = (float) Setting::get('low_price_boost_gbp', 0.20);
        if ($computed < 0.10 && $lowPriceBoost > 0) {
            $computed = round($computed + $lowPriceBoost, 2);
        }
        if ($this->is_bundle) {
            $bundleGbp = (float) Setting::get('bundle_price_increase_gbp', 0);
            if ($bundleGbp > 0) $computed = round($computed + $bundleGbp, 2);
        }
        $highPricePct = (float) Setting::get('high_price_reduction_pct', 0);
        if ($highPricePct > 0 && $computed > 10.00) {
            $computed = round($computed * (1 - ($highPricePct / 100)), 2);
        }

        return ['display_price' => '£' . number_format($computed, 2), 'price_numeric' => $computed, 'source' => $source];
    }
}

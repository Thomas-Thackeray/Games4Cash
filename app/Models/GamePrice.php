<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\FranchiseAdjustment;
use App\Models\GameNameAdjustment;
use App\Models\Setting;

class GamePrice extends Model
{
    protected $table      = 'game_prices';
    protected $primaryKey = 'igdb_game_id';
    public    $incrementing = false;
    public    $timestamps   = false; // updated_at set explicitly so we control retry logic

    protected $fillable = [
        'igdb_game_id',
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
    ];

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
    ): void {
        self::updateOrCreate(
            ['igdb_game_id' => $igdbGameId],
            [
                'steam_app_id'    => $steamAppId,
                'release_date'    => $releaseDate,
                'platform_ids'    => empty($platformIds) ? null : json_encode(array_values($platformIds)),
                'franchise_names' => empty($franchiseNames) ? null : json_encode(array_values($franchiseNames)),
                'is_free'         => $isFree,
                'is_bundle'       => $isBundle,
                'steam_gbp'       => $steamGbp,
                'cheapshark_usd'  => $cheapsharkUsd,
                'updated_at'      => now(),
            ]
        );
    }

    /**
     * Compute the display price from stored raw values and current admin settings.
     *
     * Formula (in order):
     *   1. Base price: Steam GBP → CheapShark USD (converted) → admin Base Price fallback
     *   2. Add/subtract franchise adjustment (flat £)
     *   3. Apply discount %
     *   4. Apply best platform modifier across the game's platforms
     *   5. Apply age-based reduction
     *   6. Floor at £0.01; if under £0.10 add £0.20 low-price boost
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

        // 1. Base price: Steam first, CheapShark second, admin fallback last
        $usdToGbp = (float) Setting::get('usd_to_gbp_rate', 1.36);
        if ($this->steam_gbp !== null) {
            $baseGbp = $this->steam_gbp;
        } elseif ($this->cheapshark_usd !== null) {
            $baseGbp = $this->cheapshark_usd / $usdToGbp;
        } else {
            $basePriceGbp = (float) Setting::get('base_price_gbp', 0);
            if ($basePriceGbp <= 0) {
                return null;
            }
            $baseGbp = $basePriceGbp;
        }

        // 2. Franchise adjustment added to the base price
        $resolvedNames = !empty($franchiseNames) ? $franchiseNames : ($this->franchise_names ?? []);
        $franchiseAdj  = FranchiseAdjustment::getAdjustment($resolvedNames);
        $baseGbp      += $franchiseAdj;

        // 2b. Game name adjustment (keyword partial-match against the game title)
        if ($gameTitle !== null) {
            $baseGbp += GameNameAdjustment::getAdjustment($gameTitle);
        }

        // 3. Discount
        $discountPct = (float) Setting::get('pricing_discount_percent', 85);
        $computed    = $baseGbp * (1 - ($discountPct / 100));

        // 4. No platform modifier here — per-platform prices are computed by
        //    getComputedPriceForPlatform() and shown individually in the Get Cash dropdown.

        // 5. Age-based reduction (flat £ per year deducted from the computed price)
        if ($this->release_date !== null) {
            $ageReductionGbp = (float) Setting::get('age_reduction_per_year', 0);
            if ($ageReductionGbp > 0) {
                $ageYears = max(0, (int) floor((time() - $this->release_date) / (365.25 * 86400)));
                $computed = max(0.01, $computed - ($ageYears * $ageReductionGbp));
            }
        }

        // 6. Floor and low-price boost
        $computed = max(0.01, round($computed, 2));
        $lowPriceBoost = (float) Setting::get('low_price_boost_gbp', 0.20);
        if ($computed < 0.10 && $lowPriceBoost > 0) {
            $computed = round($computed + $lowPriceBoost, 2);
        }

        // 7. Bundle bonus: if this is a game bundle, add flat £ amount
        if ($this->is_bundle) {
            $bundleGbp = (float) Setting::get('bundle_price_increase_gbp', 0);
            if ($bundleGbp > 0) {
                $computed = round($computed + $bundleGbp, 2);
            }
        }

        // 8. High-price reduction: if price > £10, deduct X%
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
     * Compute the display price for a specific platform (uses that platform's modifier only).
     *
     * Same formula as getComputedPrice() but uses the given platform's modifier
     * rather than the best modifier across all stored platforms.
     */
    public function getComputedPriceForPlatform(int $platformId, array $franchiseNames = [], ?string $gameTitle = null): ?array
    {
        if ($this->is_free) {
            return [
                'is_free'       => true,
                'display_price' => 'Free to Play',
                'price_numeric' => null,
            ];
        }

        // 1. Base price: Steam first, CheapShark second, admin fallback last
        $usdToGbp = (float) Setting::get('usd_to_gbp_rate', 1.36);
        if ($this->steam_gbp !== null) {
            $baseGbp = $this->steam_gbp;
        } elseif ($this->cheapshark_usd !== null) {
            $baseGbp = $this->cheapshark_usd / $usdToGbp;
        } else {
            $basePriceGbp = (float) Setting::get('base_price_gbp', 0);
            if ($basePriceGbp <= 0) {
                return null;
            }
            $baseGbp = $basePriceGbp;
        }

        // 2. Franchise adjustment added to the base price
        $resolvedNames = !empty($franchiseNames) ? $franchiseNames : ($this->franchise_names ?? []);
        $franchiseAdj  = FranchiseAdjustment::getAdjustment($resolvedNames);
        $baseGbp      += $franchiseAdj;

        // 2b. Game name adjustment (keyword partial-match against the game title)
        if ($gameTitle !== null) {
            $baseGbp += GameNameAdjustment::getAdjustment($gameTitle);
        }

        // 3. Discount
        $discountPct = (float) Setting::get('pricing_discount_percent', 85);
        $computed    = $baseGbp * (1 - ($discountPct / 100));

        // 4. This platform's modifier (% or flat £)
        $adjustment     = (float) Setting::get("platform_modifier_{$platformId}", 0);
        $adjustmentType = Setting::get("platform_modifier_type_{$platformId}", 'percent');
        if ($adjustment !== 0.0) {
            if ($adjustmentType === 'gbp') {
                $computed += $adjustment;
            } else {
                $computed *= 1 + ($adjustment / 100);
            }
        }

        // 5. Age-based reduction (flat £ per year deducted from the computed price)
        if ($this->release_date !== null) {
            $ageReductionGbp = (float) Setting::get('age_reduction_per_year', 0);
            if ($ageReductionGbp > 0) {
                $ageYears = max(0, (int) floor((time() - $this->release_date) / (365.25 * 86400)));
                $computed = max(0.01, $computed - ($ageYears * $ageReductionGbp));
            }
        }

        // 6. Floor and low-price boost
        $computed = max(0.01, round($computed, 2));
        $lowPriceBoost = (float) Setting::get('low_price_boost_gbp', 0.20);
        if ($computed < 0.10 && $lowPriceBoost > 0) {
            $computed = round($computed + $lowPriceBoost, 2);
        }

        // 7. Bundle bonus: if this is a game bundle, add flat £ amount
        if ($this->is_bundle) {
            $bundleGbp = (float) Setting::get('bundle_price_increase_gbp', 0);
            if ($bundleGbp > 0) {
                $computed = round($computed + $bundleGbp, 2);
            }
        }

        // 8. High-price reduction: if price > £10, deduct X%
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
}

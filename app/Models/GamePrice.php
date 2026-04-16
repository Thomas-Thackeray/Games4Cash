<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\FranchiseAdjustment;
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
        'is_free',
        'steam_gbp',
        'cheapshark_usd',
        'updated_at',
    ];

    protected $casts = [
        'is_free'        => 'boolean',
        'steam_gbp'      => 'float',
        'cheapshark_usd' => 'float',
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
    ): void {
        self::updateOrCreate(
            ['igdb_game_id' => $igdbGameId],
            [
                'steam_app_id'   => $steamAppId,
                'release_date'   => $releaseDate,
                'platform_ids'   => empty($platformIds) ? null : json_encode(array_values($platformIds)),
                'is_free'        => $isFree,
                'steam_gbp'      => $steamGbp,
                'cheapshark_usd' => $cheapsharkUsd,
                'updated_at'     => now(),
            ]
        );
    }

    /**
     * Compute the display price from stored raw values and current admin settings.
     * Returns null if there is no usable price data.
     */
    public function getComputedPrice(array $franchiseNames = []): ?array
    {
        if ($this->is_free) {
            return [
                'is_free'       => true,
                'display_price' => 'Free to Play',
                'price_numeric' => null,
            ];
        }

        $usdToGbp           = (float) Setting::get('usd_to_gbp_rate', 1.36);
        $discountPct        = (float) Setting::get('pricing_discount_percent', 85);
        $discountMultiplier = 1 - ($discountPct / 100);

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

        // Age-based reduction
        $ageMultiplier = 1.0;
        if ($this->release_date !== null) {
            $ageReductionPerYear = (float) Setting::get('age_reduction_per_year', 1);
            if ($ageReductionPerYear > 0) {
                $ageYears      = max(0, (int) floor((time() - $this->release_date) / (365.25 * 86400)));
                $agePct        = min($ageReductionPerYear * $ageYears, 99.0);
                $ageMultiplier = 1 - ($agePct / 100);
            }
        }

        // Per-platform modifier — apply the most favourable modifier across the game's platforms
        $platformMultiplier = 1.0;
        $platformIds = json_decode($this->platform_ids ?? '[]', true);
        if (! empty($platformIds)) {
            $adjustments = array_map(
                fn($pid) => (float) Setting::get("platform_modifier_{$pid}", 0),
                $platformIds
            );
            $bestAdjustment = max($adjustments); // Most favourable for the seller
            if ($bestAdjustment !== 0.0) {
                $platformMultiplier = 1 + ($bestAdjustment / 100);
            }
        }

        $franchiseAdj = FranchiseAdjustment::getAdjustment($franchiseNames);
        $computed = max(0.01, round($baseGbp * $discountMultiplier * $ageMultiplier * $platformMultiplier + $franchiseAdj, 2));

        return [
            'is_free'       => false,
            'display_price' => '£' . number_format($computed, 2),
            'price_numeric' => $computed,
        ];
    }

    /**
     * Compute the display price for a specific platform (uses that platform's modifier only).
     * Returns null if there is no usable price data.
     */
    public function getComputedPriceForPlatform(int $platformId, array $franchiseNames = []): ?array
    {
        if ($this->is_free) {
            return [
                'is_free'       => true,
                'display_price' => 'Free to Play',
                'price_numeric' => null,
            ];
        }

        $usdToGbp           = (float) Setting::get('usd_to_gbp_rate', 1.36);
        $discountPct        = (float) Setting::get('pricing_discount_percent', 85);
        $discountMultiplier = 1 - ($discountPct / 100);

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

        // Age-based reduction
        $ageMultiplier = 1.0;
        if ($this->release_date !== null) {
            $ageReductionPerYear = (float) Setting::get('age_reduction_per_year', 1);
            if ($ageReductionPerYear > 0) {
                $ageYears      = max(0, (int) floor((time() - $this->release_date) / (365.25 * 86400)));
                $agePct        = min($ageReductionPerYear * $ageYears, 99.0);
                $ageMultiplier = 1 - ($agePct / 100);
            }
        }

        // Apply only this platform's modifier
        $platformMultiplier = 1.0;
        $adjustment = (float) Setting::get("platform_modifier_{$platformId}", 0);
        if ($adjustment !== 0.0) {
            $platformMultiplier = 1 + ($adjustment / 100);
        }

        $franchiseAdj = FranchiseAdjustment::getAdjustment($franchiseNames);
        $computed = max(0.01, round($baseGbp * $discountMultiplier * $ageMultiplier * $platformMultiplier + $franchiseAdj, 2));

        return [
            'is_free'       => false,
            'display_price' => '£' . number_format($computed, 2),
            'price_numeric' => $computed,
        ];
    }
}

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
    public    $timestamps   = false;

    protected $fillable = [
        'igdb_game_id',
        'game_title',
        'slug',
        'steam_app_id',
        'release_date',
        'platform_ids',
        'franchise_names',
        'is_free',
        'is_bundle',
        'steam_gbp',
        'cheapshark_usd',
        'base_price_gbp',
        'price_overrides',
        'updated_at',
    ];

    protected $casts = [
        'is_free'         => 'boolean',
        'is_bundle'       => 'boolean',
        'steam_gbp'       => 'float',
        'cheapshark_usd'  => 'float',
        'base_price_gbp'  => 'float',
        'franchise_names' => 'array',
        'price_overrides' => 'array',
    ];

    // -----------------------------------------------------------------------
    //  Helpers
    // -----------------------------------------------------------------------

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
     * Remove free-to-play games from an IGDB result array.
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
            ->flip()
            ->all();

        return empty($freeIds)
            ? $igdbGames
            : array_values(array_filter($igdbGames, fn ($g) => ! isset($freeIds[$g['id']])));
    }

    /**
     * Upsert raw price data.
     */
    public static function record(
        int    $igdbGameId,
        int    $steamAppId,
        ?int   $releaseDate,
        bool   $isFree,
        ?float $steamGbp,
        ?float $cheapsharkUsd,
        array  $platformIds    = [],
        array  $franchiseNames = [],
        bool   $isBundle       = false,
        ?string $slug          = null,
    ): void {
        $usdToGbp     = (float) Setting::get('usd_to_gbp_rate', 1.36);
        $basePriceGbp = null;

        if ($cheapsharkUsd !== null) {
            $basePriceGbp = round($cheapsharkUsd / $usdToGbp, 4);
        } elseif ($steamGbp !== null) {
            $basePriceGbp = round($steamGbp, 4);
        }

        $values = [
            'steam_app_id'    => $steamAppId,
            'release_date'    => $releaseDate,
            'platform_ids'    => empty($platformIds) ? null : json_encode(array_values($platformIds)),
            'franchise_names' => empty($franchiseNames) ? null : json_encode(array_values($franchiseNames)),
            'is_free'         => $isFree,
            'is_bundle'       => $isBundle,
            'steam_gbp'       => $steamGbp,
            'cheapshark_usd'  => $cheapsharkUsd,
            'base_price_gbp'  => $basePriceGbp,
            'updated_at'      => now(),
        ];

        if ($slug !== null) {
            $values['slug'] = $slug;
        }

        self::updateOrCreate(['igdb_game_id' => $igdbGameId], $values);
    }

    public static function urlForId(int $igdbId): string
    {
        $slug = static::where('igdb_game_id', $igdbId)->value('slug');
        return $slug
            ? route('game.show', ['slug' => $slug])
            : url('/game/' . $igdbId);
    }

    // -----------------------------------------------------------------------
    //  Pricing
    // -----------------------------------------------------------------------

    /**
     * Compute the cash offer price for a specific platform.
     *
     * Formula:
     *   1. Base price: CheapShark USD → GBP, then Steam GBP (no other fallback)
     *   2. Add franchise adjustment (flat £)
     *   3. Add platform modifier (flat £ or %)
     *   4. Apply age-based reduction (£ per year)
     *   5. Apply discount %
     *   6. Floor at £0.01; if < £0.05 apply low-price boost
     */
    public function getComputedPriceForPlatform(int $platformId, array $franchiseNames = [], ?string $gameTitle = null): ?array
    {
        if ($this->is_free) {
            return ['is_free' => true, 'display_price' => 'Free to Play', 'price_numeric' => null, 'source' => null];
        }

        // 0. Manual override (absolute priority)
        $overrides = $this->price_overrides ?? [];
        if (isset($overrides[$platformId])) {
            $p = round((float) $overrides[$platformId], 2);
            return ['is_free' => false, 'display_price' => '£' . number_format($p, 2), 'price_numeric' => $p, 'source' => 'Override'];
        }

        // 1. Base price
        $usdToGbp = (float) Setting::get('usd_to_gbp_rate', 1.36);
        if ($this->cheapshark_usd !== null) {
            $baseGbp = $this->cheapshark_usd / $usdToGbp;
            $source  = 'CheapShark';
        } elseif ($this->steam_gbp !== null) {
            $baseGbp = $this->steam_gbp;
            $source  = 'Steam';
        } else {
            return null;
        }

        return $this->applyAdjustments($baseGbp, $source, $platformId, $franchiseNames);
    }

    /**
     * Admin-safe version: reads stored data only, never triggers API calls.
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

        // Base price
        $usdToGbp = (float) Setting::get('usd_to_gbp_rate', 1.36);
        if ($this->cheapshark_usd !== null) {
            $baseGbp = $this->cheapshark_usd / $usdToGbp;
            $source  = 'CheapShark';
        } elseif ($this->steam_gbp !== null) {
            $baseGbp = $this->steam_gbp;
            $source  = 'Steam';
        } else {
            return null;
        }

        return $this->applyAdjustments($baseGbp, $source, $platformId);
    }

    /**
     * Shared adjustment pipeline used by both price methods.
     */
    private function applyAdjustments(float $baseGbp, string $source, int $platformId, array $franchiseNames = []): array
    {
        // 2. Franchise adjustment (flat £)
        $resolvedNames = ! empty($franchiseNames) ? $franchiseNames : ($this->franchise_names ?? []);
        if (is_string($resolvedNames)) {
            $resolvedNames = json_decode($resolvedNames, true) ?? [];
        }
        $baseGbp += FranchiseAdjustment::getAdjustment($resolvedNames);

        // 3. Platform modifier (£ or %)
        $modifier     = (float) Setting::get("platform_modifier_{$platformId}", 0);
        $modifierType = Setting::get("platform_modifier_type_{$platformId}", 'percent');
        if ($modifier !== 0.0) {
            $baseGbp = $modifierType === 'gbp'
                ? $baseGbp + $modifier
                : $baseGbp * (1 + ($modifier / 100));
        }

        // 4. Age-based reduction
        if ($this->release_date !== null) {
            $ageReduction = (float) Setting::get('age_reduction_per_year', 0);
            if ($ageReduction > 0) {
                $ageYears = max(0, (int) floor((time() - $this->release_date) / (365.25 * 86400)));
                $baseGbp  = max(0.01, $baseGbp - ($ageYears * $ageReduction));
            }
        }

        // 5. Discount
        $discountPct = (float) Setting::get('pricing_discount_percent', 85);
        $computed    = max(0.01, $baseGbp * (1 - ($discountPct / 100)));

        // 6. Floor & low-price boost (threshold: £0.05)
        $computed      = round($computed, 2);
        $lowPriceBoost = (float) Setting::get('low_price_boost_gbp', 0.10);
        if ($computed < 0.05 && $lowPriceBoost > 0) {
            $computed = round($lowPriceBoost, 2);
        }

        return [
            'is_free'       => false,
            'display_price' => '£' . number_format($computed, 2),
            'price_numeric' => $computed,
            'source'        => $source,
        ];
    }

    /**
     * Legacy: compute a single general price (not platform-specific).
     * Used by admin user-detail basket view.
     */
    public function getComputedPrice(array $franchiseNames = [], ?string $gameTitle = null): ?array
    {
        // Use the first platform ID available, or 0 as a generic fallback
        $platformIds = json_decode($this->platform_ids ?? '[]', true) ?? [];
        $platformId  = (int) ($platformIds[0] ?? 0);
        return $this->getComputedPriceForPlatform($platformId, $franchiseNames, $gameTitle);
    }
}

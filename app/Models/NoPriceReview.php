<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoPriceReview extends Model
{
    public $timestamps  = false;
    public $incrementing = true;

    protected $fillable = ['igdb_game_id', 'platform_id'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Remove games from an IGDB result array when ALL their tracked platforms
     * are in the no_price_reviews queue (i.e. no price found for any platform).
     */
    public static function strip(array $igdbGames): array
    {
        if (empty($igdbGames)) {
            return [];
        }

        if (! \Illuminate\Support\Facades\Schema::hasTable('no_price_reviews')) {
            return $igdbGames;
        }

        $ids = array_column($igdbGames, 'id');

        // Get all no-price game IDs from the DB in one query
        $noPriceIds = static::whereIn('igdb_game_id', $ids)
            ->distinct()
            ->pluck('igdb_game_id')
            ->flip()
            ->all();

        if (empty($noPriceIds)) {
            return $igdbGames;
        }

        // Only strip a game if it has NO price data at all
        // (i.e. it's in no_price_reviews AND has no override set)
        $hasOverrideIds = GamePrice::whereIn('igdb_game_id', array_keys($noPriceIds))
            ->where(function ($q) {
                $q->whereNotNull('price_overrides')
                  ->where('price_overrides', '!=', 'null')
                  ->where('price_overrides', '!=', '{}');
            })
            ->pluck('igdb_game_id')
            ->flip()
            ->all();

        return array_values(array_filter($igdbGames, function ($game) use ($noPriceIds, $hasOverrideIds) {
            $id = (int) $game['id'];
            if (! isset($noPriceIds[$id])) {
                return true; // has price — keep
            }
            return isset($hasOverrideIds[$id]); // keep only if has override
        }));
    }
}

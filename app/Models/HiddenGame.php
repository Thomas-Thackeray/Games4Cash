<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HiddenGame extends Model
{
    protected $fillable = ['igdb_game_id'];

    /**
     * Remove hidden games from an IGDB result array.
     * One DB query regardless of array size.
     */
    public static function strip(array $igdbGames): array
    {
        if (empty($igdbGames)) {
            return [];
        }

        $ids       = array_column($igdbGames, 'id');
        $hiddenIds = static::whereIn('igdb_game_id', $ids)
            ->pluck('igdb_game_id')
            ->flip()
            ->all();

        return empty($hiddenIds)
            ? $igdbGames
            : array_values(array_filter($igdbGames, fn ($g) => ! isset($hiddenIds[$g['id']])));
    }
}

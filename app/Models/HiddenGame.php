<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class HiddenGame extends Model
{
    protected $fillable = ['igdb_game_id', 'platform_id'];

    /**
     * Returns [igdb_game_id => [platform_id, ...]] for the given game IDs.
     */
    public static function hiddenMapForGames(array $igdbIds): array
    {
        if (empty($igdbIds) || ! Schema::hasTable('hidden_games')) {
            return [];
        }

        $map = [];
        static::whereIn('igdb_game_id', $igdbIds)
            ->get(['igdb_game_id', 'platform_id'])
            ->each(function ($row) use (&$map) {
                $map[$row->igdb_game_id][] = (int) $row->platform_id;
            });

        return $map;
    }

    /**
     * Remove games from an IGDB result array when ALL their platforms are hidden.
     * Games with only some platforms hidden still appear (prices just won't show
     * for the hidden platform on the detail page).
     */
    public static function strip(array $igdbGames): array
    {
        if (empty($igdbGames)) {
            return [];
        }

        if (! Schema::hasTable('hidden_games')) {
            return $igdbGames;
        }

        $ids       = array_column($igdbGames, 'id');
        $hiddenMap = static::hiddenMapForGames($ids);

        if (empty($hiddenMap)) {
            return $igdbGames;
        }

        return array_values(array_filter($igdbGames, function ($game) use ($hiddenMap) {
            $gameId = (int) $game['id'];

            if (! isset($hiddenMap[$gameId])) {
                return true; // no hidden platforms → keep
            }

            $gamePlatforms   = array_map('intval', array_column($game['platforms'] ?? [], 'id'));
            $hiddenPlatforms = $hiddenMap[$gameId];

            if (empty($gamePlatforms)) {
                return false; // hidden and no platform data → remove
            }

            // Keep if at least one platform is NOT hidden
            foreach ($gamePlatforms as $pid) {
                if (! in_array($pid, $hiddenPlatforms, true)) {
                    return true;
                }
            }

            return false; // every platform is hidden → remove
        }));
    }
}

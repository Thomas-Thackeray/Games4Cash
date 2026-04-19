<?php

namespace App\Http\Controllers;

use App\Models\GamePrice;
use App\Models\HiddenGame;
use App\Models\NoPriceReview;
use App\Services\IgdbService;
use App\Services\PriceSyncService;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class GenresController extends Controller
{
    public function index(): View
    {
        $genresConfig  = config('igdb.genres');
        $genreDesc     = config('igdb.genre_descriptions');
        $igdb          = new IgdbService();
        $genreGames    = [];

        foreach ($genresConfig as $gName => $gId) {
            $cacheKey = 'genres_overview_' . $gId;

            $games = Cache::remember($cacheKey, now()->addHours(6), function () use ($igdb, $gId) {
                try {
                    return $igdb->getGamesByGenre($gId, 6);
                } catch (\Throwable) {
                    return [];
                }
            });

            $games = GamePrice::stripFreeGames($games);
            $games = HiddenGame::strip($games);
            $games = NoPriceReview::strip($games);
            PriceSyncService::ensureForGames($games);

            $priced = array_filter($games, function ($game) {
                $gp = GamePrice::where('igdb_game_id', $game['id'])->first();
                if (! $gp) return false;
                $pricing = $gp->getComputedPrice(array_column($game['franchises'] ?? [], 'name'), $game['name'] ?? '');
                return $pricing && ! $pricing['is_free'];
            });

            $genreGames[$gName] = array_values(array_slice($priced, 0, 5));
        }

        return view('genres', compact('genresConfig', 'genreDesc', 'genreGames'));
    }
}

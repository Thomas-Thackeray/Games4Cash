<?php

namespace App\Http\Controllers;

use App\Models\GamePrice;
use App\Services\ActivityLogger;
use App\Services\IgdbService;
use App\Services\PriceSyncService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $query     = trim($request->input('q', ''));
        $genre     = $request->input('genre', '');
        $franchise = $request->input('franchise', '');
        $minPrice  = $request->input('min_price', '');
        $maxPrice  = $request->input('max_price', '');
        $page      = max(1, (int) $request->input('page', 1));
        $limit     = 24;
        $offset    = ($page - 1) * $limit;

        $hasPriceFilter = $minPrice !== '' || $maxPrice !== '';

        // Log only on page 1 to avoid pagination noise
        if ($page === 1) {
            if ($query !== '') {
                ActivityLogger::search('Searched for "' . $query . '"', $request);
            } elseif ($genre !== '') {
                ActivityLogger::filter('Filtered by genre ID: ' . $genre, $request);
            } elseif ($franchise !== '') {
                ActivityLogger::filter('Filtered by franchise: ' . $franchise, $request);
            } elseif ($hasPriceFilter) {
                ActivityLogger::filter('Filtered by price: £' . ($minPrice ?: '0') . '–£' . ($maxPrice ?: '60+'), $request);
            }
        }

        $games = [];
        $error = null;

        try {
            $igdb = new IgdbService();

            if ($query !== '') {
                $games = $igdb->searchGames($query, $limit, $offset);
            } elseif ($genre !== '') {
                $games = $igdb->getGamesByGenre((int) $genre, $limit, $offset);
            } elseif ($franchise !== '') {
                $games = $igdb->getGamesByFranchise($franchise, $limit, $offset);
            } elseif ($hasPriceFilter) {
                $priceQuery = GamePrice::where('is_free', false)->whereNotNull('steam_gbp');
                if ($minPrice !== '') {
                    $priceQuery->where('steam_gbp', '>=', (float) $minPrice);
                }
                if ($maxPrice !== '' && (float) $maxPrice < 60) {
                    $priceQuery->where('steam_gbp', '<=', (float) $maxPrice);
                }
                $gameIds = $priceQuery->pluck('igdb_game_id')->toArray();
                $games   = $igdb->getGamesByIds($gameIds, $limit, $offset);
            } else {
                $games = $igdb->getTrendingGames($limit);
            }

        } catch (\RuntimeException $e) {
            $error = $e->getMessage();
        }

        PriceSyncService::ensureForGames($games);

        return view('search', compact('games', 'query', 'genre', 'franchise', 'minPrice', 'maxPrice', 'page', 'limit', 'error'));
    }
}

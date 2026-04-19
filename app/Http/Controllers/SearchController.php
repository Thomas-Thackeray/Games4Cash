<?php

namespace App\Http\Controllers;

use App\Models\GamePrice;
use App\Models\HiddenGame;
use App\Models\NoPriceReview;
use App\Services\ActivityLogger;
use App\Services\IgdbService;
use App\Services\PriceSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $query     = trim($request->input('q') ?? '');
        $franchise = $request->input('franchise') ?? '';
        $page      = max(1, (int) $request->input('page', 1));
        $limit     = 24;
        $offset    = ($page - 1) * $limit;

        // Log only on page 1, and skip if already logged as security
        if ($page === 1 && !$request->attributes->get('security_logged')) {
            if ($query !== '') {
                ActivityLogger::search('Searched for "' . $query . '"', $request);
            } elseif ($franchise !== '') {
                ActivityLogger::filter('Filtered by franchise: ' . $franchise, $request);
            }
        }

        $games = [];
        $error = null;

        try {
            $igdb = new IgdbService();

            if ($query !== '') {
                // User-specific search — not cached (every query is unique)
                $games = $igdb->searchGames($query, $limit, $offset);
            } elseif ($franchise !== '') {
                // Franchise browse — same results for everyone, cache 10 min
                $cacheKey = 'search_franchise_' . md5($franchise) . '_p' . $page;
                $games = Cache::remember($cacheKey, now()->addMinutes(10), fn () => $igdb->getGamesByFranchise($franchise, $limit, $offset));
            } else {
                // Trending page — same for everyone, cache 10 min
                $games = Cache::remember('search_trending_p' . $page, now()->addMinutes(10), fn () => $igdb->getTrendingGames($limit));
            }

        } catch (\RuntimeException $e) {
            $error = $e->getMessage();
        }

        $games = GamePrice::stripFreeGames($games);
        $games = HiddenGame::strip($games);
        $games = NoPriceReview::strip($games);
        PriceSyncService::ensureForGames($games);

        return view('search', compact('games', 'query', 'franchise', 'page', 'limit', 'error'));
    }
}

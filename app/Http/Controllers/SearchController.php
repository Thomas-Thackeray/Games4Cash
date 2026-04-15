<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogger;
use App\Services\IgdbService;
use App\Services\PriceSyncService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $query  = trim($request->input('q', ''));
        $sort   = $request->input('sort', 'trending');
        $page   = max(1, (int) $request->input('page', 1));
        $limit  = 24;
        $offset = ($page - 1) * $limit;

        // Log only on page 1 to avoid pagination noise
        if ($page === 1) {
            if ($query !== '') {
                ActivityLogger::search('Searched for "' . $query . '"', $request);
            } elseif ($sort !== 'trending') {
                $labels = [
                    'top_rated' => 'Top Rated',
                    'recent'    => 'New Releases',
                    'upcoming'  => 'Upcoming',
                ];
                ActivityLogger::filter('Browsed games sorted by: ' . ($labels[$sort] ?? $sort), $request);
            }
        }

        $games = [];
        $error = null;

        try {
            $igdb = new IgdbService();

            if ($query !== '') {
                $games = $igdb->searchGames($query, $limit, $offset);
            } else {
                $games = match ($sort) {
                    'top_rated' => $igdb->getTopRated($limit),
                    'recent'    => $igdb->getRecentGames($limit),
                    'upcoming'  => $igdb->getUpcomingGames($limit),
                    default     => $igdb->getTrendingGames($limit),
                };
            }

        } catch (\RuntimeException $e) {
            $error = $e->getMessage();
        }

        PriceSyncService::ensureForGames($games);

        return view('search', compact('games', 'query', 'sort', 'page', 'limit', 'error'));
    }
}

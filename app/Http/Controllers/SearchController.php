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
                $games = $igdb->searchGames($query, $limit, $offset);
            } elseif ($franchise !== '') {
                $games = $igdb->getGamesByFranchise($franchise, $limit, $offset);
            } else {
                $games = $igdb->getTrendingGames($limit);
            }

        } catch (\RuntimeException $e) {
            $error = $e->getMessage();
        }

        PriceSyncService::ensureForGames($games);

        return view('search', compact('games', 'query', 'franchise', 'page', 'limit', 'error'));
    }
}

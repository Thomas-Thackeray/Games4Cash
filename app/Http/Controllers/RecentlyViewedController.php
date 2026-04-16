<?php

namespace App\Http\Controllers;

use App\Services\IgdbService;
use Illuminate\View\View;

class RecentlyViewedController extends Controller
{
    public function index(): View
    {
        $viewedIds = session('recently_viewed', []);
        $games     = [];

        if (!empty($viewedIds)) {
            try {
                $igdb    = new IgdbService();
                $fetched = $igdb->getGamesByIds($viewedIds, 20);
                // Re-sort to match session order (most recent first)
                $indexed = array_column($fetched, null, 'id');
                $games   = array_values(array_filter(
                    array_map(fn($id) => $indexed[$id] ?? null, $viewedIds)
                ));
            } catch (\Throwable) {
                $games = [];
            }
        }

        $wishlistIds = auth()->user()->wishlistItems()->pluck('igdb_game_id')->toArray();

        return view('recently-viewed', compact('games', 'wishlistIds'));
    }
}

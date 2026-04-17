<?php

namespace App\Http\Controllers;

use App\Models\GamePrice;
use App\Models\HiddenGame;
use App\Models\NoPriceReview;
use App\Services\IgdbService;
use App\Services\PriceSyncService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $igdb        = new IgdbService();
        $viewedIds   = session('recently_viewed', []);
        $sectionTitle = 'Recently Viewed';

        try {
            if (!empty($viewedIds)) {
                $fetched = $igdb->getGamesByIds($viewedIds, 20);
                // Re-sort to match session order (most recent first)
                $indexed = array_column($fetched, null, 'id');
                $games   = array_values(array_filter(
                    array_map(fn($id) => $indexed[$id] ?? null, $viewedIds)
                ));
            } else {
                $games        = $igdb->getRandomGames(20);
                $sectionTitle = 'Discover Games';
            }
        } catch (\Throwable) {
            $games        = [];
            $sectionTitle = 'Discover Games';
        }

        $games = GamePrice::stripFreeGames($games);
        $games = HiddenGame::strip($games);
        $games = NoPriceReview::strip($games);
        PriceSyncService::ensureForGames($games);

        return view('home', compact('games', 'sectionTitle'));
    }
}

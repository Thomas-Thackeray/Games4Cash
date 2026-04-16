<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogger;
use App\Services\IgdbService;
use App\Services\PriceSyncService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GenreController extends Controller
{
    public function show(Request $request, int $id, string $name): View
    {
        $page      = max(1, (int) $request->input('page', 1));
        $limit     = 24;
        $offset    = ($page - 1) * $limit;
        $genreName = html_entity_decode($name, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if ($page === 1 && !$request->attributes->get('security_logged')) {
            ActivityLogger::filter('Browsed genre: ' . $genreName, $request);
        }

        $games = [];
        $error = null;

        try {
            $igdb  = new IgdbService();
            $games = $igdb->getGamesByGenre($id, $limit, $offset);
        } catch (\RuntimeException $e) {
            $error = $e->getMessage();
        }

        PriceSyncService::ensureForGames($games);

        return view('genre', compact('games', 'error', 'id', 'genreName', 'page', 'limit'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\GamePrice;
use App\Models\SnakeScore;
use App\Services\IgdbService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SnakeController extends Controller
{
    public function index(): View
    {
        $scores = SnakeScore::orderByDesc('score')->orderBy('created_at')->limit(10)->get();

        // Recently viewed games (same logic as home page)
        $recentGames = [];
        $viewedIds   = session('recently_viewed', []);
        if (!empty($viewedIds)) {
            try {
                $igdb    = new IgdbService();
                $fetched = $igdb->getGamesByIds($viewedIds, 8);
                $indexed = array_column($fetched, null, 'id');
                $recentGames = array_values(array_filter(
                    array_map(fn ($id) => $indexed[$id] ?? null, $viewedIds)
                ));
                $recentGames = GamePrice::stripFreeGames($recentGames);
            } catch (\Throwable) {
                $recentGames = [];
            }
        }

        return view('snake', compact('scores', 'recentGames'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'min:1', 'max:30'],
            'score' => ['required', 'integer', 'min:1', 'max:99999'],
        ]);

        $data['name'] = strip_tags(trim($data['name']));

        $entry = SnakeScore::create($data);

        // What rank is this score?
        $rank = SnakeScore::where('score', '>', $entry->score)->count() + 1;

        $top10 = SnakeScore::orderByDesc('score')->orderBy('created_at')->limit(10)->get()
            ->map(fn ($s) => [
                'id'    => $s->id,
                'name'  => $s->name,
                'score' => $s->score,
                'date'  => $s->created_at->format('d M Y'),
            ]);

        return response()->json([
            'rank'   => $rank,
            'top10'  => $top10,
        ]);
    }
}

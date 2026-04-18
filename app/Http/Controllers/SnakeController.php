<?php

namespace App\Http\Controllers;

use App\Models\SnakeScore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SnakeController extends Controller
{
    public function index(): View
    {
        $scores = SnakeScore::orderByDesc('score')->orderBy('created_at')->limit(10)->get();

        return view('snake', compact('scores'));
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

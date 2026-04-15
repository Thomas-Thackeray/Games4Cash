<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(): View
    {
        $items = auth()->user()->wishlistItems()->latest('created_at')->get();

        return view('wishlist', compact('items'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'igdb_game_id' => ['required', 'integer', 'min:1'],
            'game_title'   => ['required', 'string', 'max:255'],
            'cover_url'    => ['nullable', 'string', 'max:500'],
            'steam_app_id' => ['nullable', 'integer', 'min:1'],
            'release_date' => ['nullable', 'integer'],
        ]);

        $user = auth()->user();

        if ($user->wishlistItems()->where('igdb_game_id', $request->igdb_game_id)->exists()) {
            return back()->with('flash_error', '"' . $request->game_title . '" is already in your wishlist.');
        }

        $user->wishlistItems()->create([
            'igdb_game_id' => $request->igdb_game_id,
            'game_title'   => $request->game_title,
            'cover_url'    => $request->cover_url,
            'steam_app_id' => $request->steam_app_id,
            'release_date' => $request->release_date,
        ]);

        return back()->with('flash_success', '"' . $request->game_title . '" added to your wishlist.');
    }

    public function destroy(int $igdbGameId): RedirectResponse
    {
        auth()->user()->wishlistItems()->where('igdb_game_id', $igdbGameId)->delete();

        return back()->with('flash_success', 'Game removed from your wishlist.');
    }
}

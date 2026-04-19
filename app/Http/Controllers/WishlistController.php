<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        // Remove any wishlist entries whose custom game has been deleted
        $user->wishlistItems()
            ->whereNotNull('custom_game_id')
            ->whereDoesntHave('customGame')
            ->delete();

        $items = $user->wishlistItems()->with('customGame')->latest('created_at')->get();

        return view('wishlist', compact('items'));
    }

    public function store(Request $request): RedirectResponse
    {
        // Custom game wishlist
        if ($request->filled('custom_game_id')) {
            $request->validate([
                'custom_game_id' => ['required', 'integer', 'min:1'],
                'game_title'     => ['required', 'string', 'max:255'],
                'cover_url'      => ['nullable', 'string', 'max:500'],
            ]);

            $user = auth()->user();

            if ($user->wishlistItems()->where('custom_game_id', $request->custom_game_id)->exists()) {
                return back()->with('flash_error', '"' . $request->game_title . '" is already in your wishlist.');
            }

            $user->wishlistItems()->create([
                'custom_game_id' => $request->custom_game_id,
                'game_title'     => $request->game_title,
                'cover_url'      => $request->cover_url,
            ]);

            return back()->with('flash_success', '"' . $request->game_title . '" added to your wishlist.');
        }

        // IGDB game wishlist
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

    public function destroyCustom(int $customGameId): RedirectResponse
    {
        auth()->user()->wishlistItems()->where('custom_game_id', $customGameId)->delete();

        return back()->with('flash_success', 'Game removed from your wishlist.');
    }
}

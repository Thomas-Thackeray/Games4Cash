<?php

namespace App\Http\Controllers;

use App\Models\CashBasketItem;
use App\Models\PriceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PriceRequestController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'igdb_game_id' => ['required', 'integer', 'min:1'],
            'platform_id'  => ['nullable', 'integer', 'min:1'],
            'game_title'   => ['required', 'string', 'max:255'],
            'cover_url'    => ['nullable', 'string', 'max:500'],
            'slug'         => ['nullable', 'string', 'max:255'],
        ]);

        if (! Schema::hasTable('price_requests')) {
            return back()->with('flash_error', 'Price requests are not available yet.');
        }

        $user       = auth()->user();
        $gameId     = (int) $request->igdb_game_id;
        $platformId = $request->platform_id ? (int) $request->platform_id : null;

        // Avoid duplicate pending requests for the same game+platform
        $exists = PriceRequest::where('user_id', $user->id)
            ->where('igdb_game_id', $gameId)
            ->where('status', 'pending')
            ->when($platformId, fn ($q) => $q->where('platform_id', $platformId))
            ->exists();

        if (! $exists) {
            PriceRequest::create([
                'user_id'      => $user->id,
                'igdb_game_id' => $gameId,
                'platform_id'  => $platformId,
                'game_title'   => $request->game_title,
                'cover_url'    => $request->cover_url,
                'slug'         => $request->slug,
            ]);
        }

        // Also add to cash basket if not already there
        $inBasket = $user->cashBasketItems()
            ->where('igdb_game_id', $gameId)
            ->where(function ($q) use ($platformId) {
                $platformId !== null
                    ? $q->where('platform_id', $platformId)
                    : $q->whereNull('platform_id');
            })
            ->exists();

        if (! $inBasket) {
            $user->cashBasketItems()->create([
                'igdb_game_id' => $gameId,
                'platform_id'  => $platformId,
                'game_title'   => $request->game_title,
                'cover_url'    => $request->cover_url,
                'steam_app_id' => null,
                'release_date' => null,
            ]);
        }

        return back()->with('flash_success', 'Price requested for "' . $request->game_title . '". We\'ll review it shortly and it will appear in your basket once priced.');
    }
}

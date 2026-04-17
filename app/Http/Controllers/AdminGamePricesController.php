<?php

namespace App\Http\Controllers;

use App\Models\GamePrice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminGamePricesController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->input('search', ''));

        $query = GamePrice::whereNotNull('platform_ids')
            ->where('platform_ids', '!=', '[]');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('game_title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $gamePrices   = $query->orderBy('game_title')->orderBy('slug')->paginate(30)->withQueryString();
        $allPlatforms = config('igdb.all_platforms');

        return view('admin.game-prices', compact('gamePrices', 'search', 'allPlatforms'));
    }

    public function updateOverride(Request $request, int $igdbGameId, int $platformId): JsonResponse
    {
        $request->validate([
            'price' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
        ]);

        $gamePrice = GamePrice::where('igdb_game_id', $igdbGameId)->firstOrFail();

        $overrides = $gamePrice->price_overrides ?? [];
        $price     = $request->input('price');

        if ($price === null || $price === '') {
            unset($overrides[$platformId]);
        } else {
            $overrides[$platformId] = round((float) $price, 2);
        }

        $gamePrice->price_overrides = empty($overrides) ? null : $overrides;
        $gamePrice->save();

        // Return the new computed price for this platform so the UI can update
        $result = $gamePrice->adminPriceForPlatform($platformId);

        return response()->json([
            'display_price' => $result['display_price'] ?? '—',
            'source'        => $result['source'] ?? null,
            'override_set'  => isset($overrides[$platformId]),
        ]);
    }
}

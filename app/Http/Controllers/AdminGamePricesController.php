<?php

namespace App\Http\Controllers;

use App\Models\GamePrice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminGamePricesController extends Controller
{
    public function index(Request $request): \Illuminate\Contracts\View\View
    {
        try {
            $search = trim($request->input('search', ''));

            $query = GamePrice::whereNotNull('platform_ids')
                ->where('platform_ids', '!=', '[]');

            $hasGameTitle = \Illuminate\Support\Facades\Schema::hasColumn('game_prices', 'game_title');

            if ($search !== '') {
                $query->where(function ($q) use ($search, $hasGameTitle) {
                    if ($hasGameTitle) {
                        $q->where('game_title', 'like', "%{$search}%")
                          ->orWhere('slug', 'like', "%{$search}%");
                    } else {
                        $q->where('slug', 'like', "%{$search}%");
                    }
                });
            }

            $gamePrices = $hasGameTitle
                ? $query->orderBy('game_title')->orderBy('slug')->paginate(30)->withQueryString()
                : $query->orderBy('slug')->paginate(30)->withQueryString();
            $allPlatforms = config('igdb.all_platforms');

            return view('admin.game-prices', compact('gamePrices', 'search', 'allPlatforms'));

        } catch (\Throwable $e) {
            return view('admin.error-debug', [
                'error'   => $e->getMessage(),
                'file'    => $e->getFile() . ':' . $e->getLine(),
                'context' => 'admin/game-prices index',
            ]);
        }
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

        $newOverrides = empty($overrides) ? null : $overrides;

        try {
            $gamePrice->price_overrides = $newOverrides;
            $gamePrice->save();
        } catch (\Throwable) {
            // price_overrides column may not exist until migration runs
        }

        // Return the new computed price for this platform so the UI can update
        $result = $gamePrice->adminPriceForPlatform($platformId);

        return response()->json([
            'display_price' => $result['display_price'] ?? '—',
            'source'        => $result['source'] ?? null,
            'override_set'  => isset($newOverrides[$platformId]),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\GamePrice;
use App\Models\HiddenGame;
use App\Services\IgdbService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminGamePricesController extends Controller
{
    public function index(Request $request): \Illuminate\Contracts\View\View
    {
        try {
            $search = trim($request->input('search', ''));
            $source = $request->input('source', ''); // cex|cheapshark|steam|base|none

            $query = GamePrice::whereNotNull('platform_ids')
                ->where('platform_ids', '!=', '[]')
                ->where('is_free', false);

            $hasGameTitle      = \Illuminate\Support\Facades\Schema::hasColumn('game_prices', 'game_title');
            $hasPriceOverrides = \Illuminate\Support\Facades\Schema::hasColumn('game_prices', 'price_overrides');

            $hasHiddenTable = \Illuminate\Support\Facades\Schema::hasTable('hidden_games');
            // IDs of games that have at least one hidden platform row
            $gamesWithHiddenRows = $hasHiddenTable
                ? HiddenGame::distinct()->pluck('igdb_game_id')->all()
                : [];

            // Source filter — "hidden" tab shows games with any hidden row;
            // all other tabs show everything (hidden rows are dimmed in the view).
            match ($source) {
                'cheapshark' => $query->whereNotNull('cheapshark_usd'),
                'steam'      => $query->whereNotNull('steam_gbp')->whereNull('cheapshark_usd'),
                'none'       => $query->where(function ($q) {
                                    $q->where('is_free', true)
                                      ->orWhere(function ($q2) {
                                          $q2->whereNull('steam_gbp')->whereNull('cheapshark_usd');
                                      });
                                }),
                'override'   => $query->when($hasPriceOverrides, fn ($q) =>
                                    $q->whereNotNull('price_overrides')
                                      ->where('price_overrides', '!=', 'null')
                                      ->where('price_overrides', '!=', '{}')),
                'hidden'     => $query->whereIn('igdb_game_id', $gamesWithHiddenRows),
                'over10'     => $query->where(function ($q) {
                                    // Work backwards through the discount to find the raw price
                                    // threshold that would produce a ~£10 calculated offer.
                                    // computed = raw * (1 - discount%) so raw > 10 / (1 - discount%)
                                    $discountPct = (float) \App\Models\Setting::get('pricing_discount_percent', 85);
                                    $factor      = max(0.01, 1 - ($discountPct / 100));
                                    $steamMin    = round(10.0 / $factor, 2);
                                    $usdToGbp    = (float) \App\Models\Setting::get('usd_to_gbp_rate', 1.36);
                                    $csMin       = round($steamMin * $usdToGbp, 2);
                                    $q->where('steam_gbp', '>', $steamMin)
                                      ->orWhere('cheapshark_usd', '>', $csMin);
                                }),
                default      => null,
            };

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

            // Backfill missing names for games on this page only (keeps request fast)
            $this->backfillNamesForPage($gamePrices->items());

            $allPlatforms = config('igdb.all_platforms');

            // Build [igdb_game_id => [platform_id, ...]] for the current page only
            $pageIds   = array_column($gamePrices->items(), 'igdb_game_id');
            $hiddenMap = HiddenGame::hiddenMapForGames($pageIds);

            return view('admin.game-prices', compact('gamePrices', 'search', 'source', 'allPlatforms', 'hiddenMap'));

        } catch (\Throwable $e) {
            return view('admin.error-debug', [
                'error'   => $e->getMessage(),
                'file'    => $e->getFile() . ':' . $e->getLine(),
                'context' => 'admin/game-prices index',
            ]);
        }
    }

    /**
     * For any games on the current page that are missing both game_title and slug,
     * fetch their names from IGDB in a single batched query and persist them.
     * Updates the in-memory model instances so the view shows the name immediately.
     */
    private function backfillNamesForPage(array $items): void
    {
        $needsName = array_values(array_filter($items, fn ($gp) =>
            empty($gp->game_title) && empty($gp->slug)
        ));

        if (empty($needsName)) {
            return;
        }

        try {
            $ids   = array_column($needsName, 'igdb_game_id');
            $igdb  = new IgdbService();
            $games = $igdb->getGamesByIds($ids, count($ids));

            // Index results by IGDB ID for fast lookup
            $byId = [];
            foreach ($games as $g) {
                $byId[(int) $g['id']] = $g;
            }

            foreach ($needsName as $gp) {
                $g = $byId[$gp->igdb_game_id] ?? null;
                if (! $g) {
                    continue;
                }
                $title = $g['name'] ?? null;
                $slug  = $g['slug'] ?? null;
                $values = [];
                if ($title) $values['game_title'] = $title;
                if ($slug)  $values['slug']       = $slug;
                if (! empty($values)) {
                    GamePrice::where('igdb_game_id', $gp->igdb_game_id)->update($values);
                    // Update in-memory so the view shows it without a reload
                    if ($title) $gp->game_title = $title;
                    if ($slug)  $gp->slug       = $slug;
                }
            }
        } catch (\Throwable) {
            // IGDB unavailable — names stay as-is, will retry next page load
        }
    }

    public function toggleHide(int $igdbGameId, int $platformId): JsonResponse
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('hidden_games')) {
            return response()->json(['error' => 'Run php artisan migrate first.'], 503);
        }

        $existing = HiddenGame::where('igdb_game_id', $igdbGameId)
            ->where('platform_id', $platformId)
            ->first();

        if ($existing) {
            $existing->delete();
            $hidden = false;
        } else {
            HiddenGame::create(['igdb_game_id' => $igdbGameId, 'platform_id' => $platformId]);
            $hidden = true;
        }

        return response()->json(['hidden' => $hidden]);
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

    public function breakdown(int $igdbGameId, int $platformId): JsonResponse
    {
        $gp = GamePrice::where('igdb_game_id', $igdbGameId)->first();
        if (! $gp) {
            return response()->json(['error' => 'Game not found'], 404);
        }

        $data = $gp->getBreakdownForPlatform($platformId);
        if ($data === null) {
            return response()->json(['error' => 'No price data available for this game/platform'], 404);
        }

        return response()->json($data);
    }
}

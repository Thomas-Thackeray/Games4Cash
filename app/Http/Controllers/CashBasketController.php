<?php

namespace App\Http\Controllers;

use App\Models\CustomGame;
use App\Models\GamePrice;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CashBasketController extends Controller
{
    private const CONDITIONS = ['new', 'complete', 'disk'];

    public function index(): View
    {
        $items        = auth()->user()->cashBasketItems()->latest('created_at')->get();
        $allPlatforms = config('igdb.all_platforms');
        $modifiers    = $this->conditionModifiers();

        $total           = 0.0;
        $itemsWithPrices = $items->map(function ($item) use (&$total, $allPlatforms, $modifiers) {
            [$baseNumeric, $baseDisplay, $adjNumeric, $adjDisplay, $adjLabel] =
                $this->resolveItemPricing($item, $allPlatforms, $modifiers);

            if ($adjNumeric !== null) {
                $total += $adjNumeric;
            } elseif ($baseNumeric !== null && $item->condition !== null) {
                $total += $baseNumeric;
            }

            return [
                'id'               => $item->id,
                'igdb_game_id'     => $item->igdb_game_id,
                'game_title'       => $item->game_title,
                'cover_url'        => $item->cover_url,
                'platform_name'    => $item->platform_id ? ($allPlatforms[$item->platform_id] ?? null) : null,
                'base_price'       => $baseNumeric,
                'display_price'    => $adjDisplay ?? $baseDisplay,
                'price_numeric'    => $adjNumeric ?? $baseNumeric,
                'condition'        => $item->condition,
                'adjustment_label' => $adjLabel,
            ];
        });

        $totalFormatted   = '£' . number_format($total, 2);
        $allHaveCondition = $items->isNotEmpty() && $items->every(fn ($i) => $i->condition !== null);

        return view('cash-basket', compact('itemsWithPrices', 'totalFormatted', 'total', 'allHaveCondition'));
    }

    // -----------------------------------------------------------------------

    public function store(Request $request): RedirectResponse
    {
        $isCustom = $request->filled('custom_game_id');

        if ($isCustom) {
            $request->validate([
                'custom_game_id' => ['required', 'integer', 'min:1', 'exists:custom_games,id'],
                'platform_id'    => ['required', 'integer', 'min:1'],
                'game_title'     => ['required', 'string', 'max:255'],
                'cover_url'      => ['nullable', 'string', 'max:500'],
            ]);
        } else {
            $request->validate([
                'igdb_game_id' => ['required', 'integer', 'min:1'],
                'platform_id'  => ['nullable', 'integer', 'min:1'],
                'game_title'   => ['required', 'string', 'max:255'],
                'cover_url'    => ['nullable', 'string', 'max:500'],
                'steam_app_id' => ['nullable', 'integer', 'min:1'],
                'release_date' => ['nullable', 'integer'],
            ]);
        }

        $user       = auth()->user();
        $platformId = $request->input('platform_id') ? (int) $request->input('platform_id') : null;

        if ($isCustom) {
            $customGameId = (int) $request->input('custom_game_id');

            $duplicate = $user->cashBasketItems()
                ->where('custom_game_id', $customGameId)
                ->where(function ($q) use ($platformId) {
                    $platformId !== null
                        ? $q->where('platform_id', $platformId)
                        : $q->whereNull('platform_id');
                })
                ->exists();

            if ($duplicate) {
                return back()->with('flash_error', '"' . $request->game_title . '" is already in your cash basket.');
            }

            $user->cashBasketItems()->create([
                'custom_game_id' => $customGameId,
                'igdb_game_id'   => null,
                'platform_id'    => $platformId,
                'game_title'     => $request->game_title,
                'cover_url'      => $request->cover_url,
            ]);
        } else {
            $duplicate = $user->cashBasketItems()
                ->where('igdb_game_id', $request->igdb_game_id)
                ->where(function ($q) use ($platformId) {
                    $platformId !== null
                        ? $q->where('platform_id', $platformId)
                        : $q->whereNull('platform_id');
                })
                ->exists();

            if ($duplicate) {
                return back()->with('flash_error', '"' . $request->game_title . '" is already in your cash basket.');
            }

            // Block free-to-play games — no cash offer is possible
            $gamePrice = \App\Models\GamePrice::where('igdb_game_id', $request->igdb_game_id)->first();
            if ($gamePrice && $gamePrice->is_free) {
                return back()->with('flash_error', '"' . $request->game_title . '" is free to play and cannot be traded for cash.');
            }

            $user->cashBasketItems()->create([
                'igdb_game_id' => $request->igdb_game_id,
                'platform_id'  => $platformId,
                'game_title'   => $request->game_title,
                'cover_url'    => $request->cover_url,
                'steam_app_id' => $request->steam_app_id,
                'release_date' => $request->release_date,
            ]);
        }

        return back()->with('flash_success', '"' . $request->game_title . '" added to your cash basket.');
    }

    // -----------------------------------------------------------------------

    /**
     * AJAX — update condition for one item, return recalculated prices.
     */
    public function updateCondition(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'condition' => ['required', Rule::in(self::CONDITIONS)],
        ]);

        $item = auth()->user()->cashBasketItems()->findOrFail($id);
        $item->update(['condition' => $request->condition]);

        $allPlatforms = config('igdb.all_platforms');
        $modifiers    = $this->conditionModifiers();

        [, , $adjNumeric, $adjDisplay, $adjLabel] =
            $this->resolveItemPricing($item, $allPlatforms, $modifiers);

        // Recalculate full basket total
        $total = 0.0;
        foreach (auth()->user()->cashBasketItems()->get() as $bi) {
            [, , $n] = $this->resolveItemPricing($bi, $allPlatforms, $modifiers);
            if ($n !== null) {
                $total += $n;
            }
        }

        $allHaveCondition = auth()->user()->cashBasketItems()->whereNull('condition')->doesntExist();
        $minOrder         = (float) Setting::get('min_order_gbp', 20);

        return response()->json([
            'item_price'         => $adjDisplay,
            'adjustment_label'   => $adjLabel,
            'basket_total'       => '£' . number_format($total, 2),
            'basket_total_raw'   => round($total, 2),
            'all_have_condition' => $allHaveCondition,
            'min_order'          => $minOrder,
        ]);
    }

    // -----------------------------------------------------------------------

    public function destroy(int $id): RedirectResponse
    {
        auth()->user()->cashBasketItems()->findOrFail($id)->delete();

        return back()->with('flash_success', 'Game removed from your cash basket.');
    }

    // -----------------------------------------------------------------------

    /**
     * Returns [baseNumeric, baseDisplay, adjNumeric, adjDisplay, adjLabel].
     * adjNumeric/adjDisplay/adjLabel are null when condition is not set.
     */
    private function resolveItemPricing($item, array $allPlatforms, array $modifiers): array
    {
        $baseNumeric = null;
        $baseDisplay = null;

        if (!empty($item->custom_game_id)) {
            // Custom game — price set directly per platform
            $customGame = CustomGame::find($item->custom_game_id);
            if ($customGame && $item->platform_id) {
                $price = $customGame->priceForPlatform($item->platform_id);
                if ($price !== null) {
                    $baseNumeric = $price;
                    $baseDisplay = '£' . number_format($price, 2);
                }
            }
        } else {
            $pricing   = null;
            $gamePrice = GamePrice::where('igdb_game_id', $item->igdb_game_id)->first();

            if ($gamePrice) {
                try {
                    $pricing = $item->platform_id
                        ? $gamePrice->getComputedPriceForPlatform((int) $item->platform_id, [], $item->game_title)
                        : $gamePrice->getComputedPrice([], $item->game_title);
                } catch (\Throwable) {}
            }

            if (! $pricing || $pricing['is_free']) {
                return [null, null, null, null, null];
            }

            $baseNumeric = (float) $pricing['price_numeric'];
            $baseDisplay = $pricing['display_price'];
        }

        if ($baseNumeric === null) {
            return [null, null, null, null, null];
        }

        if ($item->condition === null) {
            return [$baseNumeric, $baseDisplay, null, null, null];
        }

        $pct        = $modifiers[$item->condition] ?? 0.0;
        $adjNumeric = max(0.01, round($baseNumeric * (1 + $pct / 100), 2));
        $adjDisplay = '£' . number_format($adjNumeric, 2);
        $adjLabel   = $this->conditionLabel($item->condition, $pct, $baseNumeric, $adjNumeric);

        return [$baseNumeric, $baseDisplay, $adjNumeric, $adjDisplay, $adjLabel];
    }

    private function conditionLabel(string $condition, float $pct, float $base, float $adjusted): string
    {
        $names = ['new' => 'Brand New', 'complete' => 'Complete Game', 'disk' => 'Just Disk'];
        $name  = $names[$condition] ?? $condition;

        if ($pct == 0) {
            return $name . ': standard rate';
        }

        $diff = abs(round($adjusted - $base, 2));

        return $pct > 0
            ? $name . ': +' . $pct . '% premium (£' . number_format($diff, 2) . ' added)'
            : $name . ': ' . abs($pct) . '% deducted (£' . number_format($diff, 2) . ' removed)';
    }

    private function conditionModifiers(): array
    {
        return [
            'new'      => (float) Setting::get('condition_new_pct', 20),
            'complete' => (float) Setting::get('condition_complete_pct', 0),
            'disk'     => (float) Setting::get('condition_disk_pct', -50),
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\GamePrice;
use App\Models\NoPriceReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminNoPriceController extends Controller
{
    public function index(): View
    {
        $platforms = config('igdb.all_platforms', []);

        // All game_prices rows where no effective price exists:
        // no steam price, no cheapshark price, no override, not free-to-play.
        $rows = GamePrice::where(function ($q) {
                $q->whereNull('steam_gbp')
                  ->whereNull('cheapshark_usd');
            })
            ->where(function ($q) {
                $q->whereNull('price_overrides')
                  ->orWhere('price_overrides', '')
                  ->orWhere('price_overrides', '{}')
                  ->orWhere('price_overrides', 'null');
            })
            ->where(function ($q) {
                $q->whereNull('is_free')
                  ->orWhere('is_free', false);
            })
            ->orderBy('updated_at', 'asc')
            ->paginate(30);

        // Also pull no_price_reviews for platform-level detail
        $igdbIds = $rows->pluck('igdb_game_id')->all();
        $nprPlatforms = [];
        if (! empty($igdbIds) && Schema::hasTable('no_price_reviews')) {
            $nprRows = NoPriceReview::whereIn('igdb_game_id', $igdbIds)->get(['igdb_game_id', 'platform_id']);
            foreach ($nprRows as $n) {
                $nprPlatforms[$n->igdb_game_id][] = $n->platform_id;
            }
        }

        return view('admin.no-price-review', compact('rows', 'platforms', 'nprPlatforms'));
    }

    public function setPrice(Request $request, int $igdbGameId): RedirectResponse
    {
        $request->validate([
            'platform_id' => ['required', 'integer', 'min:1'],
            'price'       => ['required', 'numeric', 'min:0.01', 'max:9999.99'],
        ]);

        $platformId = (int) $request->input('platform_id');
        $price      = round((float) $request->input('price'), 2);

        $gp = GamePrice::where('igdb_game_id', $igdbGameId)->firstOrFail();

        $overrides              = $gp->price_overrides ?? [];
        $overrides[$platformId] = $price;

        // Ensure the platform is in platform_ids
        $platformIds = json_decode($gp->platform_ids ?? '[]', true);
        if (! in_array($platformId, $platformIds)) {
            $platformIds[] = $platformId;
        }

        $gp->price_overrides = $overrides;
        $gp->platform_ids    = json_encode($platformIds);
        $gp->save();

        // Clear no_price_reviews entry for this platform
        if (Schema::hasTable('no_price_reviews')) {
            NoPriceReview::where('igdb_game_id', $igdbGameId)
                ->where('platform_id', $platformId)
                ->delete();
        }

        return back()->with('flash_success', 'Price override saved.');
    }

    public function dismiss(Request $request, int $igdbGameId): RedirectResponse
    {
        if (Schema::hasTable('no_price_reviews')) {
            $platformId = $request->input('platform_id');
            $query      = NoPriceReview::where('igdb_game_id', $igdbGameId);
            if ($platformId) {
                $query->where('platform_id', (int) $platformId);
            }
            $query->delete();
        }

        return back()->with('flash_success', 'Review entry dismissed.');
    }
}

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
        if (! Schema::hasTable('no_price_reviews')) {
            return view('admin.no-price-review', [
                'reviews'    => collect(),
                'gamePrices' => collect(),
                'platforms'  => [],
                'migrationPending' => true,
            ]);
        }

        $reviews = NoPriceReview::select('igdb_game_id')
            ->selectRaw('MIN(id) as id')
            ->selectRaw('MIN(created_at) as created_at')
            ->selectRaw('GROUP_CONCAT(platform_id) as platform_ids_csv')
            ->groupBy('igdb_game_id')
            ->orderBy('created_at')
            ->paginate(30);

        // Attach game price records for display
        $igdbIds    = $reviews->pluck('igdb_game_id')->all();
        $gamePrices = GamePrice::whereIn('igdb_game_id', $igdbIds)
            ->get(['igdb_game_id', 'game_title', 'slug', 'steam_app_id', 'price_overrides'])
            ->keyBy('igdb_game_id');

        $platforms = config('igdb.all_platforms', []);

        return view('admin.no-price-review', compact('reviews', 'gamePrices', 'platforms'));
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

        $overrides                = $gp->price_overrides ?? [];
        $overrides[$platformId]   = $price;
        $gp->price_overrides      = $overrides;
        $gp->save();

        // Remove this platform's review entry; if none left for this game, it's fully resolved
        NoPriceReview::where('igdb_game_id', $igdbGameId)
            ->where('platform_id', $platformId)
            ->delete();

        return back()->with('flash_success', 'Price override saved and game approved for listing.');
    }

    public function dismiss(Request $request, int $igdbGameId): RedirectResponse
    {
        $request->validate([
            'platform_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $platformId = $request->input('platform_id');

        $query = NoPriceReview::where('igdb_game_id', $igdbGameId);
        if ($platformId) {
            $query->where('platform_id', (int) $platformId);
        }
        $query->delete();

        return back()->with('flash_success', 'Review entry dismissed.');
    }
}

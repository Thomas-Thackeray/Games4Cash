<?php

namespace App\Http\Controllers;

use App\Models\GamePrice;
use App\Models\PriceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminPriceRequestController extends Controller
{
    public function index(): View
    {
        if (! Schema::hasTable('price_requests')) {
            return view('admin.price-requests', ['migrationPending' => true, 'groups' => []]);
        }

        // Group pending requests by igdb_game_id, collapse into one row per game
        $requests = PriceRequest::where('status', 'pending')
            ->orderBy('created_at')
            ->get();

        // Collapse by igdb_game_id → array of unique platform_ids per game
        $groups = [];
        foreach ($requests as $req) {
            $id = $req->igdb_game_id;
            if (! isset($groups[$id])) {
                $groups[$id] = [
                    'igdb_game_id' => $id,
                    'game_title'   => $req->game_title,
                    'cover_url'    => $req->cover_url,
                    'slug'         => $req->slug,
                    'platforms'    => [],
                    'request_ids'  => [],
                    'created_at'   => $req->created_at,
                    'user_count'   => 0,
                ];
            }
            $groups[$id]['request_ids'][] = $req->id;
            $groups[$id]['user_count']++;
            if ($req->platform_id && ! in_array($req->platform_id, $groups[$id]['platforms'])) {
                $groups[$id]['platforms'][] = $req->platform_id;
            }
        }

        $allPlatforms = config('igdb.all_platforms');

        return view('admin.price-requests', [
            'migrationPending' => false,
            'groups'           => array_values($groups),
            'allPlatforms'     => $allPlatforms,
        ]);
    }

    public function fulfill(Request $request, int $igdbGameId): RedirectResponse
    {
        $request->validate([
            'platform_id' => ['required', 'integer', 'min:1'],
            'price'       => ['required', 'numeric', 'min:0.01', 'max:999.99'],
        ]);

        $platformId = (int) $request->platform_id;
        $price      = round((float) $request->price, 2);

        // Save price override
        $gp = GamePrice::firstOrCreate(
            ['igdb_game_id' => $igdbGameId],
            ['platform_ids' => '[]', 'price_overrides' => '{}', 'franchise_names' => '[]']
        );

        $overrides              = json_decode($gp->price_overrides ?? '{}', true);
        $overrides[$platformId] = $price;

        // Ensure the platform appears in platform_ids
        $platformIds = json_decode($gp->platform_ids ?? '[]', true);
        if (! in_array($platformId, $platformIds)) {
            $platformIds[] = $platformId;
        }

        $gp->update([
            'price_overrides' => json_encode($overrides),
            'platform_ids'    => json_encode($platformIds),
        ]);

        // Mark all pending requests for this game as fulfilled
        PriceRequest::where('igdb_game_id', $igdbGameId)
            ->where('status', 'pending')
            ->update(['status' => 'fulfilled']);

        return redirect()->route('admin.price-requests')->with('flash_success', 'Price set and requests marked as fulfilled.');
    }

    public function dismiss(int $igdbGameId): RedirectResponse
    {
        PriceRequest::where('igdb_game_id', $igdbGameId)
            ->where('status', 'pending')
            ->update(['status' => 'dismissed']);

        return redirect()->route('admin.price-requests')->with('flash_success', 'Price requests dismissed.');
    }
}

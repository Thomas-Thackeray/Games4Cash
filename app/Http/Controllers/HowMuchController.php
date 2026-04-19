<?php

namespace App\Http\Controllers;

use App\Models\GamePrice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HowMuchController extends Controller
{
    public function index(): View
    {
        return view('how-much');
    }

    /**
     * AJAX search — returns up to 8 matching games with per-platform cash prices.
     */
    public function search(Request $request): JsonResponse
    {
        $q = trim($request->input('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $platforms   = config('igdb.all_platforms', []);
        $mainPlatforms = [];
        foreach (config('igdb.platforms', []) as $name => $data) {
            $mainPlatforms[$data['id']] = $name;
        }

        $results = GamePrice::where('game_title', 'like', '%' . $q . '%')
            ->whereNull('is_free')
            ->orWhere(function ($query) use ($q) {
                $query->where('game_title', 'like', '%' . $q . '%')
                      ->where('is_free', false);
            })
            ->orderByRaw('CASE WHEN game_title LIKE ? THEN 0 ELSE 1 END', [$q . '%'])
            ->orderBy('game_title')
            ->limit(8)
            ->get();

        $output = [];

        foreach ($results as $gp) {
            if ($gp->is_free) {
                continue;
            }

            $platformPrices = [];

            $platformIds = $gp->platform_ids ?? [];
            if (is_string($platformIds)) {
                $platformIds = json_decode($platformIds, true) ?? [];
            }

            foreach ($platformIds as $pid) {
                if (! isset($mainPlatforms[$pid])) {
                    continue;
                }
                try {
                    $pricing = $gp->getComputedPriceForPlatform((int) $pid, [], $gp->game_title);
                    if ($pricing && ! $pricing['is_free'] && $pricing['price_numeric'] > 0) {
                        $platformPrices[] = [
                            'platform_id'   => $pid,
                            'platform_name' => $mainPlatforms[$pid],
                            'display_price' => $pricing['display_price'],
                            'price_numeric' => $pricing['price_numeric'],
                        ];
                    }
                } catch (\Throwable) {
                    // skip platform if pricing fails
                }
            }

            if (empty($platformPrices)) {
                // Try generic price
                try {
                    $pricing = $gp->getComputedPrice([], $gp->game_title);
                    if ($pricing && ! $pricing['is_free'] && $pricing['price_numeric'] > 0) {
                        $platformPrices[] = [
                            'platform_id'   => null,
                            'platform_name' => 'Any Platform',
                            'display_price' => $pricing['display_price'],
                            'price_numeric' => $pricing['price_numeric'],
                        ];
                    }
                } catch (\Throwable) {}
            }

            if (empty($platformPrices)) {
                continue;
            }

            $output[] = [
                'igdb_game_id' => $gp->igdb_game_id,
                'game_title'   => $gp->game_title,
                'slug'         => $gp->slug,
                'game_url'     => $gp->slug ? route('game.show', $gp->slug) : null,
                'platforms'    => $platformPrices,
            ];
        }

        return response()->json($output);
    }
}

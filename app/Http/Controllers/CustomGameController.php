<?php

namespace App\Http\Controllers;

use App\Models\CustomGame;
use Illuminate\View\View;

class CustomGameController extends Controller
{
    public function show(string $slug): View
    {
        $game      = CustomGame::where('slug', $slug)->where('published', true)->firstOrFail();
        $platforms = config('igdb.all_platforms', []);

        // Build pricing rows — only platforms that have a price set
        $pricingRows = [];
        foreach ($platforms as $platformId => $platformName) {
            $price = $game->priceForPlatform($platformId);
            if ($price !== null) {
                $pricingRows[] = [
                    'platform_name' => $platformName,
                    'display_price' => '£' . number_format($price, 2),
                    'price_numeric' => $price,
                ];
            }
        }

        return view('custom-game', compact('game', 'pricingRows', 'platforms'));
    }
}

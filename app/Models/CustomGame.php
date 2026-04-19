<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomGame extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'summary',
        'cover_image_path',
        'developer',
        'publisher',
        'release_year',
        'mode',
        'genres',
        'platform_prices',
        'published',
    ];

    protected function casts(): array
    {
        return [
            'genres'          => 'array',
            'platform_prices' => 'array',
            'published'       => 'boolean',
        ];
    }

    /**
     * Return the cash trade-in price for the given platform (in pence → pounds).
     * Returns null if no price is set.
     */
    public function priceForPlatform(int|string $platformId): ?float
    {
        $prices = $this->platform_prices ?? [];
        $price  = $prices[(string) $platformId] ?? null;

        if ($price === null || $price === '' || (float) $price <= 0) {
            return null;
        }

        return (float) $price;
    }
}

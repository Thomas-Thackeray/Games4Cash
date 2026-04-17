<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceRequest extends Model
{
    protected $fillable = [
        'user_id',
        'igdb_game_id',
        'platform_id',
        'game_title',
        'cover_url',
        'slug',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

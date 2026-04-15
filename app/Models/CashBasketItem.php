<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashBasketItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'igdb_game_id',
        'platform_id',
        'condition',
        'game_title',
        'cover_url',
        'steam_app_id',
        'release_date',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wishlist extends Model
{
    public function customGame(): BelongsTo
    {
        return $this->belongsTo(CustomGame::class);
    }

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'igdb_game_id',
        'custom_game_id',
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameEvaluation extends Model
{
    protected $fillable = [
        'user_id',
        'game_title',
        'platform',
        'condition',
        'description',
        'image_paths',
        'status',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'image_paths' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

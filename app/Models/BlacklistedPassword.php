<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlacklistedPassword extends Model
{
    public $timestamps = false;

    protected $fillable = ['password', 'created_at'];

    protected $casts = ['created_at' => 'datetime'];

    public static function isBlacklisted(string $password): bool
    {
        return static::whereRaw('LOWER(password) = ?', [strtolower($password)])->exists();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewsletterSubscriber extends Model
{
    protected $fillable = [
        'email',
        'name',
        'token',
        'source',
        'subscribed_at',
        'unsubscribed_at',
    ];

    protected $casts = [
        'subscribed_at'   => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    public function isActive(): bool
    {
        return $this->unsubscribed_at === null;
    }

    public static function subscribe(string $email, string $name = '', string $source = 'footer'): self
    {
        return static::updateOrCreate(
            ['email' => strtolower(trim($email))],
            [
                'name'            => $name ?: null,
                'token'           => Str::random(64),
                'source'          => $source,
                'subscribed_at'   => now(),
                'unsubscribed_at' => null,
            ]
        );
    }

    public static function activeCount(): int
    {
        return static::whereNull('unsubscribed_at')->count();
    }
}

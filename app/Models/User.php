<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'first_name',
        'surname',
        'username',
        'email',
        'contact_number',
        'password',
        'role',
        'force_password_reset',
        'last_active_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'last_active_at'       => 'datetime',
            'password'             => 'hashed',
            'force_password_reset' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function loginAttempts(): HasMany
    {
        return $this->hasMany(LoginAttempt::class)->latest('created_at');
    }

    public function wishlistItems(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function cashBasketItems(): HasMany
    {
        return $this->hasMany(CashBasketItem::class);
    }

    public function cashOrders(): HasMany
    {
        return $this->hasMany(CashOrder::class);
    }
}

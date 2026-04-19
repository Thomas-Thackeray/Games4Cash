<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

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
        'two_factor_secret',
        'two_factor_confirmed_at',
        'referral_code',
        'referred_by_user_id',
        'referral_bonus_gbp',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $user) {
            if (empty($user->referral_code)) {
                do {
                    $code = strtoupper(Str::random(8));
                } while (static::where('referral_code', $code)->exists());
                $user->referral_code = $code;
            }
        });
    }

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'       => 'datetime',
            'last_active_at'          => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'password'                => 'hashed',
            'force_password_reset'    => 'boolean',
            'referral_bonus_gbp'      => 'decimal:2',
        ];
    }

    public function hasTwoFactorEnabled(): bool
    {
        return ! is_null($this->two_factor_confirmed_at);
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

    public function referredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by_user_id');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by_user_id');
    }

    public function referralLink(): string
    {
        return url('/ref/' . $this->referral_code);
    }
}

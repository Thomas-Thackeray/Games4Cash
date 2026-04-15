<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CashOrder extends Model
{
    protected $table = 'cash_orders';

    protected $fillable = [
        'order_ref',
        'user_id',
        'status',
        'items',
        'total_gbp',
        'admin_notes',
        'house_name_number',
        'address_line1',
        'address_line2',
        'address_line3',
        'city',
        'county',
        'postcode',
        'agreed_terms',
        'confirmed_contents',
    ];

    protected $casts = [
        'items'              => 'array',
        'total_gbp'          => 'decimal:2',
        'agreed_terms'       => 'boolean',
        'confirmed_contents' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a unique order reference like GC-A1B2C3D4.
     */
    public static function generateRef(): string
    {
        do {
            $ref = 'GC-' . strtoupper(Str::random(8));
        } while (self::where('order_ref', $ref)->exists());

        return $ref;
    }

    /**
     * Users may cancel a pending order within 2 hours of placing it.
     */
    public function canCancel(): bool
    {
        return $this->status === 'pending'
            && $this->created_at->diffInMinutes(now()) < 120;
    }

    /**
     * Minutes remaining in the cancellation window (0 if expired).
     */
    public function cancelMinutesRemaining(): int
    {
        return max(0, 120 - (int) $this->created_at->diffInMinutes(now()));
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending'   => 'Pending',
            'contacted' => 'Contacted',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default     => ucfirst($this->status),
        };
    }

    public function statusClass(): string
    {
        return match ($this->status) {
            'pending'   => 'status-badge--pending',
            'contacted' => 'status-badge--contacted',
            'completed' => 'status-badge--completed',
            'cancelled' => 'status-badge--cancelled',
            default     => '',
        };
    }
}

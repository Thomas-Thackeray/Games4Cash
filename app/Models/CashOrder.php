<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use App\Models\Setting;

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
     * Users may cancel a pending order within the configurable window.
     */
    public function canCancel(): bool
    {
        $window = (int) Setting::get('cancel_window_minutes', 120);
        return $this->status === 'pending'
            && $this->created_at->diffInMinutes(now()) < $window;
    }

    /**
     * Minutes remaining in the cancellation window (0 if expired).
     */
    public function cancelMinutesRemaining(): int
    {
        $window = (int) Setting::get('cancel_window_minutes', 120);
        return max(0, $window - (int) $this->created_at->diffInMinutes(now()));
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending'   => 'Booked',
            'contacted' => 'Collection Arranged',
            'collected' => 'Collected',
            'inspected' => 'Inspected',
            'completed' => 'Paid',
            'cancelled' => 'Cancelled',
            default     => ucfirst($this->status),
        };
    }

    public function statusClass(): string
    {
        return match ($this->status) {
            'pending'   => 'status-badge--pending',
            'contacted' => 'status-badge--contacted',
            'collected' => 'status-badge--contacted',
            'inspected' => 'status-badge--contacted',
            'completed' => 'status-badge--completed',
            'cancelled' => 'status-badge--cancelled',
            default     => '',
        };
    }

    /**
     * Returns the ordered tracking steps and which one is currently active.
     * Each step: ['label', 'description', 'done', 'active']
     */
    public function trackingSteps(): array
    {
        if ($this->status === 'cancelled') {
            return [];
        }

        $order = ['pending', 'contacted', 'collected', 'inspected', 'completed'];
        $pos   = array_search($this->status, $order);
        if ($pos === false) $pos = 0;

        $steps = [
            ['key' => 'pending',   'label' => 'Booked',              'desc' => 'Order received and confirmed.'],
            ['key' => 'contacted', 'label' => 'Collection Arranged', 'desc' => 'We\'ve been in touch to arrange collection.'],
            ['key' => 'collected', 'label' => 'Collected',           'desc' => 'Games collected from your address.'],
            ['key' => 'inspected', 'label' => 'Inspected',           'desc' => 'Games checked and prices confirmed.'],
            ['key' => 'completed', 'label' => 'Paid',                'desc' => 'Payment sent to you. All done!'],
        ];

        foreach ($steps as $i => &$step) {
            $step['done']   = $i < $pos;
            $step['active'] = $i === $pos;
        }

        return $steps;
    }
}

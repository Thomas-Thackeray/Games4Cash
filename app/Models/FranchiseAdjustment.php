<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FranchiseAdjustment extends Model
{
    protected $fillable = ['franchise_name', 'adjustment_gbp'];

    protected $casts = ['adjustment_gbp' => 'float'];

    /**
     * Return the total flat £ adjustment for a game given its franchise names.
     * Multiple franchises stack. Positive = add, negative = deduct.
     */
    public static function getAdjustment(array $franchiseNames): float
    {
        if (empty($franchiseNames)) {
            return 0.0;
        }

        $total = self::whereIn('franchise_name', $franchiseNames)->sum('adjustment_gbp');
        return (float) $total;
    }
}

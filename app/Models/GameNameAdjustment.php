<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameNameAdjustment extends Model
{
    protected $fillable = ['keyword', 'adjustment_gbp'];

    protected $casts = ['adjustment_gbp' => 'float'];

    /**
     * Return the total flat £ adjustment for a game given its title.
     * Any stored keyword that appears anywhere in the title (case-insensitive)
     * contributes its adjustment. Multiple matches stack.
     */
    public static function getAdjustment(string $gameTitle): float
    {
        if ($gameTitle === '') {
            return 0.0;
        }

        $total = 0.0;
        foreach (self::all() as $adj) {
            if (stripos($gameTitle, $adj->keyword) !== false) {
                $total += $adj->adjustment_gbp;
            }
        }

        return $total;
    }
}

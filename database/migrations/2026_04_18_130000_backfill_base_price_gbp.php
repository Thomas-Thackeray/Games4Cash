<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('game_prices', 'base_price_gbp')) {
            return;
        }

        // Read the stored exchange rate; fall back to 1.36 if settings table missing
        $usdToGbp = 1.36;
        try {
            $row = DB::table('settings')->where('key', 'usd_to_gbp_rate')->first();
            if ($row && is_numeric($row->value) && (float) $row->value > 0) {
                $usdToGbp = (float) $row->value;
            }
        } catch (\Throwable) {}

        // Backfill from CheapShark (priority) then Steam
        DB::table('game_prices')
            ->whereNotNull('cheapshark_usd')
            ->whereNull('base_price_gbp')
            ->update([
                'base_price_gbp' => DB::raw("ROUND(cheapshark_usd / {$usdToGbp}, 4)"),
            ]);

        DB::table('game_prices')
            ->whereNull('cheapshark_usd')
            ->whereNotNull('steam_gbp')
            ->whereNull('base_price_gbp')
            ->update([
                'base_price_gbp' => DB::raw('ROUND(steam_gbp, 4)'),
            ]);
    }

    public function down(): void
    {
        // Backfill is non-destructive; no rollback needed
    }
};

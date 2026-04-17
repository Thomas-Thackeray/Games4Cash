<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_prices', function (Blueprint $table) {
            $table->decimal('base_price_gbp', 8, 4)->nullable()->after('cheapshark_usd');
        });
    }

    public function down(): void
    {
        Schema::table('game_prices', function (Blueprint $table) {
            $table->dropColumn('base_price_gbp');
        });
    }
};

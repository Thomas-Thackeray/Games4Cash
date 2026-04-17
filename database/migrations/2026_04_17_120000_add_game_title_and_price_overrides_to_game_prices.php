<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_prices', function (Blueprint $table) {
            $table->string('game_title', 255)->nullable()->after('igdb_game_id');
            $table->json('price_overrides')->nullable()->after('cex_fetched_at');
        });
    }

    public function down(): void
    {
        Schema::table('game_prices', function (Blueprint $table) {
            $table->dropColumn(['game_title', 'price_overrides']);
        });
    }
};

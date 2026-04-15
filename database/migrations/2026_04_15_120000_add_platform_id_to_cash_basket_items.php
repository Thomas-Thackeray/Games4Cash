<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_basket_items', function (Blueprint $table) {
            // Drop old unique so the same game can appear once per platform
            $table->dropUnique(['user_id', 'igdb_game_id']);

            // Platform-specific basket entry (null = no specific platform)
            $table->unsignedInteger('platform_id')->nullable()->after('igdb_game_id');
        });
    }

    public function down(): void
    {
        Schema::table('cash_basket_items', function (Blueprint $table) {
            $table->dropColumn('platform_id');
            $table->unique(['user_id', 'igdb_game_id']);
        });
    }
};

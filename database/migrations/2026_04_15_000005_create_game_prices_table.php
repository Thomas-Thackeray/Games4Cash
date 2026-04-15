<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_prices', function (Blueprint $table) {
            $table->unsignedInteger('igdb_game_id')->primary();
            $table->unsignedInteger('steam_app_id')->nullable();
            $table->unsignedInteger('release_date')->nullable();
            $table->boolean('is_free')->default(false);
            // Raw prices stored so cards can recompute without API calls
            $table->decimal('steam_gbp', 10, 2)->nullable();
            $table->decimal('cheapshark_usd', 10, 4)->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_prices');
    }
};

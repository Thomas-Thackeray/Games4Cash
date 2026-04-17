<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_prices', function (Blueprint $table) {
            $table->json('cex_prices')->nullable()->after('slug');
            $table->timestamp('cex_fetched_at')->nullable()->after('cex_prices');
        });
    }

    public function down(): void
    {
        Schema::table('game_prices', function (Blueprint $table) {
            $table->dropColumn(['cex_prices', 'cex_fetched_at']);
        });
    }
};

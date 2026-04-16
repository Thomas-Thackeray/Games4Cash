<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_prices', function (Blueprint $table) {
            $table->json('franchise_names')->nullable()->after('platform_ids');
        });
    }

    public function down(): void
    {
        Schema::table('game_prices', function (Blueprint $table) {
            $table->dropColumn('franchise_names');
        });
    }
};

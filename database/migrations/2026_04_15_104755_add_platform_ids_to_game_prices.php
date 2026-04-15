<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_prices', function (Blueprint $table) {
            $table->text('platform_ids')->nullable()->after('release_date'); // JSON array of IGDB platform IDs
        });
    }

    public function down(): void
    {
        Schema::table('game_prices', function (Blueprint $table) {
            $table->dropColumn('platform_ids');
        });
    }
};

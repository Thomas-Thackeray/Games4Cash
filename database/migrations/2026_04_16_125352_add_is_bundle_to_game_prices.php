<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('game_prices', function (Blueprint $table) {
            $table->boolean('is_bundle')->default(false)->after('is_free');
        });
    }

    public function down(): void
    {
        Schema::table('game_prices', function (Blueprint $table) {
            $table->dropColumn('is_bundle');
        });
    }
};

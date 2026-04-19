<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_basket_items', function (Blueprint $table) {
            $table->unsignedInteger('igdb_game_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('cash_basket_items', function (Blueprint $table) {
            $table->unsignedInteger('igdb_game_id')->nullable(false)->change();
        });
    }
};

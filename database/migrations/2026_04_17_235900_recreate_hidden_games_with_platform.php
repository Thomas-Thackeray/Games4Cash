<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('hidden_games');

        Schema::create('hidden_games', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('igdb_game_id');
            $table->unsignedInteger('platform_id');
            $table->timestamps();
            $table->unique(['igdb_game_id', 'platform_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hidden_games');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_basket_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('igdb_game_id');
            $table->string('game_title');
            $table->string('cover_url', 500)->nullable();
            $table->unsignedInteger('steam_app_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['user_id', 'igdb_game_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_basket_items');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->integer('igdb_game_id');
            $table->integer('platform_id')->nullable();
            $table->string('game_title');
            $table->string('cover_url', 500)->nullable();
            $table->string('slug')->nullable();
            $table->enum('status', ['pending', 'fulfilled', 'dismissed'])->default('pending');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['igdb_game_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_requests');
    }
};

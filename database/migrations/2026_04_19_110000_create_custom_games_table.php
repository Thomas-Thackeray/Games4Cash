<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_games', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->string('cover_image_path')->nullable(); // stored in public disk
            $table->string('developer')->nullable();
            $table->string('publisher')->nullable();
            $table->unsignedSmallInteger('release_year')->nullable();
            $table->json('genres')->nullable();       // array of genre strings
            $table->json('platform_prices')->nullable(); // {platform_id: price_gbp, ...}
            $table->boolean('published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_games');
    }
};

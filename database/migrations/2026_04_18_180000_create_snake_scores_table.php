<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('snake_scores', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30);
            $table->unsignedInteger('score');
            $table->timestamps();

            $table->index('score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('snake_scores');
    }
};

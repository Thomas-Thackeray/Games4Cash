<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('game_name_adjustments');
    }

    public function down(): void
    {
        Schema::create('game_name_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('keyword', 200)->unique();
            $table->decimal('adjustment_gbp', 8, 2)->default(0);
            $table->timestamps();
        });
    }
};

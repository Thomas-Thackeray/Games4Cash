<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('franchise_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('franchise_name')->unique();
            $table->decimal('adjustment_gbp', 8, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('franchise_adjustments');
    }
};

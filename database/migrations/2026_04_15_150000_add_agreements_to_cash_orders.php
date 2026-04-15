<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_orders', function (Blueprint $table) {
            $table->boolean('agreed_terms')->default(false)->after('postcode');
            $table->boolean('confirmed_contents')->default(false)->after('agreed_terms');
        });
    }

    public function down(): void
    {
        Schema::table('cash_orders', function (Blueprint $table) {
            $table->dropColumn(['agreed_terms', 'confirmed_contents']);
        });
    }
};

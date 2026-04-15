<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wishlists', function (Blueprint $table) {
            $table->unsignedInteger('release_date')->nullable()->after('steam_app_id');
        });

        Schema::table('cash_basket_items', function (Blueprint $table) {
            $table->unsignedInteger('release_date')->nullable()->after('steam_app_id');
        });
    }

    public function down(): void
    {
        Schema::table('wishlists', function (Blueprint $table) {
            $table->dropColumn('release_date');
        });

        Schema::table('cash_basket_items', function (Blueprint $table) {
            $table->dropColumn('release_date');
        });
    }
};

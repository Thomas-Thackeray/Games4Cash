<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_basket_items', function (Blueprint $table) {
            // new | complete | disk  — null until the user selects one in the basket
            $table->string('condition', 20)->nullable()->after('platform_id');
        });
    }

    public function down(): void
    {
        Schema::table('cash_basket_items', function (Blueprint $table) {
            $table->dropColumn('condition');
        });
    }
};

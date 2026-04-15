<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_orders', function (Blueprint $table) {
            $table->string('house_name_number', 100)->nullable()->after('admin_notes');
            $table->string('address_line1', 150)->nullable()->after('house_name_number');
            $table->string('address_line2', 150)->nullable()->after('address_line1');
            $table->string('address_line3', 150)->nullable()->after('address_line2');
            $table->string('city', 100)->nullable()->after('address_line3');
            $table->string('county', 100)->nullable()->after('city');
            $table->string('postcode', 20)->nullable()->after('county');
        });
    }

    public function down(): void
    {
        Schema::table('cash_orders', function (Blueprint $table) {
            $table->dropColumn(['house_name_number', 'address_line1', 'address_line2', 'address_line3', 'city', 'county', 'postcode']);
        });
    }
};

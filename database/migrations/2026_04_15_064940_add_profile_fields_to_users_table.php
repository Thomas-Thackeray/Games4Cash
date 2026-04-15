<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->after('id');
            $table->string('surname')->after('first_name');
            $table->string('username')->unique()->after('surname');
            $table->string('contact_number', 20)->after('email');
            $table->string('name')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'surname', 'username', 'contact_number']);
            $table->string('name')->nullable(false)->change();
        });
    }
};

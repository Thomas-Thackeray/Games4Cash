<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['user', 'admin'])->default('user')->after('username');
            $table->boolean('force_password_reset')->default(false)->after('role');
            $table->timestamp('last_active_at')->nullable()->after('force_password_reset');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'force_password_reset', 'last_active_at']);
        });
    }
};

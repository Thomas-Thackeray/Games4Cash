<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_ref', 20)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('pending'); // pending | contacted | completed | cancelled
            $table->json('items');           // snapshot of basket at submission time
            $table->decimal('total_gbp', 8, 2)->default(0);
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_orders');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('customer_phone')->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->enum('payment_method', ['cash', 'card', 'digital_wallet']);
            $table->foreignId('staff_id')->constrained('users');
            $table->enum('status', ['pending', 'completed', 'refunded'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

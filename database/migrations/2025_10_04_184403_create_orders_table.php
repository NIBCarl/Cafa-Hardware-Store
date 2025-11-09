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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('order_number')->unique();
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['pending', 'confirmed', 'processing', 'ready', 'completed', 'cancelled'])->default('pending');
            $table->enum('payment_method', ['cash', 'card', 'digital_wallet', 'gcash'])->default('cash');
            $table->enum('payment_status', ['pending', 'paid', 'refunded'])->default('pending');
            $table->enum('delivery_method', ['pickup', 'delivery'])->default('pickup');
            $table->text('delivery_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['customer_id', 'status']);
            $table->index('order_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

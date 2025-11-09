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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_proof')->nullable()->after('payment_status');
            $table->string('payment_reference')->nullable()->after('payment_proof');
            $table->timestamp('verified_at')->nullable()->after('completed_at');
            $table->foreignId('verified_by')->nullable()->constrained('users')->after('verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['payment_proof', 'payment_reference', 'verified_at', 'verified_by']);
        });
    }
};

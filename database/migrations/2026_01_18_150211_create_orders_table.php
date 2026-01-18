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
            $table->string('stripe_session_id')->unique()->nullable();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('customer_email');
            $table->string('customer_name');
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('fee');
            $table->unsignedInteger('total');
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->timestamps();
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

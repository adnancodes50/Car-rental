<?php
// database/migrations/2025_10_29_000000_create_equipment_purchase_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Drop existing table if you really want a fresh start (⚠️ removes data)
        if (Schema::hasTable('equipment_purchase')) {
            Schema::drop('equipment_purchase');
        }

        Schema::create('equipment_purchase', function (Blueprint $table) {
            $table->id();

            // Who / what
            $table->unsignedBigInteger('equipment_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable(); // where the stock was taken from
            $table->unsignedInteger('quantity')->default(1);       // how many purchased

            // Money
            $table->decimal('total_price', 12, 2)->default(0);
            $table->decimal('deposit_expected', 12, 2)->nullable();
            $table->decimal('deposit_paid', 12, 2)->default(0);

            // Status / method
            $table->string('payment_status')->default('pending'); // pending|paid|failed
            $table->string('payment_method')->nullable();         // stripe|payfast|…

            // Stripe (optional)
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_payment_method_id')->nullable();
            $table->string('stripe_charge_id')->nullable();
            $table->string('card_brand')->nullable();
            $table->string('card_last4')->nullable();
            $table->integer('card_exp_month')->nullable();
            $table->integer('card_exp_year')->nullable();
            $table->string('receipt_url')->nullable();

            // PayFast (optional)
            $table->string('payfast_payment_id')->nullable();

            // Audit snapshot of stock at the selected location
            $table->integer('stock_before')->nullable();
            $table->integer('stock_after')->nullable();

            $table->timestamps();

            // FKs
            $table->foreign('equipment_id')
                  ->references('id')->on('equipment')
                  ->onUpdate('cascade')->onDelete('restrict');

            $table->foreign('customer_id')
                  ->references('id')->on('customers')
                  ->onUpdate('cascade')->onDelete('set null');

            $table->foreign('location_id')
                  ->references('id')->on('locations')
                  ->onUpdate('cascade')->onDelete('set null');

            // Indexes
            $table->index(['equipment_id']);
            $table->index(['customer_id']);
            $table->index(['location_id']);
            $table->index(['payment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_purchase');
    }
};

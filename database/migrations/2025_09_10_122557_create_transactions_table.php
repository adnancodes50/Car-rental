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
    $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
    $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
    $table->enum('type', ['rental_payment','deposit','purchase','refund']);
    $table->bigInteger('amount'); // in cents
    $table->string('currency', 3)->default('ZAR');
    $table->enum('status', ['authorized','captured','refunded','failed'])->default('authorized');
    $table->string('gateway')->nullable();
    $table->string('external_id')->nullable();
    $table->timestamp('occurred_at')->nullable();
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

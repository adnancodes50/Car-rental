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
        Schema::create('bookings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
    $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
    $table->date('start_date');
    $table->date('end_date');
    $table->enum('type', ['rental','maintenance','block']);
    $table->enum('status', ['pending','confirmed','completed','canceled'])->default('pending');
    $table->string('reference')->nullable();
    $table->text('notes')->nullable();
    $table->unsignedBigInteger('total_price')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

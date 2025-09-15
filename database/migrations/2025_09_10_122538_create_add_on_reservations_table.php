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
        Schema::create('add_on_reservations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('add_on_id')->constrained()->cascadeOnDelete();
    $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
    $table->unsignedInteger('qty')->default(1);
    $table->unsignedBigInteger('price_total')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('add_on_reservations');
    }
};

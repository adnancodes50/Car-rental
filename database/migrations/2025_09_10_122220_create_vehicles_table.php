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
       Schema::create('vehicles', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('model')->nullable();
    $table->year('year')->nullable();
    $table->string('type')->nullable(); // SUV, 4x4 etc.
    $table->text('description')->nullable();
    $table->string('location')->nullable();
    $table->string('transmission')->nullable();
    $table->string('fuel_type')->nullable();
    $table->string('drive_type')->nullable();
    $table->unsignedInteger('seats')->nullable();
    $table->unsignedInteger('mileage')->nullable();
    $table->string('engine')->nullable();
    $table->string('main_image_url')->nullable();
    $table->boolean('is_for_sale')->default(false);
    $table->unsignedBigInteger('rental_price_day')->nullable();
    $table->unsignedBigInteger('rental_price_week')->nullable();
    $table->unsignedBigInteger('rental_price_month')->nullable();
    $table->unsignedInteger('booking_lead_days')->default(0);
    $table->unsignedBigInteger('purchase_price')->nullable();
    $table->unsignedBigInteger('deposit_amount')->nullable();
    $table->enum('status', ['available','rented','maintenance','sold'])->default('available');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};

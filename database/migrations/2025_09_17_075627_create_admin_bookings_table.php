<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminBookingsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_bookings', function (Blueprint $table) {
            $table->id();

            // Link to vehicles only
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');

            // Booking fields
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('type', ['maintenance', 'internal', 'purchaser']);
            $table->string('customer_reference')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_bookings');
    }
}

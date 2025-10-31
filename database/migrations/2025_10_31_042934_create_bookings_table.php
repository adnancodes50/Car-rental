<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('equipment_id')->nullable()->constrained('equipment')->onDelete('set null');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');

            // Booking details
            $table->date('start_date');
            $table->date('end_date');
            $table->string('type')->nullable();      // e.g., rental, sale, demo, etc.
            $table->string('status')->default('pending'); // pending, confirmed, cancelled
            $table->string('reference')->unique()->nullable();
            $table->text('admin_note')->nullable();
            $table->text('notes')->nullable();

            // Pricing info
            $table->decimal('total_price', 10, 2)->default(0);
            $table->integer('extra_days')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

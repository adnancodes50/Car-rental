<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('location_pricings', function (Blueprint $table) {
            $table->id();

            // 🔗 Reference the 'locations' table for "from" and "to"
            $table->foreignId('from_location_id')
                  ->constrained('locations')
                  ->cascadeOnDelete();

            $table->foreignId('to_location_id')
                  ->constrained('locations')
                  ->cascadeOnDelete();

            // 💰 Transfer fee between these two locations
            $table->unsignedBigInteger('transfer_fee')->default(0);

            // (optional) If you want to make it inactive temporarily
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_pricings');
    }
};

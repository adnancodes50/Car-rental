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
        Schema::create('stripe_settings', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_key')->nullable();       // Publishable Key
            $table->string('stripe_secret')->nullable();    // Secret Key
            $table->enum('stripe_mode', ['sandbox', 'live'])->default('sandbox'); // Mode
            $table->boolean('stripe_enabled')->default(false); // Enable/Disable
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stripe_settings');
    }
};

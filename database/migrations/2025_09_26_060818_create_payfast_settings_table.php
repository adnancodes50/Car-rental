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
        Schema::create('payfast_settings', function (Blueprint $table) {
            $table->id();
            $table->string('merchant_id')->nullable();
            $table->string('merchant_key')->nullable();
            $table->string('passphrase')->nullable();
            $table->boolean('test_mode')->default(true);   // true = sandbox, false = live
            $table->boolean('enabled')->default(false);    // enable toggle
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payfast_settings');
    }
};

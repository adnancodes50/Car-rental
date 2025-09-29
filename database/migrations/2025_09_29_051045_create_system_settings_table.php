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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();

            // Stripe Settings
            $table->string('stripe_key')->nullable();
            $table->string('stripe_secret')->nullable();
            $table->enum('stripe_mode', ['sandbox', 'live'])->default('sandbox');
            $table->boolean('stripe_enabled')->default(false);

            // Payfast Settings
            $table->string('payfast_merchant_id')->nullable();
            $table->string('payfast_merchant_key')->nullable();
            $table->string('payfast_passphrase')->nullable();
            $table->boolean('payfast_test_mode')->default(true);
            $table->boolean('payfast_enabled')->default(false);

            // SMTP / Email Settings
            $table->string('mail_username')->nullable();
            $table->string('mail_password')->nullable();
            $table->string('mail_host')->nullable();
            $table->integer('mail_port')->nullable();
            $table->enum('mail_encryption', ['ssl', 'tls', 'none'])->default('ssl');
            $table->string('mail_from_address')->nullable();
            $table->string('mail_from_name')->nullable();
            $table->boolean('mail_enabled')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};

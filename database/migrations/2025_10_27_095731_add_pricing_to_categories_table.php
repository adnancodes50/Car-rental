<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Pricing columns
            $table->decimal('daily_price', 10, 2)->nullable()->after('status');
            $table->decimal('weekly_price', 10, 2)->nullable()->after('daily_price');
            $table->decimal('monthly_price', 10, 2)->nullable()->after('weekly_price');

            // Sale-related columns
            $table->boolean('is_for_sale')->default(false)->after('monthly_price');
            $table->decimal('deposit_price', 10, 2)->nullable()->after('is_for_sale');
            $table->decimal('total_amount', 10, 2)->nullable()->after('deposit_price');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn([
                'daily_price',
                'weekly_price',
                'monthly_price',
                'is_for_sale',
                'deposit_price',
                'total_amount',
            ]);
        });
    }
};
    
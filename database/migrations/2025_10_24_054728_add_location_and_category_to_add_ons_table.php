<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('add_ons', function (Blueprint $table) {
            $table->foreignId('location_id')->after('id')->constrained('locations')->onDelete('cascade');
            $table->foreignId('category_id')->after('location_id')->constrained('categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('add_ons', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropForeign(['category_id']);
            $table->dropColumn(['location_id', 'category_id']);
        });
    }
};

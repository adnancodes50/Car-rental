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
    Schema::table('vehicles', function (Blueprint $table) {
        $table->foreignId('category_id')->nullable()->after('description')->constrained()->nullOnDelete();
        $table->foreignId('location_id')->nullable()->after('category_id')->constrained()->nullOnDelete();
    });
}

public function down(): void
{
    Schema::table('vehicles', function (Blueprint $table) {
        $table->dropForeign(['category_id']);
        $table->dropForeign(['location_id']);
        $table->dropColumn(['category_id', 'location_id']);
    });
}

};

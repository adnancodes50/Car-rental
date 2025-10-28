<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image')->nullable(); // store image path
            $table->text('description')->nullable();

            // ✅ only keep category_id (location_id is handled in equipment_stocks)
            $table->unsignedBigInteger('category_id');

            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            // ✅ foreign key to categories
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};

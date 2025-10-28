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
            $table->string('image')->nullable();          // store path or filename
            $table->text('description')->nullable();     // description of equipment

            // Foreign keys
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('category_id');

            $table->enum('status', ['active','inactive'])->default('active');

            $table->timestamps();

            // Foreign key constraints
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};

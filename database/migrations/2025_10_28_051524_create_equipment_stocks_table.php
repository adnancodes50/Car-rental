<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('equipment_stocks', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('equipment_id');
    $table->unsignedBigInteger('location_id');
    $table->integer('stock')->default(0);
    $table->timestamps();

    $table->foreign('equipment_id')->references('id')->on('equipment')->onDelete('cascade');
    $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
    $table->unique(['equipment_id', 'location_id']);
});

    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_stocks');
    }
};

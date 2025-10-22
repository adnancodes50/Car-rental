<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image')->nullable();              // store path or filename
            $table->text('short_description')->nullable();    // short text about category
            $table->enum('status', ['active','inactive'])->default('active');
            $table->timestamps();                             // remove if you want exactly 5 cols total
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};

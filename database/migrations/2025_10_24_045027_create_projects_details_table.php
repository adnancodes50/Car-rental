<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects_details', function (Blueprint $table) {
            $table->id();
            $table->string('project_name');
            $table->string('logo')->nullable(); // store logo filename or path
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects_details');
    }
};

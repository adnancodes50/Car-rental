<?php



use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();                              // 1) id
            $table->string('name');                    // 2) name
            $table->string('email')->nullable();       // 3) email
            $table->string('phone')->nullable();       // 4) phone
            $table->enum('status', ['active','inactive'])->default('active'); // 5) status
            // no timestamps so we keep it to 5 columns exactly
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};

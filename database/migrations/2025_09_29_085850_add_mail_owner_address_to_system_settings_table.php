<?php
// php artisan make:migration add_mail_owner_address_to_system_settings_table --table=system_settings
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('system_settings', function (Blueprint $t) {
            $t->string('mail_owner_address')->nullable()->after('mail_from_name');
        });
    }
    public function down(): void {
        Schema::table('system_settings', function (Blueprint $t) {
            $t->dropColumn('mail_owner_address');
        });
    }
};

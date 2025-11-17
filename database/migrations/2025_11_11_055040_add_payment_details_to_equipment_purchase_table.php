<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentDetailsToEquipmentPurchaseTable extends Migration
{
    public function up()
    {
        Schema::table('equipment_purchase', function (Blueprint $table) {
            $table->timestamp('paid_at')->nullable()->after('payment_status');
            $table->json('payment_details')->nullable()->after('payfast_payment_id');
        });
    }

    public function down()
    {
        Schema::table('equipment_purchase', function (Blueprint $table) {
            $table->dropColumn(['paid_at', 'payment_details']);
        });
    }
}

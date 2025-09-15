<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLandingSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('landing_settings', function (Blueprint $table) {
            $table->id();
            $table->string('hero_image_url')->nullable();   // publicly usable URL (e.g. /storage/hero_images/xxx.jpg or external URL)
            $table->string('hero_image_path')->nullable();  // storage path (e.g. public/hero_images/xxx.jpg) to delete when replaced
            $table->string('email_btn_text')->nullable();
            $table->string('email_link')->nullable();       // store the mailto:... string
            $table->string('phone_btn_text')->nullable();
            $table->string('phone_link')->nullable();       // store the tel:... string
            $table->string('whatsapp_btn_text')->nullable();
            $table->string('whatsapp_link')->nullable();    // full https://wa.me/...
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('landing_settings');
    }
}

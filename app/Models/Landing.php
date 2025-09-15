<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Landing extends Model
{
    use HasFactory;

    // Specify the table name explicitly
    protected $table = 'landing_settings';

   protected $fillable = [
    'hero_image_url',
    'hero_image_path',
    'email_btn_text',
    'email_link',
    'phone_btn_text',
    'phone_link',
    'whatsapp_btn_text',
    'whatsapp_link',
];

}

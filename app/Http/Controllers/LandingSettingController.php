<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Landing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LandingSettingController extends Controller
{
    public function index()
    {
        $settings = Landing::first();
        return view('admin.landing_settings.index', compact('settings'));
    }

public function update(Request $request)
{
    $data = $request->validate([
        'email_btn_text'      => 'required|string|max:255',
        'email_link'          => ['required','string', function($attr, $value, $fail) {
            if (!Str::startsWith($value, 'mailto:') || !filter_var(substr($value, 7), FILTER_VALIDATE_EMAIL)) {
                $fail('Email link must be a valid mailto:someone@example.com');
            }
        }],
        'phone_btn_text'      => 'required|string|max:255',
        'phone_link'          => ['required','string', function($attr, $value, $fail) {
            if (!Str::startsWith($value, 'tel:') || !preg_match('/^tel:\+\d{6,15}$/', $value)) {
                $fail('Phone link must be like tel:+123456789 (include +countrycode)');
            }
        }],
        'whatsapp_btn_text'   => 'required|string|max:255',
        'whatsapp_link'       => ['required', 'string', function($attr, $value, $fail) {
            $isNumber = preg_match('/^\+\d{7,15}$/', $value);
            $isWaMe   = preg_match('/^https:\/\/wa\.me\/\d{7,15}$/', $value);
            $isApi    = preg_match('/^https:\/\/api\.whatsapp\.com\/send\?phone=\d{7,15}$/', $value);

            if (!($isNumber || $isWaMe || $isApi)) {
                $fail('WhatsApp link must be a valid number (+123456789) or a valid link like https://wa.me/number or https://api.whatsapp.com/send?phone=...');
            }
        }],
        'hero_image'          => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
    ]);

    // Fetch existing settings or create new
    $settings = Landing::first() ?? new Landing();

    // Handle hero image upload
    if ($request->hasFile('hero_image')) {
        // Delete old file if exists
        if ($settings->hero_image_path && Storage::exists($settings->hero_image_path)) {
            Storage::delete($settings->hero_image_path);
        }

        // Store new file in Hero_section/image folder
        $path = $request->file('hero_image')->store('public/Hero_section/image');

        // Save only the path
        $settings->hero_image_path = $path;
    }

    // Save contact settings
    $settings->email_btn_text     = $data['email_btn_text'];
    $settings->email_link         = $data['email_link'];
    $settings->phone_btn_text     = $data['phone_btn_text'];
    $settings->phone_link         = $data['phone_link'];
    $settings->whatsapp_btn_text  = $data['whatsapp_btn_text'];
    $settings->whatsapp_link      = $data['whatsapp_link'];

    $settings->save();

    return redirect()
        ->route('admin.landing-settings.index')
        ->with('success', 'Landing page settings saved successfully.');
}



}

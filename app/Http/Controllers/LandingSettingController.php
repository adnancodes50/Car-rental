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
        'email_btn_text' => 'required|string|max:255',
       'email_link' => ['required', 'string', function($attr, $value, $fail) {
    // Validate as a proper email address directly
    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
        $fail('The email must be a valid email address.');
    }
}],

        'phone_btn_text' => 'required|string|max:255',
        'phone_link' => ['required', 'string', function($attr, $value, $fail) {
            if (!preg_match('/^\+\d{7,15}$/', $value)) {
                $fail('Phone number must be like +92454565656 (start with + followed by 7-15 digits).');
            }
        }],
        'whatsapp_btn_text' => 'required|string|max:255',
        'whatsapp_link' => ['required', 'string', function($attr, $value, $fail) {
            $isNumber = preg_match('/^\+\d{7,15}$/', $value);
            $isWaMe   = preg_match('/^https:\/\/wa\.me\/\d{7,15}$/', $value);
            $isApi    = preg_match('/^https:\/\/api\.whatsapp\.com\/send\?phone=\d{7,15}$/', $value);

            if (!($isNumber || $isWaMe || $isApi)) {
                $fail('WhatsApp link must be a valid number (+123456789) or a valid link like https://wa.me/number or https://api.whatsapp.com/send?phone=...');
            }
        }],
        'hero_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
    ]);

    $settings = Landing::first() ?? new Landing();

    // -------------------------------
    // Hero Image Handling (directly in public/storage/hero-section)
    // -------------------------------
    if ($request->hasFile('hero_image')) {
        $destination = public_path('storage/hero-section');
        if (!file_exists($destination)) mkdir($destination, 0755, true);

        // Delete old image if exists
        if ($settings->hero_image_path) {
            $oldPath = public_path(ltrim($settings->hero_image_path, '/'));
            if (file_exists($oldPath)) unlink($oldPath);
        }

        $file = $request->file('hero_image');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move($destination, $filename);

        // Save public path in DB
        $settings->hero_image_path = '/storage/hero-section/' . $filename;
    }

    // -------------------------------
    // Save Contact & Button Settings
    // -------------------------------
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

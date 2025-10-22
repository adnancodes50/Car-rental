<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Landing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LandingSettingController extends Controller
{
    public function index()
    {
        $settings = Landing::first();
        return view('admin.landing_settings.index', compact('settings'));
    }

public function update(Request $request)
{
    try {
        $data = $request->validate([
            'email_btn_text' => 'required|string|max:255',
            'email_link' => ['required', 'string', function ($attr, $value, $fail) {
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $fail('The email must be a valid email address.');
                }
            }],
            'phone_btn_text' => 'required|string|max:255',
            'phone_link' => ['required', 'string', function ($attr, $value, $fail) {
                if (!preg_match('/^\+\d{8,15}$/', $value)) {
                    $fail('Phone number must start with + and contain only digits (e.g., +1234567890).');
                }
            }],
            'hero_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        $settings = Landing::first() ?? new Landing();

        // Handle image
        if ($request->hasFile('hero_image')) {
            if (!empty($settings->hero_image_path)) {
                $publicPath = ltrim($settings->hero_image_path, '/');
                $diskPath = preg_replace('#^storage/#', '', $publicPath);
                Storage::disk('public')->delete($diskPath);
            }

            $path = $request->file('hero_image')->store('herosection', 'public');
            $settings->hero_image_path = Storage::url($path);
        }

        // Save fields
        $settings->email_btn_text = $data['email_btn_text'];
        $settings->email_link = $data['email_link'];
        $settings->phone_btn_text = $data['phone_btn_text'];
        $settings->phone_link = $data['phone_link'];

        // WhatsApp fields set to NULL
        $settings->whatsapp_btn_text = null;
        $settings->whatsapp_link = null;

        $settings->save();

        return redirect()
            ->route('admin.landing-settings.index')
            ->with('success', 'Landing page settings saved successfully.');
    } catch (\Exception $e) {
        return redirect()
            ->route('admin.landing-settings.index')
            ->with('error', $e->getMessage());
    }
}


}

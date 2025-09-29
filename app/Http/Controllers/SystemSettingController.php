<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SystemSetting;

class SystemSettingController extends Controller
{
    /**
     * Show the settings form.
     */
    public function edit()
    {
        $setting = SystemSetting::first(); // always get the first (and only) record
        return view('admin.systemsetting.setting', compact('setting'));
    }

    /**
     * Store or update the settings (only one record).
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            // Stripe
            'stripe_key'     => 'nullable|string|max:255',
            'stripe_secret'  => 'nullable|string|max:255',
            'stripe_mode'    => 'required|in:sandbox,live',
            'stripe_enabled' => 'required|boolean',

            // PayFast
            'payfast_merchant_id'  => 'nullable|string|max:255',
            'payfast_merchant_key' => 'nullable|string|max:255',
            'payfast_passphrase'   => 'nullable|string|max:255',
            'payfast_test_mode'    => 'required|boolean',
            'payfast_enabled'      => 'required|boolean',
            'payfast_live_url'     => 'nullable|url|max:255', // ✅ added

            // SMTP
            'mail_username'      => 'nullable|string|max:255',
            'mail_password'      => 'nullable|string|max:255',
            'mail_host'          => 'nullable|string|max:255',
            'mail_port'          => 'nullable|integer',
            'mail_encryption'    => 'required|in:ssl,tls,none',
            'mail_from_address'  => 'nullable|email',
            'mail_from_name'     => 'nullable|string|max:255',
            'mail_enabled'       => 'required|boolean',
'mail_owner_address' => 'required|email',
        ]);

        // create once, update after
        SystemSetting::updateOrCreate(
            ['id' => 1], // enforce single record
            $validated
        );

        return redirect()
            ->route('systemsetting.edit')
            ->with('success', 'System settings saved successfully!');
    }
}

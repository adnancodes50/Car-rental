<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PayfastSetting;

class PayfastSettingController extends Controller
{
    /**
     * Show the PayFast settings form.
     */
    public function edit()
    {
        $payfast = PayfastSetting::first(); // only one record
        return view('admin.payment.payfast', compact('payfast'));
    }

    /**
     * Store or update the PayFast settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'merchant_id'  => 'nullable|string|max:255',
            'merchant_key' => 'nullable|string|max:255',
            'passphrase'   => 'nullable|string|max:255',
            'test_mode'    => 'required|boolean',
            'enabled'      => 'required|boolean',
        ]);

        // update existing or create new (only 1 row)
        PayfastSetting::updateOrCreate(
            ['id' => 1],
            $validated
        );

        return redirect()
            ->route('payfast.edit')
            ->with('status', 'PayFast settings updated successfully!');
    }
}

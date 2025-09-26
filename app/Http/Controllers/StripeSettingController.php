<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StripeSetting;

class StripeSettingController extends Controller
{
    /**
     * Show the Stripe settings form.
     */
    public function edit()
    {
        $stripe = StripeSetting::first(); // only one record
        return view('admin.payment.stripe', compact('stripe'));
    }

    /**
     * Store or update the Stripe settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'stripe_key'     => 'nullable|string|max:255',
            'stripe_secret'  => 'nullable|string|max:255',
            'stripe_mode'    => 'required|in:sandbox,live',
            'stripe_enabled' => 'required|boolean',
        ]);

        // update existing or create new (only 1 row)
        StripeSetting::updateOrCreate(
            ['id' => 1],
            $validated
        );

       return redirect()
    ->route('stripe.edit')
    ->with('success', 'Stripe settings updated successfully!');


    }
}

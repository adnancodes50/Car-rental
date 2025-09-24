<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PaymentSettingsController extends Controller
{
   public function edit()
{
    $stripe = Cache::get('payments.stripe', config('payments.stripe')) ?: [];
    $payfast = Cache::get('payments.payfast', config('payments.payfast')) ?: [];

    return view('admin.setting.index', compact('stripe', 'payfast'));
}


    /**
     * Helper to update .env values
     */
    protected function setEnv($key, $value)
    {
        $path = base_path('.env');

        if (file_exists($path)) {
            $escapedKey = preg_quote($key, '/');

            if (preg_match("/^{$escapedKey}=.*/m", file_get_contents($path))) {
                file_put_contents(
                    $path,
                    preg_replace(
                        "/^{$escapedKey}=.*/m",
                        "{$key}={$value}",
                        file_get_contents($path)
                    )
                );
            } else {
                file_put_contents($path, PHP_EOL."{$key}={$value}", FILE_APPEND);
            }
        }
    }

    /**
     * Update Stripe settings
     */
    public function updateStripe(Request $request)
    {
        $this->setEnv('STRIPE_MODE', $request->stripe_mode ?? 'sandbox');
        $this->setEnv('STRIPE_KEY', $request->stripe_key ?? '');
        $this->setEnv('STRIPE_SECRET', $request->stripe_secret ?? '');
        $this->setEnv('STRIPE_ENABLED', $request->has('stripe_enabled') ? 'true' : 'false');

        \Artisan::call('config:clear');

        return redirect()
            ->route('settings.payments.stripe')
            ->with('status', 'Stripe settings updated successfully!');
    }

    /**
     * Update PayFast settings
     */
    public function updatePayfast(Request $request)
    {
        $this->setEnv('PAYFAST_MERCHANT_ID', $request->PAYFAST_MERCHANT_ID ?? '');
        $this->setEnv('PAYFAST_MERCHANT_KEY', $request->PAYFAST_MERCHANT_KEY ?? '');
        $this->setEnv('PAYFAST_PASSPHRASE', $request->PAYFAST_PASSPHRASE ?? '');
        $this->setEnv('PAYFAST_TEST_MODE', $request->PAYFAST_TEST_MODE ?? 'true');
        $this->setEnv('PAYFAST_ENABLED', $request->has('PAYFAST_ENABLED') ? 'true' : 'false');

        \Artisan::call('config:clear');

        return redirect()
            ->route('settings.payments.payfast')
            ->with('status', 'PayFast settings updated successfully!');
    }

    /**
     * Show Stripe settings page
     */
    public function stripe()
    {
        $stripe = config('payments.stripe');
        return view('admin.setting.payments.stripe', compact('stripe'));
    }

    /**
     * Show PayFast settings page
     */
    public function payfast()
    {
        $payfast = config('payments.payfast');
        return view('admin.setting.payments.payfast', compact('payfast'));
    }
}

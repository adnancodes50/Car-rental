<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\EmailLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class CommunicationController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::all();

        // 🔹 Handle filter selection
        $filter = $request->get('filter', 'all');
        $query = EmailLog::with('customer')->latest();

        if ($filter === '7days') {
            $query->where('sent_at', '>=', now()->subDays(7));
        } elseif ($filter === '30days') {
            $query->where('sent_at', '>=', now()->subDays(30));
        }

        $emailLogs = $query->paginate(10);

        return view('admin.communication.index', compact('customers', 'emailLogs', 'filter'));
    }

    public function sendBulkEmail(Request $request)
    {
        $request->validate([
            'customer_ids' => 'required|array|min:1',
            'customer_ids.*' => 'exists:customers,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        // Load mail settings dynamically
        $settings = \App\Models\SystemSetting::first();
        if ($settings && $settings->mail_enabled) {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.host' => $settings->mail_host,
                'mail.mailers.smtp.port' => $settings->mail_port,
                'mail.mailers.smtp.encryption' => $settings->mail_encryption,
                'mail.mailers.smtp.username' => $settings->mail_username,
                'mail.mailers.smtp.password' => $settings->mail_password,
                'mail.from.address' => $settings->mail_from_address,
                'mail.from.name' => $settings->mail_from_name,
            ]);
        }

        $customers = Customer::whereIn('id', $request->customer_ids)->get();

        foreach ($customers as $customer) {
            if (!$customer->email) continue;

            Mail::to($customer->email)->send(
                new \App\Mail\CustomerBulkMail($request->subject, $request->body)
            );

            EmailLog::create([
                'customer_id' => $customer->id,
                'subject' => $request->subject,
                'body' => $request->body,
                'sent_at' => now(),
                'sent_by' => auth()->id(),
            ]);
        }

        return redirect()->route('communication-setting.index')
            ->with('success', 'Emails sent successfully to selected customers.');
    }
}

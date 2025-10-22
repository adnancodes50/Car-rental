<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmailTemplate;
use Illuminate\Validation\Rule;



class EmailTempleteController extends Controller
{
    public function index()
    {
        $templates = EmailTemplate::all();
        return view('admin.emailtemplete.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.emailtemplete.create');
    }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'trigger' => 'required|string|max:255',
    //         'recipient' => 'required|in:customer,admin',
    //         'name' => 'required|string|max:255',
    //         'subject' => 'required|string|max:255',
    //         'body' => 'required|string',
    //         'enabled' => 'required|boolean',
    //     ]);

    //     // ✅ Check if trigger+recipient already exists
    //     $exists = EmailTemplate::where('trigger', $request->trigger)
    //         ->where('recipient', $request->recipient)
    //         ->exists();

    //     if ($exists) {
    //         return redirect()->route('email.index')
    //             ->with('error', 'This trigger and recipient already exist!');
    //     }


    //     EmailTemplate::create($request->only([
    //         'trigger',
    //         'recipient',
    //         'name',
    //         'subject',
    //         'body',
    //         'enabled'
    //     ]));

    //     return redirect()->route('email.index')->with('success', 'Template created successfully!');
    // }

    public function edit($id)
    {
        $template = EmailTemplate::findOrFail($id);
        return view('admin.emailtemplete.edit', compact('template'));
    }




public function update(Request $request, $id)
{
    $template = EmailTemplate::findOrFail($id);

    $data = $request->validate([
        'trigger'   => [
            'required', 'string', 'max:255',
            Rule::unique('email_templates')
                ->where(fn ($q) => $q->where('recipient', $request->input('recipient')))
                ->ignore($template->id),
        ],
        'recipient' => ['required', Rule::in(['customer', 'admin'])],
        'name'      => ['required', 'string', 'max:255'],
        'subject'   => ['required', 'string', 'max:255'],
        'body'      => ['required', 'string'], // store full HTML
        'enabled'   => ['nullable'],
    ]);

    // Coerce checkbox to boolean
    $data['enabled'] = $request->boolean('enabled');

    $template->update($data);

    return redirect()
        ->route('email.index')
        ->with('success', 'Template updated successfully!');
}


}

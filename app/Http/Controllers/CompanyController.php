<?php

namespace App\Http\Controllers;

use App\Models\ProjectModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache; // <- add this

class CompanyController extends Controller
{
    public function edit()
    {
        $detail = ProjectModel::first();
        return view('admin.company.edit', compact('detail'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'project_name' => ['required', 'string', 'max:255'],
            'logo'         => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
        ]);

        $detail = ProjectModel::first();

        $data = [
            'project_name' => $request->project_name,
        ];

        if ($request->hasFile('logo')) {
            Storage::makeDirectory('public/logo');

            // delete old file if present
            if (!empty($detail?->logo)) {
                Storage::delete('public/' . $detail->logo);
            }

            $ext = $request->file('logo')->extension();

            // random digits filename like img1234567890.png
            $rand     = random_int(1000000000, 9999999999);
            $filename = "img{$rand}.{$ext}";

            $request->file('logo')->storeAs('public/logo', $filename);
            $data['logo'] = "logo/{$filename}";
        }

        $detail ? $detail->update($data) : $detail = ProjectModel::create($data);

        // IMPORTANT: bust the runtime config cache so new values load immediately
        Cache::forget('project_brand_cache');

        return redirect()
            ->route('company-setting.edit')
            ->with('success', 'Company settings updated successfully.');
    }
}

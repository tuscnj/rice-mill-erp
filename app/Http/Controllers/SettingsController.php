<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\File;

class SettingsController extends Controller
{
    public function index()
    {
        $setting = Setting::firstOrCreate(['id' => 1]);
        return view('settings', compact('setting'));
    }

    public function update(Request $request)
    {
        $setting = Setting::firstOrCreate(['id' => 1]);

        $request->validate([
            'company_name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'favicon' => 'nullable|mimes:ico,png,jpg,svg|max:1024', // 🚨 New Favicon Validation
        ]);

        $setting->company_name = $request->company_name;
        $setting->address = $request->address;
        $setting->phone = $request->phone;
        $setting->email = $request->email;

        // 1. Handle Main Logo
        if ($request->hasFile('logo')) {
            if ($setting->logo_path && File::exists(public_path($setting->logo_path))) {
                File::delete(public_path($setting->logo_path));
            }
            $file = $request->file('logo');
            $extension = $file->getClientOriginalExtension();
            $filename = 'logo_' . time() . '_' . uniqid() . '.' . $extension;
            $file->move(public_path('uploads/logos'), $filename);
            $setting->logo_path = 'uploads/logos/' . $filename;
        }

        // 2. 🚨 Handle Favicon
        if ($request->hasFile('favicon')) {
            if ($setting->favicon_path && File::exists(public_path($setting->favicon_path))) {
                File::delete(public_path($setting->favicon_path));
            }
            $file = $request->file('favicon');
            $extension = $file->getClientOriginalExtension();
            $filename = 'fav_' . time() . '_' . uniqid() . '.' . $extension;
            $file->move(public_path('uploads/logos'), $filename);
            $setting->favicon_path = 'uploads/logos/' . $filename;
        }

        $setting->save();

        return redirect('/settings')->with('success', 'Company settings updated successfully!');
    }
}
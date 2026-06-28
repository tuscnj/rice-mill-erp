<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\File;

class SettingsController extends Controller
{
    public function index()
    {
        // Get the first settings row, or create an empty one if it doesn't exist
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
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048', // Max 2MB
        ]);

        $setting->company_name = $request->company_name;
        $setting->address = $request->address;
        $setting->phone = $request->phone;
        $setting->email = $request->email;

// Handle Logo Upload securely
        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($setting->logo_path && File::exists(public_path($setting->logo_path))) {
                File::delete(public_path($setting->logo_path));
            }

            $file = $request->file('logo');
            
            // 🚨 FIX: Get the original extension (png/svg) so transparency is kept
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . uniqid() . '.' . $extension;
            
            // Move to the public/uploads/logos folder
            $file->move(public_path('uploads/logos'), $filename);
            
            $setting->logo_path = 'uploads/logos/' . $filename;
        }
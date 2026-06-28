@extends('layouts.app')
@section('title', 'Global Settings')
@section('content')

@php
    // Bulletproof Loaders
    $settingsLogo = '';
    if($setting->logo_path && file_exists(public_path($setting->logo_path))) {
        $type = pathinfo(public_path($setting->logo_path), PATHINFO_EXTENSION);
        $data = file_get_contents(public_path($setting->logo_path));
        $settingsLogo = 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
    
    $settingsFavicon = '';
    if($setting->favicon_path && file_exists(public_path($setting->favicon_path))) {
        $type = pathinfo(public_path($setting->favicon_path), PATHINFO_EXTENSION);
        $data = file_get_contents(public_path($setting->favicon_path));
        $settingsFavicon = 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
@endphp

    <div class="max-w-4xl mx-auto space-y-6 mt-6">
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 p-6 rounded-2xl text-white shadow-md flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-extrabold tracking-tight">System Settings</h2>
                <p class="text-slate-300 text-sm mt-1">Manage company details, logos, and favicons</p>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 text-emerald-700 p-4 rounded-xl border border-emerald-200 font-bold">{{ session('success') }}</div>
        @endif

        <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-gray-200">
            <form action="/update-settings" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Company Name</label>
                        <input type="text" name="company_name" value="{{ $setting->company_name }}" required class="w-full p-3 bg-gray-50 border border-gray-200 rounded-lg focus:border-blue-500 font-bold text-gray-800">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Official Address</label>
                        <textarea name="address" rows="3" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-lg focus:border-blue-500 text-gray-800">{{ $setting->address }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Phone Number</label>
                        <input type="text" name="phone" value="{{ $setting->phone }}" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-lg focus:border-blue-500 text-gray-800">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Email Address</label>
                        <input type="email" name="email" value="{{ $setting->email }}" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-lg focus:border-blue-500 text-gray-800">
                    </div>

                    {{-- IMAGES UPLOAD SECTION --}}
                    <div class="md:col-span-2 pt-4 border-t border-gray-100 grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        {{-- 1. Main Logo --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Main Logo (Sidebar & Print)</label>
                            <div class="flex items-center gap-4">
                                @if($settingsLogo)
                                    <img src="{{ $settingsLogo }}" alt="Logo" class="h-16 w-auto rounded-lg border border-gray-200 p-1 bg-slate-800">
                                @else
                                    <div class="h-16 w-16 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center text-gray-400 text-[10px]">No Logo</div>
                                @endif
                                <input type="file" name="logo" accept="image/*" class="text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer w-full">
                            </div>
                        </div>

                        {{-- 2. Favicon --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Favicon (Browser Tab Icon)</label>
                            <div class="flex items-center gap-4">
                                @if($settingsFavicon)
                                    <img src="{{ $settingsFavicon }}" alt="Favicon" class="h-16 w-16 object-contain rounded-lg border border-gray-200 p-1">
                                @else
                                    <div class="h-16 w-16 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center text-gray-400 text-[10px]">No Icon</div>
                                @endif
                                <input type="file" name="favicon" accept=".ico,.png,.jpg,.svg" class="text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer w-full">
                            </div>
                        </div>

                    </div>
                </div>

                <div class="pt-4 mt-6 border-t border-gray-100">
                    <button type="submit" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg transition text-lg shadow-sm">Save Global Settings</button>
                </div>
            </form>
        </div>
    </div>

@endsection
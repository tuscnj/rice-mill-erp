@extends('layouts.app')
@section('title', 'Global Settings')
@section('content')

    <div class="max-w-4xl mx-auto space-y-6 mt-6">
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 p-6 rounded-2xl text-white shadow-md flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-extrabold tracking-tight">System Settings</h2>
                <p class="text-slate-300 text-sm mt-1">Manage company details for ledgers and invoices</p>
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
                        <input type="text" name="company_name" value="{{ $setting->company_name }}" required class="w-full p-3 bg-gray-50 border border-gray-200 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-500 font-bold text-gray-800">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Official Address</label>
                        <textarea name="address" rows="3" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-gray-800" placeholder="e.g. Amnura Road, Jamtola Bazar, Chapainawabganj">{{ $setting->address }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Phone Number</label>
                        <input type="text" name="phone" value="{{ $setting->phone }}" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-gray-800">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Email Address</label>
                        <input type="email" name="email" value="{{ $setting->email }}" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-gray-800">
                    </div>

                    <div class="md:col-span-2 pt-4 border-t border-gray-100">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Company Logo (For Printing)</label>
                        <div class="flex items-center gap-6">
                            @if($setting->logo_path)
                                <img src="/{{ $setting->logo_path }}" alt="Logo" class="h-20 w-auto rounded-lg border border-gray-200 p-1">
                            @else
                                <div class="h-20 w-20 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center text-gray-400 text-xs">No Logo</div>
                            @endif
                            
                            <input type="file" name="logo" accept="image/*" class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
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
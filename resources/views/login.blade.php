@php
    // Fetch global settings securely
    $setting = \App\Models\Setting::first();
    $companyName = $setting->company_name ?? 'Atik Auto Rice Mills';

    // Base64 encode Logo
    $logoData = '';
    if($setting && $setting->logo_path && file_exists(public_path($setting->logo_path))) {
        $type = pathinfo(public_path($setting->logo_path), PATHINFO_EXTENSION);
        $data = file_get_contents(public_path($setting->logo_path));
        $logoData = 'data:image/' . $type . ';base64,' . base64_encode($data);
    }

    // Base64 encode Favicon
    $faviconData = '';
    if($setting && $setting->favicon_path && file_exists(public_path($setting->favicon_path))) {
        $type = pathinfo(public_path($setting->favicon_path), PATHINFO_EXTENSION);
        $data = file_get_contents(public_path($setting->favicon_path));
        $faviconData = 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Login | {{ $companyName }}</title>
    
    {{-- Dynamic Favicon --}}
    @if($faviconData)
        <link rel="icon" href="{{ $faviconData }}">
    @else
        <link rel="icon" href="data:;base64,iVBORw0KGgo=">
    @endif
    
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4 font-sans relative overflow-hidden">
    
    {{-- Ambient Background Glow Effects --}}
    <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-blue-600 rounded-full mix-blend-multiply filter blur-[128px] opacity-40"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-emerald-600 rounded-full mix-blend-multiply filter blur-[128px] opacity-30"></div>

    {{-- Main Login Card --}}
    <div class="relative w-full max-w-md z-10">
        <div class="bg-white rounded-[2rem] shadow-2xl overflow-hidden border border-slate-100">
            
            {{-- Dynamic Header --}}
            <div class="bg-slate-50 p-10 text-center border-b border-slate-100 relative overflow-hidden">
                <div class="relative z-10">
                    @if($logoData)
                        <img src="{{ $logoData }}" alt="Logo" class="h-20 mx-auto object-contain mb-5 drop-shadow-sm">
                    @else
                        <div class="h-16 w-16 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-5 text-3xl shadow-inner">🌾</div>
                    @endif
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">{{ $companyName }}</h2>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mt-2">Enterprise Resource Planning</p>
                </div>
            </div>

            {{-- Login Form --}}
            <form action="/run-login" method="POST" class="p-8 sm:p-10 space-y-6">
                @csrf

                @if($errors->any())
                    <div class="bg-red-50 text-red-600 p-3.5 rounded-xl text-sm font-bold text-center border border-red-100 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ $errors->first() }}
                    </div>
                @endif

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 pl-1">Email Address</label>
                    <input type="email" name="email" required 
                           class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all font-medium text-slate-800 placeholder-slate-400" 
                           placeholder="admin@domain.com">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 pl-1">Password</label>
                    <input type="password" name="password" required 
                           class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all font-medium text-slate-800 placeholder-slate-400" 
                           placeholder="••••••••">
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white font-bold text-base py-4 rounded-xl shadow-lg shadow-blue-600/30 hover:bg-blue-700 hover:shadow-blue-600/40 transition-all active:scale-[0.98] mt-4">
                    Secure Login
                </button>
            </form>
        </div>
        
        {{-- Dynamic Footer --}}
        <p class="text-center text-slate-400 text-xs font-medium mt-8">
            &copy; {{ date('Y') }} {{ $companyName }}.<br>All rights reserved.
        </p>
    </div>

</body>
</html>
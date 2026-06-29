@php
    $globalSetting = \App\Models\Setting::first();
    $companyName = $globalSetting->company_name ?? 'Atik Auto Rice';
    
    // Bypass cPanel limitations for the main sidebar logo
    $sidebarLogo = '';
    if($globalSetting && $globalSetting->logo_path && file_exists(public_path($globalSetting->logo_path))) {
        $type = pathinfo(public_path($globalSetting->logo_path), PATHINFO_EXTENSION);
        $data = file_get_contents(public_path($globalSetting->logo_path));
        $sidebarLogo = 'data:image/' . $type . ';base64,' . base64_encode($data);
    }

    // Bypass cPanel limitations for the Favicon
    $faviconData = '';
    if($globalSetting && $globalSetting->favicon_path && file_exists(public_path($globalSetting->favicon_path))) {
        $type = pathinfo(public_path($globalSetting->favicon_path), PATHINFO_EXTENSION);
        $data = file_get_contents(public_path($globalSetting->favicon_path));
        $faviconData = 'data:image/' . $type . ';base64,' . base64_encode($data);
    }

    // 🚨 SMART MENU DETECTION: Automatically opens the correct dropdown based on URL
    $isOps = request()->is('purchase', 'mill', 'sales', 'sales-return', 'purchase-return', 'stock-adjustment', 'stock', 'edit-item/*', 'item-ledger/*');
    $isFin = request()->is('payment', 'balance-transfer', 'receipt', 'expense', 'other-income');
    $isSys = request()->is('invoices', 'transactions', 'invoice/*');
    $isAdmin = request()->is('report', 'accounts', 'units', 'users', 'settings', 'edit-account/*', 'ledger/*');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Control Center') | {{ $companyName }}</title>
    
    @if($faviconData)
        <link rel="icon" href="{{ $faviconData }}">
    @else
        <link rel="icon" href="data:;base64,iVBORw0KGgo=">
    @endif
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <style>
        /* Custom scrollbar to make the sidebar look sleek */
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        .custom-scrollbar:hover::-webkit-scrollbar-thumb { background: #475569; }
    </style>
</head>
<body class="bg-slate-100 font-sans flex h-screen overflow-hidden relative">

    <div id="sidebar-overlay" class="fixed inset-0 bg-slate-900/50 z-40 hidden transition-opacity md:hidden" onclick="toggleSidebar()"></div>

    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-gray-300 flex flex-col h-full shadow-2xl transition-transform duration-300 ease-in-out -translate-x-full md:relative md:translate-x-0 select-none">
        
        {{-- SIDEBAR HEADER --}}
        <div class="p-5 text-white border-b border-slate-800 flex items-center justify-between gap-2 shrink-0">
            <a href="/" class="flex items-center gap-3 overflow-hidden hover:opacity-80 transition">
                @if($sidebarLogo)
                    <img src="{{ $sidebarLogo }}" alt="Logo" class="h-10 w-auto object-contain shrink-0">
                @else
                    <span class="text-2xl shrink-0">🌾</span>
                @endif
                <span class="tracking-wide font-extrabold text-sm truncate" title="{{ $companyName }}">
                    {{ $companyName }}
                </span>
            </a>
            <button onclick="toggleSidebar()" class="md:hidden text-gray-400 hover:text-white shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        {{-- SMART MENU NAVIGATION --}}
        <nav class="flex-1 overflow-y-auto py-4 space-y-1 custom-scrollbar">
            
            <a href="/" class="flex items-center gap-3 px-6 py-3 hover:bg-slate-800 hover:text-white transition {{ request()->is('/') ? 'bg-slate-800 text-white border-r-4 border-indigo-500' : 'text-slate-300' }}">
                <span class="text-lg">📊</span> <span class="font-bold">Dashboard</span>
            </a>

            {{-- 1. OPERATIONS (Blue) --}}
            <div>
                <button onclick="toggleSubmenu('menu-ops', this)" class="w-full flex items-center justify-between px-6 py-3 text-slate-300 hover:text-white hover:bg-slate-800 transition {{ $isOps ? 'bg-slate-800/80 text-white' : '' }}">
                    <div class="flex items-center gap-3">
                        <span class="text-blue-400 text-lg">📦</span>
                        <span class="font-bold tracking-wide">Operations</span>
                    </div>
                    <svg id="chevron-menu-ops" class="w-4 h-4 transition-transform duration-300 {{ $isOps ? 'rotate-180 text-blue-400' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div id="menu-ops" class="transition-all duration-300 overflow-hidden {{ $isOps ? 'max-h-[500px]' : 'max-h-0' }}">
                    <div class="bg-slate-900/40 py-2 border-l-2 border-blue-500 ml-9 pl-2 space-y-1 mb-2 mt-1">
                        <a href="/purchase" class="block px-4 py-2 text-sm transition {{ request()->is('purchase') ? 'text-blue-400 font-bold' : 'text-slate-400 hover:text-white' }}">📥 Purchase</a>
                        <a href="/mill" class="block px-4 py-2 text-sm transition {{ request()->is('mill') ? 'text-blue-400 font-bold' : 'text-slate-400 hover:text-white' }}">⚙️ Production</a>
                        <a href="/sales" class="block px-4 py-2 text-sm transition {{ request()->is('sales') ? 'text-blue-400 font-bold' : 'text-slate-400 hover:text-white' }}">📤 Sell</a>
                        <a href="/sales-return" class="block px-4 py-2 text-sm transition {{ request()->is('sales-return') ? 'text-blue-400 font-bold' : 'text-slate-400 hover:text-white' }}">↩️ Sales Return</a>
                        <a href="/purchase-return" class="block px-4 py-2 text-sm transition {{ request()->is('purchase-return') ? 'text-blue-400 font-bold' : 'text-slate-400 hover:text-white' }}">↪️ Purchase Return</a>
                        <a href="/stock-adjustment" class="block px-4 py-2 text-sm transition {{ request()->is('stock-adjustment') ? 'text-blue-400 font-bold' : 'text-slate-400 hover:text-white' }}">🛠️ Adjustment</a>
                        <a href="/stock" class="block px-4 py-2 text-sm transition {{ request()->is('stock', 'edit-item/*', 'item-ledger/*') ? 'text-blue-400 font-bold' : 'text-slate-400 hover:text-white' }}">📋 Live Inventory</a>
                    </div>
                </div>
            </div>

            {{-- 2. FINANCIALS (Emerald) --}}
            <div>
                <button onclick="toggleSubmenu('menu-fin', this)" class="w-full flex items-center justify-between px-6 py-3 text-slate-300 hover:text-white hover:bg-slate-800 transition {{ $isFin ? 'bg-slate-800/80 text-white' : '' }}">
                    <div class="flex items-center gap-3">
                        <span class="text-emerald-400 text-lg">💰</span>
                        <span class="font-bold tracking-wide">Financials</span>
                    </div>
                    <svg id="chevron-menu-fin" class="w-4 h-4 transition-transform duration-300 {{ $isFin ? 'rotate-180 text-emerald-400' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div id="menu-fin" class="transition-all duration-300 overflow-hidden {{ $isFin ? 'max-h-[500px]' : 'max-h-0' }}">
                    <div class="bg-slate-900/40 py-2 border-l-2 border-emerald-500 ml-9 pl-2 space-y-1 mb-2 mt-1">
                        <a href="/payment" class="block px-4 py-2 text-sm transition {{ request()->is('payment') ? 'text-emerald-400 font-bold' : 'text-slate-400 hover:text-white' }}">💸 Pay Supplier</a>
                        <a href="/balance-transfer" class="block px-4 py-2 text-sm transition {{ request()->is('balance-transfer') ? 'text-emerald-400 font-bold' : 'text-slate-400 hover:text-white' }}">🔁 Transfer</a>
                        <a href="/receipt" class="block px-4 py-2 text-sm transition {{ request()->is('receipt') ? 'text-emerald-400 font-bold' : 'text-slate-400 hover:text-white' }}">💵 Receive Money</a>
                        <a href="/expense" class="block px-4 py-2 text-sm transition {{ request()->is('expense') ? 'text-emerald-400 font-bold' : 'text-slate-400 hover:text-white' }}">📉 Expense</a>
                        <a href="/other-income" class="block px-4 py-2 text-sm transition {{ request()->is('other-income') ? 'text-emerald-400 font-bold' : 'text-slate-400 hover:text-white' }}">📈 Other Income</a>
                    </div>
                </div>
            </div>
            
            {{-- 3. SYSTEM (Purple) --}}
            <div>
                <button onclick="toggleSubmenu('menu-sys', this)" class="w-full flex items-center justify-between px-6 py-3 text-slate-300 hover:text-white hover:bg-slate-800 transition {{ $isSys ? 'bg-slate-800/80 text-white' : '' }}">
                    <div class="flex items-center gap-3">
                        <span class="text-purple-400 text-lg">📑</span>
                        <span class="font-bold tracking-wide">System Logs</span>
                    </div>
                    <svg id="chevron-menu-sys" class="w-4 h-4 transition-transform duration-300 {{ $isSys ? 'rotate-180 text-purple-400' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div id="menu-sys" class="transition-all duration-300 overflow-hidden {{ $isSys ? 'max-h-[500px]' : 'max-h-0' }}">
                    <div class="bg-slate-900/40 py-2 border-l-2 border-purple-500 ml-9 pl-2 space-y-1 mb-2 mt-1">
                        <a href="/invoices" class="block px-4 py-2 text-sm transition {{ request()->is('invoices', 'invoice/*') ? 'text-purple-400 font-bold' : 'text-slate-400 hover:text-white' }}">🧾 Invoice Center</a>
                        <a href="/transactions" class="block px-4 py-2 text-sm transition {{ request()->is('transactions') ? 'text-purple-400 font-bold' : 'text-slate-400 hover:text-white' }}">📖 Daybook</a>
                    </div>
                </div>
            </div>

            {{-- 4. ADMIN ZONE (Amber) --}}
            @if(auth()->check() && auth()->user()->role === 'admin')
            <div class="pt-2">
                <button onclick="toggleSubmenu('menu-admin', this)" class="w-full flex items-center justify-between px-6 py-3 text-slate-300 hover:text-white hover:bg-slate-800 transition {{ $isAdmin ? 'bg-slate-800/80 text-white' : '' }}">
                    <div class="flex items-center gap-3">
                        <span class="text-amber-400 text-lg">⚙️</span>
                        <span class="font-bold tracking-wide text-amber-500/90">Admin Zone</span>
                    </div>
                    <svg id="chevron-menu-admin" class="w-4 h-4 transition-transform duration-300 {{ $isAdmin ? 'rotate-180 text-amber-400' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div id="menu-admin" class="transition-all duration-300 overflow-hidden {{ $isAdmin ? 'max-h-[500px]' : 'max-h-0' }}">
                    <div class="bg-slate-900/40 py-2 border-l-2 border-amber-500 ml-9 pl-2 space-y-1 mb-2 mt-1">
                        <a href="/report" class="block px-4 py-2 text-sm transition {{ request()->is('report') ? 'text-amber-400 font-bold' : 'text-slate-400 hover:text-white' }}">📈 Master Reports</a>
                        <a href="/accounts" class="block px-4 py-2 text-sm transition {{ request()->is('accounts', 'ledger/*', 'edit-account/*') ? 'text-amber-400 font-bold' : 'text-slate-400 hover:text-white' }}">📒 Ledgers & Accounts</a>
                        <a href="/units" class="block px-4 py-2 text-sm transition {{ request()->is('units') ? 'text-amber-400 font-bold' : 'text-slate-400 hover:text-white' }}">⚖️ Unit Settings</a>
                        <a href="/users" class="block px-4 py-2 text-sm transition {{ request()->is('users') ? 'text-amber-400 font-bold' : 'text-slate-400 hover:text-white' }}">👥 Staff Permissions</a>
                        <a href="/settings" class="block px-4 py-2 text-sm transition {{ request()->is('settings') ? 'text-amber-400 font-bold' : 'text-slate-400 hover:text-white' }}">⚙️ Site Settings</a>
                    </div>
                </div>
            </div>
            @endif
            
            {{-- LOGOUT --}}
            <div class="pt-4 px-6 pb-6">
                <form action="/logout" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 text-rose-400 hover:text-white bg-rose-500/10 hover:bg-rose-500/30 border border-rose-500/20 py-2.5 rounded-lg transition font-bold shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Logout
                    </button>
                </form>
            </div>
        </nav>
    </aside>

    <main class="flex-1 flex flex-col h-screen overflow-y-auto w-full bg-slate-50">
        <header class="bg-white shadow-sm border-b border-gray-200 p-4 sm:px-8 flex justify-between items-center sticky top-0 z-30 print:hidden">
            <div class="flex items-center gap-3 sm:gap-4">
                <button onclick="toggleSidebar()" class="md:hidden p-1 text-gray-600 hover:text-blue-600 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800 truncate">@yield('title', 'Control Center')</h2>
            </div>
            
            @if(auth()->check())
                <div class="text-gray-500 text-xs sm:text-sm font-semibold hidden sm:block">
                    {{ auth()->user()->name }} 
                    <span class="text-blue-600">({{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }})</span>
                </div>
                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm sm:hidden">
                    {{ substr(auth()->user()->name, 0, 2) }}
                </div>
            @endif
        </header>

        <div class="p-4 md:p-8 flex-1">
            @yield('content')
        </div>
    </main>

    <script>
        // Toggle mobile sidebar overlay
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        // Smart Dropdown Engine
        function toggleSubmenu(menuId, btnElement) {
            const menu = document.getElementById(menuId);
            const chevron = document.getElementById('chevron-' + menuId);
            
            if (menu.classList.contains('max-h-0')) {
                // Open Menu
                menu.classList.remove('max-h-0');
                menu.classList.add('max-h-[500px]');
                chevron.classList.add('rotate-180');
                btnElement.classList.add('bg-slate-800/80', 'text-white');
            } else {
                // Close Menu
                menu.classList.add('max-h-0');
                menu.classList.remove('max-h-[500px]');
                chevron.classList.remove('rotate-180');
                btnElement.classList.remove('bg-slate-800/80', 'text-white');
            }
        }
    </script>
</body>
</html>
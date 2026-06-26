<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rice Mill Control Center</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 font-sans flex h-screen overflow-hidden relative">

    <div id="sidebar-overlay" class="fixed inset-0 bg-slate-900/50 z-40 hidden transition-opacity md:hidden" onclick="toggleSidebar()"></div>

    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-gray-300 flex flex-col h-full shadow-2xl transition-transform duration-300 ease-in-out -translate-x-full md:relative md:translate-x-0">
        <div class="p-6 text-xl font-bold text-white border-b border-slate-800 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                🌾 <span class="tracking-wide">Atik Auto Rice</span>
            </div>
            <button onclick="toggleSidebar()" class="md:hidden text-gray-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <nav class="flex-1 overflow-y-auto py-4 space-y-1">
            <a href="/" class="block px-6 py-3 hover:bg-slate-800 hover:text-white transition {{ request()->is('/') ? 'bg-blue-600 text-white' : '' }}">📊 Dashboard</a>
            
            <div class="px-6 pt-4 pb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">Operations</div>
            <a href="/purchase" class="block px-6 py-2.5 hover:bg-slate-800 hover:text-white transition">📥 Buy Paddy</a>
            <a href="/mill" class="block px-6 py-2.5 hover:bg-slate-800 hover:text-white transition">⚙️ Run Mill</a>
            <a href="/sales" class="block px-6 py-2.5 hover:bg-slate-800 hover:text-white transition">📤 Sell Rice</a>
            <a href="/sales-return" class="block px-6 py-2.5 hover:bg-slate-800 hover:text-white transition">↩️ Sales Return</a>
            <a href="/purchase-return" class="block px-6 py-2.5 hover:bg-slate-800 hover:text-white transition">↪️ Purchase Return</a>
            <a href="/stock-adjustment" class="block px-6 py-2.5 hover:bg-slate-800 hover:text-white transition">🛠️ Stock Adjustment</a>
            <a href="/stock" class="block px-6 py-2.5 hover:bg-slate-800 hover:text-white transition">📦 Inventory</a>

            <div class="px-6 pt-4 pb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">Financials</div>
            <a href="/payment" class="block px-6 py-2.5 hover:bg-slate-800 hover:text-white transition">💸 Pay Supplier</a>
            <a href="/balance-transfer" class="block px-6 py-2.5 hover:bg-slate-800 hover:text-white transition">🔁 Balance Transfer</a>
            <a href="/receipt" class="block px-6 py-2.5 hover:bg-slate-800 hover:text-white transition">💰 Receive Money</a>
            <a href="/expense" class="block px-6 py-2.5 hover:bg-slate-800 hover:text-white transition">📉 Log Expense</a>
            <a href="/other-income" class="block px-6 py-2.5 hover:bg-slate-800 hover:text-white transition">💵 Log Other Income</a>
            
            {{-- DAYBOOK IS NOW VISIBLE TO EVERYONE --}}
            <div class="px-6 pt-4 pb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">System</div>
            <a href="/transactions" class="block px-6 py-2.5 hover:bg-slate-800 hover:text-white transition text-blue-400">📖 Daybook</a>

            {{-- 🚨 SECURE ZONE: Only Admins can see these links --}}
            @if(auth()->check() && auth()->user()->role === 'admin')
                <div class="px-6 pt-4 pb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">Admin Zone</div>
                <a href="/report" class="block px-6 py-2.5 hover:bg-slate-800 hover:text-white transition">📈 Master Reports</a>
                <a href="/accounts" class="block px-6 py-2.5 hover:bg-slate-800 hover:text-white transition">📒 Accounts & Ledgers</a>
                <a href="/units" class="block px-6 py-2.5 hover:bg-slate-800 hover:text-white transition">⚖️ Unit Settings</a>
                <a href="/users" class="block px-6 py-2.5 hover:bg-slate-800 hover:text-white transition">👥 Staff & Permissions</a>
            @endif
            
            <div class="px-6 pt-4 pb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">Account</div>
            <form action="/logout" method="POST" class="px-6 py-2.5">
                @csrf
                <button type="submit" class="w-full text-left text-gray-400 hover:text-white transition font-bold">
                    🚪 Logout
                </button>
            </form>
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
            
            {{-- Dynamic User Badge --}}
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
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
    </script>
</body>
</html>
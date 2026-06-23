<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rice Mill Control Center</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 font-sans flex h-screen overflow-hidden">

    <aside class="w-64 bg-slate-900 text-gray-300 flex flex-col h-full shadow-2xl">
        <div class="p-6 text-xl font-bold text-white border-b border-slate-800 flex items-center gap-3">
            🌾 <span class="tracking-wide">Atik Auto Rice</span>
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
            <a href="/report" class="block px-6 py-2.5 hover:bg-slate-800 hover:text-white transition">📈 P&L Report</a>

            <div class="px-6 pt-4 pb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">Directories</div>
            <a href="/accounts" class="block px-6 py-2.5 hover:bg-slate-800 hover:text-white transition">📒 Accounts & Ledgers</a>
            <a href="/items" class="block px-6 py-2.5 hover:bg-slate-800 hover:text-white transition">🌾 Item Variants</a>
            <a href="/units" class="block px-6 py-2.5 hover:bg-slate-800 hover:text-white transition">⚖️ Unit Settings</a>
            
            <div class="px-6 pt-4 pb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">System</div>
            <a href="/transactions" class="block px-6 py-2.5 hover:bg-slate-800 hover:text-white transition text-red-400">📖 Daybook (Edits)</a>
        <div class="px-6 pt-4 pb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">Account</div>
            <form action="/logout" method="POST" class="px-6 py-2.5">
                @csrf
                <button type="submit" class="w-full text-left text-gray-400 hover:text-white transition font-bold">
                    🚪 Logout
                </button>
            </form>
        </nav>
    </aside>

    <main class="flex-1 flex flex-col h-screen overflow-y-auto">
        <header class="bg-white shadow-sm border-b border-gray-200 p-4 px-8 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">@yield('title', 'Control Center')</h2>
            <div class="text-gray-500 text-sm font-semibold">Tusar Ahmmed (Admin)</div>
        </header>

        <div class="p-8">
            @yield('content')
        </div>
    </main>

</body>
</html>
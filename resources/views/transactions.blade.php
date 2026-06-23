@extends('layouts.app')
@section('title', 'Transaction Daybook')
@section('content')
<body class="bg-slate-100 min-h-screen p-4 md:p-8 font-sans">

    <div class="max-w-7xl mx-auto space-y-6 pb-12">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Transaction Daybook</h1>
                <p class="text-gray-500">View and audit detailed system entries</p>
            </div>
            <div class="flex gap-2 items-end print:hidden">
                <form method="GET" action="/transactions" class="flex gap-2 items-end border bg-white p-2 rounded-lg shadow-sm">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">From</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="p-1.5 text-sm border border-gray-200 rounded outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">To</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="p-1.5 text-sm border border-gray-200 rounded outline-none focus:border-blue-500">
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded font-bold text-sm transition">Filter</button>
                </form>
                <a href="/transactions/export?start_date={{ $startDate }}&end_date={{ $endDate }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg font-bold shadow transition">CSV</a>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-800 text-white text-sm">
                        <th class="p-4 font-bold">Date & Time</th>
                        <th class="p-4 font-bold">Type</th>
                        <th class="p-4 font-bold">Reference</th>
                        <th class="p-4 font-bold">Narration</th>
                        <th class="p-4 font-bold text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($vouchers as $voucher)
                    
                    {{-- MAIN TOP-LEVEL ROW --}}
                    <tr class="hover:bg-gray-50 transition cursor-pointer">
                        <td class="p-4 font-bold text-gray-700 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($voucher->voucher_date)->format('d M Y - h:i A') }}
                            <div class="text-[10px] font-mono text-gray-400 mt-1">#VCH-{{ $voucher->id }}</div>
                        </td>
                        <td class="p-4">
                            <span class="px-3 py-1 rounded-full text-xs font-bold 
                                {{ $voucher->voucher_type == 'Purchase' ? 'bg-blue-100 text-blue-700 border border-blue-200' : '' }}
                                {{ $voucher->voucher_type == 'Sales' ? 'bg-purple-100 text-purple-700 border border-purple-200' : '' }}
                                {{ $voucher->voucher_type == 'Production' ? 'bg-green-100 text-green-700 border border-green-200' : '' }}
                                {{ $voucher->voucher_type == 'Receipt' ? 'bg-teal-100 text-teal-700 border border-teal-200' : '' }}
                                {{ $voucher->voucher_type == 'Other Income' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : '' }}
                                {{ $voucher->voucher_type == 'Expense' ? 'bg-rose-100 text-rose-700 border border-rose-200' : '' }}
                                {{ in_array($voucher->voucher_type, ['Opening Balance', 'Journal']) ? 'bg-gray-200 text-gray-700' : '' }}">
                                {{ $voucher->voucher_type }}
                            </span>
                        </td>
                        <td class="p-4 text-gray-600 text-sm font-mono">{{ $voucher->reference_number ?? 'N/A' }}</td>
                        <td class="p-4 text-gray-600 text-sm">{{ $voucher->notes ?? '-' }}</td>
                        <td class="p-4 text-center">
                            <form action="/delete-transaction/{{ $voucher->id }}" method="POST" onsubmit="return confirm('WARNING: Are you sure you want to delete this? This will reverse all physical stock and financial balances attached to this entry.');">
                                @csrf
                                <button type="submit" class="bg-red-50 text-red-600 hover:bg-red-600 hover:text-white font-bold py-1.5 px-3 border border-red-200 hover:border-red-600 rounded text-xs transition">
                                    Reverse Entry
                                </button>
                            </form>
                        </td>
                    </tr>

                    {{-- DETAILED SUB-ROW --}}
                    <tr class="bg-slate-50 border-b-4 border-gray-300">
                        <td colspan="5" class="p-0">
                            <div class="grid grid-cols-1 lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-gray-200">
                                
                                {{-- FINANCIAL LEDGER BREAKDOWN --}}
                                <div class="p-4">
                                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                        Financial Impact (Ledger)
                                    </h4>
                                    @if(isset($entries[$voucher->id]) && count($entries[$voucher->id]) > 0)
                                        <table class="w-full text-xs text-left">
                                            <thead>
                                                <tr class="text-gray-400 border-b border-gray-200">
                                                    <th class="py-1">Account</th>
                                                    <th class="py-1 text-right text-green-600">Debit (In)</th>
                                                    <th class="py-1 text-right text-red-600">Credit (Out)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($entries[$voucher->id] as $entry)
                                                <tr class="border-b border-gray-100 last:border-0">
                                                    <td class="py-1.5 font-semibold text-gray-700">
                                                        {{ $entry->account_name ?? 'Unknown Account' }}
                                                        <span class="block text-[9px] text-gray-400 font-normal">{{ $entry->group_type }}</span>
                                                    </td>
                                                    <td class="py-1.5 text-right font-mono text-green-700 font-medium">
                                                        {{ $entry->entry_type == 'Debit' ? '৳' . number_format($entry->amount, 2) : '-' }}
                                                    </td>
                                                    <td class="py-1.5 text-right font-mono text-red-700 font-medium">
                                                        {{ $entry->entry_type == 'Credit' ? '৳' . number_format($entry->amount, 2) : '-' }}
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <p class="text-xs text-gray-400 italic py-2">No financial movement recorded.</p>
                                    @endif
                                </div>

                                {{-- INVENTORY STOCK BREAKDOWN --}}
                                <div class="p-4">
                                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                        Inventory Impact (Stock)
                                    </h4>
                                    @if(isset($movements[$voucher->id]) && count($movements[$voucher->id]) > 0)
                                        <table class="w-full text-xs text-left">
                                            <thead>
                                                <tr class="text-gray-400 border-b border-gray-200">
                                                    <th class="py-1">Item Variant</th>
                                                    <th class="py-1 text-center">Movement</th>
                                                    <th class="py-1 text-right">Base Qty</th>
                                                    <th class="py-1 text-right">Rate</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($movements[$voucher->id] as $movement)
                                                <tr class="border-b border-gray-100 last:border-0">
                                                    <td class="py-1.5 font-semibold text-gray-700">
                                                        {{ $movement->item_name ?? 'Unknown Item' }}
                                                    </td>
                                                    <td class="py-1.5 text-center">
                                                        @if($movement->movement_type == 'In')
                                                            <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-[10px] font-bold">+ IN</span>
                                                        @else
                                                            <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded text-[10px] font-bold">- OUT</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-1.5 text-right font-mono font-medium text-gray-800">
                                                        {{ number_format($movement->quantity, 2) }} <span class="text-[9px] text-gray-400">KG</span>
                                                    </td>
                                                    <td class="py-1.5 text-right font-mono text-gray-500">
                                                        ৳{{ number_format($movement->rate, 2) }}
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <p class="text-xs text-gray-400 italic py-2">No physical stock movement recorded.</p>
                                    @endif
                                </div>

                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
@endsection
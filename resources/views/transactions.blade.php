@extends('layouts.app')
@section('title', 'Transaction Daybook')
@section('content')

    <div class="max-w-7xl mx-auto space-y-6 pb-12 mt-4 sm:mt-6">
        
        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 tracking-tight">Transaction Daybook</h1>
                <p class="text-gray-500 text-sm sm:text-base mt-1">View and audit detailed system entries</p>
            </div>
            
            <div class="w-full xl:w-auto flex flex-col sm:flex-row gap-3 items-start sm:items-end print:hidden">
                <form method="GET" action="/transactions" class="flex flex-col sm:flex-row gap-3 items-start sm:items-end bg-white p-3 sm:p-4 rounded-xl shadow-sm border border-gray-100 w-full sm:w-auto">
                    <div class="w-full sm:w-auto">
                        <label class="block text-[10px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">From</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="w-full sm:w-auto px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 hover:bg-white transition outline-none text-sm font-medium text-gray-700">
                    </div>
                    <div class="w-full sm:w-auto">
                        <label class="block text-[10px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">To</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="w-full sm:w-auto px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 hover:bg-white transition outline-none text-sm font-medium text-gray-700">
                    </div>
                    <div class="flex gap-2 w-full sm:w-auto mt-2 sm:mt-0">
                        <button type="submit" class="flex-1 sm:flex-none bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-bold shadow-sm transition active:scale-95">Filter</button>
                    </div>
                </form>

                <a href="/transactions/export?start_date={{ $startDate }}&end_date={{ $endDate }}" class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-md transition-all active:scale-95 flex justify-center items-center gap-2 h-full">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    CSV
                </a>
            </div>
        </div>

        @if(session('error'))
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-4 rounded-r-lg shadow-sm">
                <p class="text-yellow-700 font-bold">{{ session('error') }}</p>
            </div>
        @endif

        <div class="bg-transparent md:bg-white rounded-none md:rounded-2xl shadow-none md:shadow-xl overflow-hidden border-none md:border border-gray-200">
            
            <table class="w-full text-left border-collapse hidden md:table">
                <thead>
                    <tr class="bg-slate-800 text-white text-sm uppercase tracking-wider">
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
                    <tr class="hover:bg-gray-50 transition border-t-4 border-gray-300">
                        <td class="p-4 font-bold text-gray-800 whitespace-nowrap align-top">
                            {{ \Carbon\Carbon::parse($voucher->voucher_date)->format('d M Y') }}
                            <div class="text-xs text-gray-500 font-normal mt-0.5">{{ \Carbon\Carbon::parse($voucher->voucher_date)->format('h:i A') }}</div>
                            <div class="text-[10px] font-mono text-gray-400 mt-1 uppercase tracking-wider">#VCH-{{ $voucher->id }}</div>
                        </td>
                        <td class="p-4 align-top">
                            <span class="px-3 py-1 rounded-lg text-xs font-bold inline-block
                                {{ $voucher->voucher_type == 'Purchase' ? 'bg-blue-50 text-blue-700 border border-blue-200' : '' }}
                                {{ $voucher->voucher_type == 'Purchase Return' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : '' }}
                                {{ $voucher->voucher_type == 'Sales' ? 'bg-purple-50 text-purple-700 border border-purple-200' : '' }}
                                {{ $voucher->voucher_type == 'Sales Return' ? 'bg-pink-50 text-pink-700 border border-pink-200' : '' }}
                                {{ $voucher->voucher_type == 'Production' ? 'bg-green-50 text-green-700 border border-green-200' : '' }}
                                {{ $voucher->voucher_type == 'Receipt' ? 'bg-teal-50 text-teal-700 border border-teal-200' : '' }}
                                {{ $voucher->voucher_type == 'Other Income' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' }}
                                {{ $voucher->voucher_type == 'Expense' ? 'bg-rose-50 text-rose-700 border border-rose-200' : '' }}
                                {{ in_array($voucher->voucher_type, ['Journal', 'Stock Adjustment']) ? 'bg-cyan-50 text-cyan-700 border border-cyan-200' : '' }}
                                {{ $voucher->voucher_type == 'Balance Transfer' ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' }}
                                {{ $voucher->voucher_type == 'Opening Balance' ? 'bg-slate-100 text-slate-700 border border-slate-200' : '' }}">
                                
                                {{-- Force Journal to display as Stock Adjustment --}}
                                {{ $voucher->voucher_type == 'Journal' ? 'Stock Adjustment' : $voucher->voucher_type }}
                            </span>
                        </td>
                        <td class="p-4 text-gray-600 text-sm font-mono align-top">{{ $voucher->reference_number ?? 'N/A' }}</td>
                        <td class="p-4 text-gray-600 text-sm align-top max-w-xs truncate" title="{{ $voucher->notes }}">{{ $voucher->notes ?? '-' }}</td>
                        <td class="p-4 text-center align-top">
                            <div class="flex justify-center gap-2">
                                <a href="/edit-transaction/{{ $voucher->id }}" class="bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white font-bold py-1.5 px-3 border border-blue-100 hover:border-blue-600 rounded-lg text-xs transition whitespace-nowrap">
                                    Edit
                                </a>
                                <form action="/delete-transaction/{{ $voucher->id }}" method="POST" onsubmit="return confirm('WARNING: Are you sure you want to delete this? This will reverse all physical stock and financial balances attached to this entry.');">
                                    @csrf
                                    <button type="submit" class="bg-red-50 text-red-600 hover:bg-red-600 hover:text-white font-bold py-1.5 px-3 border border-red-100 hover:border-red-600 rounded-lg text-xs transition whitespace-nowrap">
                                        Reverse
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    {{-- DETAILED SUB-ROW --}}
                    <tr class="bg-slate-50/50">
                        <td colspan="5" class="p-0 border-b border-gray-200">
                            <div class="grid grid-cols-1 lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-gray-200">
                                
                                {{-- FINANCIAL LEDGER BREAKDOWN --}}
                                <div class="p-5">
                                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                        Financial Impact (Ledger)
                                    </h4>
                                    @if(isset($entries[$voucher->id]) && count($entries[$voucher->id]) > 0)
                                        <table class="w-full text-xs text-left border border-gray-100 rounded-lg overflow-hidden bg-white">
                                            <thead class="bg-gray-50">
                                                <tr class="text-gray-500">
                                                    <th class="py-2 px-3">Account</th>
                                                    <th class="py-2 px-3 text-right text-emerald-600">Debit (In)</th>
                                                    <th class="py-2 px-3 text-right text-rose-600">Credit (Out)</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-50">
                                                @foreach($entries[$voucher->id] as $entry)
                                                <tr>
                                                    <td class="py-2 px-3 font-semibold text-gray-700">
                                                        {{ $entry->account_name ?? 'Unknown Account' }}
                                                        <span class="block text-[9px] text-gray-400 font-normal mt-0.5">{{ $entry->group_type }}</span>
                                                    </td>
                                                    <td class="py-2 px-3 text-right font-mono text-emerald-600 font-medium">
                                                        {{ $entry->entry_type == 'Debit' ? '৳' . number_format($entry->amount, 2) : '-' }}
                                                    </td>
                                                    <td class="py-2 px-3 text-right font-mono text-rose-600 font-medium">
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
                                <div class="p-5">
                                    <h4 class="text-xs font-bold text-orange-600 uppercase tracking-widest mb-3 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                        Inventory Impact (Stock)
                                    </h4>
                                    @if(isset($movements[$voucher->id]) && count($movements[$voucher->id]) > 0)
                                        <table class="w-full text-xs text-left border border-gray-100 rounded-lg overflow-hidden bg-white">
                                            <thead class="bg-gray-50">
                                                <tr class="text-gray-500">
                                                    <th class="py-2 px-3">Item Variant</th>
                                                    <th class="py-2 px-3 text-center">Movement</th>
                                                    <th class="py-2 px-3 text-right">Base Qty</th>
                                                    <th class="py-2 px-3 text-right">Rate</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-50">
                                                @foreach($movements[$voucher->id] as $movement)
                                                <tr>
                                                    <td class="py-2 px-3 font-semibold text-gray-700">
                                                        {{ $movement->item_name ?? 'Unknown Item' }}
                                                    </td>
                                                    <td class="py-2 px-3 text-center">
                                                        @if($movement->movement_type == 'In')
                                                            <span class="bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded text-[10px] font-bold">+ IN</span>
                                                        @else
                                                            <span class="bg-rose-100 text-rose-700 px-2 py-0.5 rounded text-[10px] font-bold">- OUT</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-2 px-3 text-right font-mono font-medium text-gray-800">
                                                        {{ number_format($movement->quantity, 2) }} <span class="text-[9px] text-gray-400">KG</span>
                                                    </td>
                                                    <td class="py-2 px-3 text-right font-mono text-gray-500">
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

            <div class="grid grid-cols-1 gap-5 md:hidden">
                @foreach($vouchers as $voucher)
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200 relative overflow-hidden">
                    
                    <div class="flex justify-between items-start mb-4 border-b border-gray-100 pb-4">
                        <div>
                            <p class="text-sm font-bold text-gray-800">{{ \Carbon\Carbon::parse($voucher->voucher_date)->format('d M Y') }}</p>
                            <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($voucher->voucher_date)->format('h:i A') }} • <span class="font-mono text-gray-400">#VCH-{{ $voucher->id }}</span></p>
                        </div>
                        <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider
                                {{ $voucher->voucher_type == 'Purchase' ? 'bg-blue-50 text-blue-700 border border-blue-100' : '' }}
                                {{ $voucher->voucher_type == 'Purchase Return' ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : '' }}
                                {{ $voucher->voucher_type == 'Sales' ? 'bg-purple-50 text-purple-700 border border-purple-100' : '' }}
                                {{ $voucher->voucher_type == 'Sales Return' ? 'bg-pink-50 text-pink-700 border border-pink-100' : '' }}
                                {{ $voucher->voucher_type == 'Production' ? 'bg-green-50 text-green-700 border border-green-100' : '' }}
                                {{ $voucher->voucher_type == 'Receipt' ? 'bg-teal-50 text-teal-700 border border-teal-100' : '' }}
                                {{ $voucher->voucher_type == 'Other Income' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : '' }}
                                {{ $voucher->voucher_type == 'Expense' ? 'bg-rose-50 text-rose-700 border border-rose-100' : '' }}
                                {{ in_array($voucher->voucher_type, ['Journal', 'Stock Adjustment']) ? 'bg-cyan-50 text-cyan-700 border border-cyan-100' : '' }}
                                {{ $voucher->voucher_type == 'Balance Transfer' ? 'bg-amber-50 text-amber-700 border border-amber-100' : '' }}
                                {{ $voucher->voucher_type == 'Opening Balance' ? 'bg-slate-100 text-slate-700 border border-slate-200' : '' }}">
                            
                            {{-- Force Journal to display as Stock Adjustment on Mobile too --}}
                            {{ $voucher->voucher_type == 'Journal' ? 'Stock Adjustment' : $voucher->voucher_type }}
                        </span>
                    </div>

                    <div class="mb-4 grid grid-cols-2 gap-2 text-xs">
                        <div class="col-span-2 sm:col-span-1">
                            <span class="block font-bold text-gray-400 uppercase mb-0.5">Reference</span>
                            <span class="font-mono text-gray-700">{{ $voucher->reference_number ?? 'N/A' }}</span>
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <span class="block font-bold text-gray-400 uppercase mb-0.5">Narration</span>
                            <span class="text-gray-700">{{ $voucher->notes ?? '-' }}</span>
                        </div>
                    </div>

                    @if(isset($entries[$voucher->id]) && count($entries[$voucher->id]) > 0)
                        <div class="bg-slate-50 rounded-xl p-3 mb-3 border border-slate-100">
                            <h4 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 border-b border-slate-200 pb-1 flex items-center gap-1">
                                <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                Finance
                            </h4>
                            <div class="space-y-2">
                                @foreach($entries[$voucher->id] as $entry)
                                <div class="flex justify-between items-start text-xs">
                                    <span class="font-semibold text-gray-700 truncate pr-2">{{ $entry->account_name }}</span>
                                    <span class="font-mono font-bold {{ $entry->entry_type == 'Debit' ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $entry->entry_type == 'Debit' ? '+' : '-' }}৳{{ number_format($entry->amount, 2) }}
                                    </span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(isset($movements[$voucher->id]) && count($movements[$voucher->id]) > 0)
                        <div class="bg-orange-50/30 rounded-xl p-3 mb-4 border border-orange-100">
                            <h4 class="text-[10px] font-bold text-orange-600 uppercase tracking-widest mb-2 border-b border-orange-200 pb-1 flex items-center gap-1">
                                <svg class="w-3 h-3 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                Inventory
                            </h4>
                            <div class="space-y-2">
                                @foreach($movements[$voucher->id] as $movement)
                                <div class="flex justify-between items-start text-xs">
                                    <span class="font-semibold text-gray-700 truncate pr-2">{{ $movement->item_name }}</span>
                                    <span class="font-mono font-bold {{ $movement->movement_type == 'In' ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $movement->movement_type == 'In' ? '+' : '-' }}{{ number_format($movement->quantity, 2) }} kg
                                    </span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mt-4 pt-4 border-t border-gray-100 flex gap-2">
                        <a href="/edit-transaction/{{ $voucher->id }}" class="w-1/2 text-center text-blue-700 bg-blue-50 hover:bg-blue-600 hover:text-white transition py-3 rounded-xl font-bold border border-blue-100 text-sm">
                            Edit
                        </a>
                        <form action="/delete-transaction/{{ $voucher->id }}" method="POST" onsubmit="return confirm('WARNING: Reverse all physical stock and financial balances attached to this entry?');" class="w-1/2">
                            @csrf
                            <button type="submit" class="w-full text-center text-red-700 bg-red-50 hover:bg-red-600 hover:text-white transition py-3 rounded-xl font-bold border border-red-100 text-sm">
                                Reverse
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>

        </div>
    </div>
@endsection
@extends('layouts.app')
@section('title', 'Stock Management')
@section('content')

    <div class="max-w-6xl mx-auto space-y-6 pb-12 mt-4 sm:mt-6">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 tracking-tight">Live Warehouse Stock</h1>
                <p class="text-gray-500 text-sm sm:text-base mt-1">Real-time inventory levels & asset valuation</p>
            </div>
            <div class="w-full md:w-auto">
                <a href="/mill" class="w-full md:w-auto flex justify-center items-center gap-2 bg-slate-800 hover:bg-slate-900 text-white font-bold py-3 px-6 rounded-xl shadow-md transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    New Milling Batch
                </a>
            </div>
        </div>

        @php
            // 🚨 FIXED: Now accurately calculates Total Valuation based on correct unit conversion!
            $totalWarehouseValue = $items->sum(function($item) use ($units) {
                $itemUnitStr = $item->unit ?? 'KG';
                $unitObj = $units->first(function($u) use ($itemUnitStr) {
                    return ($u->short_name ?? $u->name) == $itemUnitStr;
                });
                $conversionRate = ($unitObj && $unitObj->conversion_rate > 0) ? $unitObj->conversion_rate : 1;
                
                $actualQty = $item->current_stock / $conversionRate;
                return $actualQty * $item->purchase_rate;
            });
        @endphp

        <div class="bg-gradient-to-r from-slate-800 to-slate-900 text-white p-6 sm:p-8 rounded-2xl shadow-lg flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 sm:gap-0">
            <div>
                <p class="text-xs sm:text-sm uppercase tracking-widest text-slate-400 font-bold mb-1">Total Estimated Inventory Value</p>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-emerald-400">
                    ৳ {{ number_format($totalWarehouseValue, 2) }}
                </h2>
            </div>
            <div class="bg-slate-800/50 px-6 py-4 rounded-xl border border-slate-700 w-full sm:w-auto flex justify-between sm:block items-center">
                <p class="text-sm text-slate-400 font-semibold sm:mb-1">Tracked Items</p>
                <p class="text-2xl font-bold text-white">{{ $items->count() }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-gray-50/80 border-b border-gray-100 px-6 py-5 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                <h3 class="font-bold text-gray-800 tracking-wide text-sm sm:text-base">Add New Stock Item / Product Variant</h3>
            </div>
            
            <form method="POST" action="/items" class="p-6 sm:p-8 space-y-6">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Item Name</label>
                        <input type="text" name="name" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 text-gray-800 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all duration-200" placeholder="e.g. Miniket Rice (50KG)">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Category</label>
                        <select name="category" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 text-gray-800 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all duration-200 cursor-pointer appearance-none">
                            <option value="Raw Material">Raw Material</option>
                            <option value="Finished Goods">Finished Goods</option>
                            <option value="Byproduct">Byproduct</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Base Unit</label>
                        <select name="unit" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 text-gray-800 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all duration-200 cursor-pointer appearance-none">
                            <option value="" disabled selected>Select Unit...</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->short_name ?? $unit->name }}">{{ $unit->name }} ({{ $unit->short_name ?? '' }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Opening Stock (QTY)</label>
                        <input type="number" step="0.01" name="opening_stock" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 text-gray-800 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all duration-200" placeholder="Leave blank if 0">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Purchase Cost / Rate Per Unit (৳)</label>
                        <input type="number" step="0.01" name="purchase_rate" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 text-gray-800 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all duration-200" placeholder="Leave blank if 0">
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-100">
                    <button type="submit" class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl shadow-md transition-all active:scale-95 flex justify-center items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                        Save Stock Item
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-transparent md:bg-white rounded-none md:rounded-2xl shadow-none md:shadow-sm border-none md:border border-gray-100 overflow-hidden">
            <table class="w-full text-left border-collapse hidden md:table">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-xs uppercase tracking-wider text-gray-500">
                        <th class="p-4 font-bold">Item Name</th>
                        <th class="p-4 font-bold">Category</th>
                        <th class="p-4 font-bold text-right">Current Qty</th>
                        <th class="p-4 font-bold text-right">Cost Rate</th>
                        <th class="p-4 font-bold text-right">Valuation (৳)</th>
                        <th class="p-4 font-bold text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @foreach($items as $item)
                        {{-- 🚨 FIXED SMART DISPLAY MATH LOGIC --}}
                        @php
                            $itemUnitStr = $item->unit ?? 'KG';
                            $unitObj = $units->first(function($u) use ($itemUnitStr) {
                                return ($u->short_name ?? $u->name) == $itemUnitStr;
                            });
                            $conversionRate = ($unitObj && $unitObj->conversion_rate > 0) ? $unitObj->conversion_rate : 1;
                            
                            $displayQty = $item->current_stock / $conversionRate;
                            // Removed the faulty multiplier!
                            $displayRate = $item->purchase_rate; 
                            $valuation = $displayQty * $displayRate;
                        @endphp

                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-4 font-bold text-gray-800">
                                <a href="/item-ledger/{{ $item->id }}" class="hover:text-blue-600 transition flex items-center gap-2">
                                    {{ $item->name }}
                                </a>
                            </td>
                            <td class="p-4">
                                <span class="px-3 py-1.5 rounded-lg text-xs font-bold inline-flex items-center gap-1
                                    {{ $item->category == 'Raw Material' ? 'bg-red-50 text-red-700 border border-red-100' : '' }}
                                    {{ $item->category == 'Finished Goods' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : '' }}
                                    {{ $item->category == 'Byproduct' ? 'bg-amber-50 text-amber-700 border border-amber-100' : '' }}">
                                    @if($item->category == 'Raw Material') <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> @endif
                                    @if($item->category == 'Finished Goods') <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> @endif
                                    @if($item->category == 'Byproduct') <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> @endif
                                    {{ $item->category }}
                                </span>
                            </td>
                            <td class="p-4 text-right font-mono font-medium text-gray-900 text-base">
                                {{ number_format($displayQty, 2) }} <span class="text-xs text-gray-400 ml-1">{{ $itemUnitStr }}</span>
                            </td>
                            <td class="p-4 text-right font-mono text-gray-500">
                                ৳{{ number_format($displayRate, 2) }} <span class="text-[10px] text-gray-400">/ {{ $itemUnitStr }}</span>
                            </td>
                            <td class="p-4 text-right font-mono font-bold text-slate-800 text-base">
                                ৳{{ number_format($valuation, 2) }}
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex justify-center items-center gap-2">
                                    <a href="/edit-item/{{ $item->id }}" class="text-blue-600 hover:text-white bg-blue-50 hover:bg-blue-600 px-3 py-1.5 rounded-lg font-semibold transition border border-blue-100 hover:border-blue-600 text-xs">Edit</a>
                                    <a href="/delete-item/{{ $item->id }}" onclick="return confirm('Delete this item?')" class="text-red-600 hover:text-white bg-red-50 hover:bg-red-600 px-3 py-1.5 rounded-lg font-semibold transition border border-red-100 hover:border-red-600 text-xs">Del</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- MOBILE VIEW --}}
            <div class="grid grid-cols-1 gap-4 md:hidden">
                @foreach($items as $item)
                    @php
                        $itemUnitStr = $item->unit ?? 'KG';
                        $unitObj = $units->first(function($u) use ($itemUnitStr) {
                            return ($u->short_name ?? $u->name) == $itemUnitStr;
                        });
                        $conversionRate = ($unitObj && $unitObj->conversion_rate > 0) ? $unitObj->conversion_rate : 1;
                        
                        $displayQty = $item->current_stock / $conversionRate;
                        $displayRate = $item->purchase_rate;
                        $valuation = $displayQty * $displayRate;
                    @endphp
                    
                    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 relative">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <a href="/item-ledger/{{ $item->id }}" class="text-lg font-bold text-gray-800 hover:text-blue-600">{{ $item->name }}</a>
                                <div class="mt-1">
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider inline-flex items-center gap-1
                                        {{ $item->category == 'Raw Material' ? 'bg-red-50 text-red-700' : '' }}
                                        {{ $item->category == 'Finished Goods' ? 'bg-emerald-50 text-emerald-700' : '' }}
                                        {{ $item->category == 'Byproduct' ? 'bg-amber-50 text-amber-700' : '' }}">
                                        {{ $item->category }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="text-xs text-gray-400 font-bold uppercase mb-1">Total Value</span>
                                <span class="font-mono font-extrabold text-slate-800 text-lg">৳{{ number_format($valuation, 2) }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 bg-gray-50 rounded-xl p-3 mb-4 border border-gray-100">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Stock Level</p>
                                <p class="font-mono font-bold text-gray-900 text-base">{{ number_format($displayQty, 2) }} <span class="text-xs text-gray-500 font-normal">{{ $itemUnitStr }}</span></p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Unit Rate</p>
                                <p class="font-mono font-semibold text-gray-700 text-base">৳{{ number_format($displayRate, 2) }} <span class="text-[10px] text-gray-400 font-normal">/ {{ $itemUnitStr }}</span></p>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <a href="/edit-item/{{ $item->id }}" class="flex-1 text-center text-blue-700 bg-blue-50 py-2.5 rounded-xl font-bold border border-blue-100 text-sm active:bg-blue-100">Edit</a>
                            <a href="/delete-item/{{ $item->id }}" onclick="return confirm('Delete this item?')" class="flex-1 text-center text-red-700 bg-red-50 py-2.5 rounded-xl font-bold border border-red-100 text-sm active:bg-red-100">Delete</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
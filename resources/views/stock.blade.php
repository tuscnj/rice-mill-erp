@extends('layouts.app')
@section('title', 'Stock Management')
@section('content')

    <div class="max-w-5xl mx-auto space-y-6 pb-12">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Live Warehouse Stock</h1>
                <p class="text-gray-500">Real-time inventory levels & asset valuation</p>
            </div>
            <div class="flex gap-2">
                <a href="/mill" class="bg-slate-800 hover:bg-slate-900 text-white font-bold py-2 px-4 rounded-lg shadow transition">
                    + New Milling Batch
                </a>
            </div>
        </div>

        <div class="bg-slate-900 text-white p-6 rounded-2xl shadow-xl flex justify-between items-center">
            <div>
                <p class="text-xs uppercase tracking-widest text-slate-400 font-bold">Total Estimated Inventory Value</p>
                <h2 class="text-4xl font-extrabold mt-1">
                    ৳ {{ number_format($items->sum(function($item) { return $item->current_stock * $item->purchase_rate; }), 2) }}
                </h2>
            </div>
            <div class="text-right hidden sm:block">
                <p class="text-sm text-slate-400">Total Tracked Items</p>
                <p class="text-2xl font-bold">{{ $items->count() }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                <h3 class="font-bold text-gray-700">Add New Stock Item / Product Variant</h3>
            </div>
            <form method="POST" action="/items" class="p-6 space-y-4">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Item Name</label>
                        <input type="text" name="name" required class="w-full p-2.5 border border-gray-300 rounded focus:border-slate-500 outline-none text-sm" placeholder="e.g. Miniket Rice (50KG)">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Category</label>
                        <select name="category" required class="w-full p-2.5 border border-gray-300 rounded focus:border-slate-500 bg-white outline-none text-sm">
                            <option value="Raw Material">Raw Material</option>
                            <option value="Finished Goods">Finished Goods</option>
                            <option value="Byproduct">Byproduct</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Base Unit</label>
                        <select name="unit" required class="w-full p-2.5 border border-gray-300 rounded focus:border-slate-500 bg-white outline-none text-sm">
                            <option value="" disabled selected>Select Unit...</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->short_name ?? $unit->name }}">{{ $unit->name }} ({{ $unit->short_name ?? '' }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Opening Stock (QTY)</label>
                        <input type="number" step="0.01" name="opening_stock" class="w-full p-2.5 border border-gray-300 rounded focus:border-slate-500 outline-none text-sm" placeholder="Leave blank if 0">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Purchase Cost / Rate Per Unit (৳)</label>
                        <input type="number" step="0.01" name="purchase_rate" class="w-full p-2.5 border border-gray-300 rounded focus:border-slate-500 outline-none text-sm" placeholder="Leave blank if 0">
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow transition text-sm">
                        Save Stock Item
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
            <div class="p-0 overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-gray-200 text-sm">
                            <th class="p-4 font-bold text-gray-600">Item Name</th>
                            <th class="p-4 font-bold text-gray-600">Category</th>
                            <th class="p-4 font-bold text-gray-600 text-right">Current Qty</th>
                            <th class="p-4 font-bold text-gray-600 text-right">Cost Rate</th>
                            <th class="p-4 font-bold text-gray-600 text-right">Valuation (৳)</th>
                            <th class="p-4 font-bold text-gray-600 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @foreach($items as $item)
                        
                        {{-- SMART DISPLAY CONVERSION LOGIC --}}
                        @php
                            $itemUnitStr = $item->unit ?? 'KG';
                            $unitObj = $units->first(function($u) use ($itemUnitStr) {
                                return ($u->short_name ?? $u->name) == $itemUnitStr;
                            });
                            // Default conversion rate is 1 if unit isn't found
                            $conversionRate = ($unitObj && $unitObj->conversion_rate > 0) ? $unitObj->conversion_rate : 1;
                            
                            $displayQty = $item->current_stock / $conversionRate;
                            $displayRate = $item->purchase_rate * $conversionRate;
                        @endphp

                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-4 font-semibold text-gray-800">
                                <a href="/item-ledger/{{ $item->id }}" class="text-slate-900 hover:text-blue-600 hover:underline">
                                    {{ $item->name }}
                                </a>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold 
                                    {{ $item->category == 'Raw Material' ? 'bg-red-50 text-red-700 border border-red-200' : '' }}
                                    {{ $item->category == 'Finished Goods' ? 'bg-green-50 text-green-700 border border-green-200' : '' }}
                                    {{ $item->category == 'Byproduct' ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' }}">
                                    {{ $item->category }}
                                </span>
                            </td>
                            <td class="p-4 text-right font-mono font-medium text-gray-900">
                                {{ number_format($displayQty, 2) }} <span class="text-xs text-gray-400">{{ $itemUnitStr }}</span>
                            </td>
                            <td class="p-4 text-right font-mono text-gray-600">
                                ৳{{ number_format($displayRate, 2) }} <span class="text-[10px] text-gray-400">/ {{ $itemUnitStr }}</span>
                            </td>
                            <td class="p-4 text-right font-mono font-bold text-slate-900">
                                ৳{{ number_format($item->current_stock * $item->purchase_rate, 2) }}
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex justify-center items-center gap-2">
                                    <a href="/edit-item/{{ $item->id }}" class="text-blue-600 hover:text-blue-800 font-bold bg-blue-50 px-3 py-1.5 rounded-md border border-blue-100 hover:bg-blue-100 transition">
                                        Edit
                                    </a>
                                    <a href="/delete-item/{{ $item->id }}" onclick="return confirm('Are you sure you want to delete this item? This cannot be undone.')" class="text-red-600 hover:text-red-800 font-bold bg-red-50 px-3 py-1.5 rounded-md border border-red-100 hover:bg-red-100 transition">
                                        Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
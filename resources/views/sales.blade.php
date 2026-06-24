@extends('layouts.app')
@section('title', 'Sell Items')
@section('content')

    <div class="bg-white w-full max-w-4xl mx-auto rounded-2xl shadow-sm border border-gray-100 overflow-hidden mt-6">
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-purple-700 to-purple-800 p-6 text-center sm:text-left sm:px-8">
            <h2 class="text-2xl font-extrabold text-white tracking-tight">Sell Inventory</h2>
            <p class="text-purple-100 text-sm mt-1">Create customer invoice for one or more items</p>
        </div>

        <form action="/run-sales" method="POST" class="p-6 sm:p-8 space-y-8">
            @csrf
            
<div class="mb-4">
    <label class="block text-sm font-bold text-gray-700 mb-1">Transaction Date</label>
    <input type="date" name="voucher_date" value="{{ date('Y-m-d') }}" 
           class="w-full p-3 border-2 border-gray-200 rounded-lg focus:border-blue-500">
</div>

            <!-- Party Selection -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Select Party (Customer / Supplier)</label>
                <select name="party_id" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 text-gray-800 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition-all duration-200 cursor-pointer appearance-none">
                    <option value="" disabled selected>Choose a party...</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }} (Customer)</option>
                    @endforeach
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }} (Supplier)</option>
                    @endforeach
                </select>
            </div>

            <!-- Items Section -->
            <div class="bg-white border-2 border-purple-50 rounded-2xl p-5 sm:p-6 shadow-sm">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-5 gap-3 sm:gap-0">
                    <h3 class="font-bold text-gray-800 text-base flex items-center gap-2">
                        <span class="w-2 h-6 bg-purple-600 rounded-full"></span>
                        ITEMS TO SELL
                    </h3>
                    <button type="button" onclick="addSaleRow()" class="w-full sm:w-auto text-sm font-semibold text-purple-700 bg-purple-50 hover:bg-purple-100 px-4 py-2 rounded-lg transition-colors flex justify-center items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Add Another Item
                    </button>
                </div>

                <div id="sale-container" class="space-y-4">
                    <!-- Initial Item Row -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 p-4 bg-gray-50 rounded-xl border border-gray-100 relative">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Item</label>
                            <select name="item_id[]" required class="w-full px-3 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200">
                                <option value="" disabled selected>Select item...</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }} (Stock: {{ number_format($item->current_stock, 0) }} KG)</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Qty</label>
                            <input type="number" name="quantity[]" required class="w-full px-3 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200" placeholder="e.g. 10">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Unit</label>
                            <select name="unit_id[]" required class="w-full px-3 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200">
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->short_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Rate / Unit</label>
                            <input type="number" step="0.01" name="rate[]" required class="w-full px-3 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200" placeholder="e.g. 3000">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Optional Details Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Invoice Number</label>
                    <input type="text" name="invoice_number" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition-all duration-200" placeholder="Optional">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Narration</label>
                    <input type="text" name="narration" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition-all duration-200" placeholder="e.g. Sale to customer on credit">
                </div>
            </div>

            <hr class="border-gray-200">

            <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold text-lg py-4 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 active:scale-[0.99] flex justify-center items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Confirm Sale
            </button>
        </form>
    </div>

    <script>
        const saleItemOptions = `@foreach($items as $item)<option value="{{ $item->id }}">{{ $item->name }} (Stock: {{ number_format($item->current_stock, 0) }} KG)</option>@endforeach`;
        const saleUnitOptions = `@foreach($units as $unit)<option value="{{ $unit->id }}">{{ $unit->short_name }}</option>@endforeach`;

        function addSaleRow() {
            const html = `
                <div class="sale-row grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 p-4 bg-gray-50 rounded-xl border border-gray-200 relative animate-fade-in mt-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Item</label>
                        <select name="item_id[]" required class="w-full px-3 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200">
                            <option value="" disabled selected>Select item...</option>
                            ${saleItemOptions}
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Qty</label>
                        <input type="number" name="quantity[]" required class="w-full px-3 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200" placeholder="e.g. 10">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Unit</label>
                        <select name="unit_id[]" required class="w-full px-3 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200">
                            ${saleUnitOptions}
                        </select>
                    </div>
                    <div class="flex gap-2 items-end">
                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Rate / Unit</label>
                            <input type="number" step="0.01" name="rate[]" required class="w-full px-3 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200" placeholder="e.g. 3000">
                        </div>
                        <button type="button" onclick="this.closest('.sale-row').remove()" class="bg-red-50 text-red-500 hover:bg-red-500 hover:text-white px-3 py-2.5 rounded-lg font-bold border border-red-100 hover:border-red-500 transition-all duration-200" title="Remove Row">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </div>
            `;
            document.getElementById('sale-container').insertAdjacentHTML('beforeend', html);
        }
    </script>

    <style>
        .animate-fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
@endsection
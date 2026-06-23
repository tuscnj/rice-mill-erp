@extends('layouts.app')
@section('title', 'Sell Items')
@section('content')

    <div class="bg-white w-full max-w-4xl rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-purple-700 p-4 text-center">
            <h2 class="text-2xl font-bold text-white">Sell Inventory</h2>
            <p class="text-purple-200 text-sm">Create customer invoice for one or more items</p>
        </div>

        <form action="/run-sales" method="POST" class="p-6 space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Select Party (Customer / Supplier)</label>
                <select name="party_id" required class="w-full p-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-purple-500 text-lg bg-white">
                    <option value="" disabled selected>Choose a party...</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }} (Customer)</option>
                    @endforeach
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }} (Supplier)</option>
                    @endforeach
                </select>
            </div>

            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-bold text-gray-700 uppercase text-sm tracking-wider">Items to Sell</h3>
                    <button type="button" onclick="addSaleRow()" class="text-sm font-bold text-purple-600 hover:text-purple-800 bg-purple-100 px-3 py-1 rounded-full">+ Add Another</button>
                </div>

                <div id="sale-container" class="space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Item</label>
                            <select name="item_id[]" required class="w-full p-2 border border-gray-300 rounded focus:border-purple-500 bg-white">
                                <option value="" disabled selected>Select item...</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }} (Stock: {{ number_format($item->current_stock, 0) }} KG)</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Qty</label>
                            <input type="number" name="quantity[]" required class="w-full p-2 border border-gray-300 rounded focus:border-purple-500" placeholder="e.g. 10">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Unit</label>
                            <select name="unit_id[]" required class="w-full p-2 border border-gray-300 rounded focus:border-purple-500 bg-white">
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->short_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Rate / Unit</label>
                            <input type="number" step="0.01" name="rate[]" required class="w-full p-2 border border-gray-300 rounded focus:border-purple-500" placeholder="e.g. 3000">
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Invoice Number</label>
                    <input type="text" name="invoice_number" class="w-full p-3 border-2 border-gray-200 rounded-lg focus:border-purple-500" placeholder="Optional">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Narration</label>
                    <input type="text" name="narration" class="w-full p-3 border-2 border-gray-200 rounded-lg focus:border-purple-500" placeholder="e.g. Sale to customer on credit">
                </div>
            </div>

            <button type="submit" class="w-full bg-purple-700 text-white font-bold text-lg py-3 rounded-lg shadow-lg hover:bg-purple-800 transition mt-4">
                Confirm Sale
            </button>
        </form>
    </div>

    <script>
        const saleItemOptions = `@foreach($items as $item)<option value="{{ $item->id }}">{{ $item->name }} (Stock: {{ number_format($item->current_stock, 0) }} KG)</option>@endforeach`;
        const saleUnitOptions = `@foreach($units as $unit)<option value="{{ $unit->id }}">{{ $unit->short_name }}</option>@endforeach`;

        function addSaleRow() {
            const html = `
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 pt-3 border-t border-gray-200">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Item</label>
                        <select name="item_id[]" required class="w-full p-2 border border-gray-300 rounded focus:border-purple-500 bg-white">
                            <option value="" disabled selected>Select item...</option>
                            ${saleItemOptions}
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Qty</label>
                        <input type="number" name="quantity[]" required class="w-full p-2 border border-gray-300 rounded focus:border-purple-500" placeholder="e.g. 10">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Unit</label>
                        <select name="unit_id[]" required class="w-full p-2 border border-gray-300 rounded focus:border-purple-500 bg-white">
                            ${saleUnitOptions}
                        </select>
                    </div>
                    <div class="flex gap-2 items-end">
                        <div class="flex-1">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Rate / Unit</label>
                            <input type="number" step="0.01" name="rate[]" required class="w-full p-2 border border-gray-300 rounded focus:border-purple-500" placeholder="e.g. 3000">
                        </div>
                        <button type="button" onclick="this.parentElement.parentElement.remove()" class="bg-red-100 text-red-600 px-3 py-2 rounded font-bold hover:bg-red-200" title="Remove Row">X</button>
                    </div>
                </div>
            `;
            document.getElementById('sale-container').insertAdjacentHTML('beforeend', html);
        }
    </script>
@endsection
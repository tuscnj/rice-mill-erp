@extends('layouts.app')
@section('title', 'Edit Sales Invoice')
@section('content')
    <div class="bg-white w-full max-w-4xl mx-auto rounded-2xl shadow-xl overflow-hidden mb-20 mt-6">
        <div class="bg-purple-600 p-4 flex justify-between items-center text-center md:text-left">
            <div>
                <h2 class="text-2xl font-bold text-white">Edit Sale #VCH-{{ $voucher->id }}</h2>
                <p class="text-purple-200 text-sm">Modify quantities, rates, or customer</p>
            </div>
            <a href="/transactions" class="bg-purple-800 text-white px-4 py-2 rounded-lg font-bold hover:bg-purple-900 transition text-sm">Cancel</a>
        </div>

        <form action="/update-sales/{{ $voucher->id }}" method="POST" class="p-6 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 p-4 rounded-xl border border-gray-200">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Customer (Debtor) <span class="text-red-500">*</span></label>
                    <select name="party_id" required class="w-full p-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 bg-white">
                        <option value="" disabled>Select Customer...</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ $partyId == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Invoice / Reference Number</label>
                    <input type="text" name="invoice_number" value="{{ $voucher->reference_number }}" class="w-full p-3 border-2 border-gray-200 rounded-lg focus:border-purple-500" placeholder="Optional">
                </div>
            </div>

            <div>
                <div class="flex justify-between items-center mb-2">
                    <h3 class="font-bold text-gray-800">Sold Items</h3>
                    <button type="button" onclick="addItemRow()" class="text-sm font-bold text-purple-600 hover:text-purple-800 bg-purple-100 px-3 py-1 rounded-full transition">+ Add Row</button>
                </div>
                
                <div id="items-container" class="space-y-3">
                    @foreach($voucher->inventoryMovements as $movement)
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 item-row bg-gray-50 p-3 rounded-lg border border-gray-200 items-end">
                        <div class="md:col-span-5">
                            <label class="block text-xs font-bold text-gray-500 mb-1">Item</label>
                            <select name="item_id[]" required class="w-full p-2 border border-gray-300 rounded focus:border-purple-500 bg-white">
                                <option value="" disabled>Select Item...</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}" {{ $movement->item_id == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-xs font-bold text-gray-500 mb-1">Quantity</label>
                            <input type="number" step="0.01" name="quantity[]" value="{{ $movement->quantity }}" required class="w-full p-2 border border-gray-300 rounded focus:border-purple-500" placeholder="Qty">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 mb-1">Unit</label>
                            <select name="unit_id[]" required class="w-full p-2 border border-gray-300 rounded focus:border-purple-500 bg-white">
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ $unit->conversion_rate == 1 ? 'selected' : '' }}>{{ $unit->short_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2 flex gap-2">
                            <div class="w-full">
                                <label class="block text-xs font-bold text-gray-500 mb-1">Rate</label>
                                <input type="number" step="0.01" name="rate[]" value="{{ $movement->rate }}" required class="w-full p-2 border border-gray-300 rounded focus:border-purple-500 font-bold" placeholder="৳">
                            </div>
                            <button type="button" onclick="this.parentElement.parentElement.remove()" class="bg-red-100 text-red-600 px-3 rounded font-bold hover:bg-red-200 h-[38px] mt-[20px]" title="Remove Row">X</button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Narration / Notes</label>
                <input type="text" name="narration" value="{{ $voucher->notes }}" class="w-full p-3 border-2 border-gray-200 rounded-lg focus:border-purple-500" placeholder="e.g. Parcel must be checked in front of the delivery person.">
            </div>

            <button type="submit" class="w-full bg-purple-600 text-white font-bold text-lg py-4 rounded-lg shadow-xl hover:bg-purple-700 transition mt-4">
                Update & Recalculate Sale
            </button>
        </form>
    </div>

    <script>
        const itemOptions = `@foreach($items as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach`;
        const unitOptions = `@foreach($units as $unit)<option value="{{ $unit->id }}" {{ $unit->name == 'KG' ? 'selected' : '' }}>{{ $unit->short_name }}</option>@endforeach`;

        function addItemRow() {
            const row = document.createElement('div');
            row.className = "grid grid-cols-1 md:grid-cols-12 gap-3 item-row bg-gray-50 p-3 rounded-lg border border-gray-200 items-end";
            row.innerHTML = `
                <div class="md:col-span-5">
                    <select name="item_id[]" required class="w-full p-2 border border-gray-300 rounded focus:border-purple-500 bg-white">
                        <option value="" disabled selected>Select Item...</option>
                        ${itemOptions}
                    </select>
                </div>
                <div class="md:col-span-3">
                    <input type="number" step="0.01" name="quantity[]" required class="w-full p-2 border border-gray-300 rounded focus:border-purple-500" placeholder="Qty">
                </div>
                <div class="md:col-span-2">
                    <select name="unit_id[]" required class="w-full p-2 border border-gray-300 rounded focus:border-purple-500 bg-white">
                        ${unitOptions}
                    </select>
                </div>
                <div class="md:col-span-2 flex gap-2">
                    <input type="number" step="0.01" name="rate[]" required class="w-full p-2 border border-gray-300 rounded focus:border-purple-500 font-bold" placeholder="৳">
                    <button type="button" onclick="this.parentElement.parentElement.remove()" class="bg-red-100 text-red-600 px-3 rounded font-bold hover:bg-red-200 h-[38px]" title="Remove Row">X</button>
                </div>
            `;
            document.getElementById('items-container').appendChild(row);
        }
    </script>
@endsection
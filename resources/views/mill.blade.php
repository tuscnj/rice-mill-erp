@extends('layouts.app')
@section('title', 'Run Mill Production')
@section('content')
    <div class="bg-white w-full max-w-4xl mx-auto rounded-2xl shadow-xl overflow-hidden mb-20">
        <div class="bg-green-600 p-4 text-center">
            <h2 class="text-2xl font-bold text-white">Run Mill Production</h2>
            <p class="text-green-200 text-sm">Convert raw paddy into multiple finished goods with manual valuation</p>
        </div>

        <form action="/run-mill" method="POST" class="p-6 space-y-6">
            @csrf

            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 border-l-4 border-l-red-500">
                <h3 class="font-bold text-gray-700 mb-3 uppercase text-sm tracking-wider">Input: Raw Material</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 raw-row">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1">Item</label>
                        <select name="raw_item_id" id="raw_item" required class="w-full p-2 border border-gray-300 rounded focus:border-green-500 bg-white auto-calc-item">
                            <option value="" disabled selected>Select Paddy...</option>
                            @foreach($rawMaterials as $item)
                                <option value="{{ $item->id }}">{{ $item->name }} (Stock: {{ number_format($item->current_stock, 0) }} KG)</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1">Qty Used</label>
                        <input type="number" step="0.01" name="raw_quantity" required class="w-full p-2 border border-gray-300 rounded focus:border-green-500" placeholder="Qty">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1">Unit</label>
                        <select name="raw_unit_id" id="raw_unit" required class="w-full p-2 border border-gray-300 rounded focus:border-green-500 bg-white auto-calc-unit">
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ $unit->name == 'Mon' ? 'selected' : '' }}>{{ $unit->short_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1">Cost Rate / Unit</label>
                        <input type="number" step="0.01" name="raw_rate" id="raw_rate" required class="w-full p-2 border border-gray-300 rounded focus:border-green-500 font-bold text-red-700 bg-red-50 auto-calc-rate" placeholder="৳">
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 border-l-4 border-l-green-500">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-bold text-gray-700 uppercase text-sm tracking-wider">Output: Finished Goods</h3>
                    <button type="button" onclick="addRiceRow()" class="text-sm font-bold text-green-600 hover:text-green-800 bg-green-100 px-3 py-1 rounded-full">+ Add Another</button>
                </div>
                
                <div id="rice-container" class="space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 rice-row">
                        <div class="md:col-span-4">
                            <label class="block text-xs font-bold text-gray-500 mb-1">Item</label>
                            <select name="rice_item_id[]" required class="w-full p-2 border border-gray-300 rounded focus:border-green-500 bg-white auto-calc-item">
                                <option value="" disabled selected>Select Rice...</option>
                                @foreach($finishedGoods as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-xs font-bold text-gray-500 mb-1">Qty Produced</label>
                            <input type="number" step="0.01" name="rice_quantity[]" required class="w-full p-2 border border-gray-300 rounded focus:border-green-500" placeholder="Qty">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 mb-1">Unit</label>
                            <select name="rice_unit_id[]" required class="w-full p-2 border border-gray-300 rounded focus:border-green-500 bg-white auto-calc-unit">
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ $unit->name == 'KG' ? 'selected' : '' }}>{{ $unit->short_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-xs font-bold text-gray-500 mb-1">Value / Unit</label>
                            <input type="number" step="0.01" name="rice_rate[]" required class="w-full p-2 border border-gray-300 rounded focus:border-green-500 font-bold text-green-700 bg-green-50 auto-calc-rate" placeholder="৳">
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 border-l-4 border-l-orange-500">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-bold text-gray-700 uppercase text-sm tracking-wider">Output: Byproducts</h3>
                    <button type="button" onclick="addByproductRow()" class="text-sm font-bold text-orange-600 hover:text-orange-800 bg-orange-100 px-3 py-1 rounded-full">+ Add Another</button>
                </div>
                
                <div id="byproduct-container" class="space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 byproduct-row">
                        <div class="md:col-span-4">
                            <label class="block text-xs font-bold text-gray-500 mb-1">Item</label>
                            <select name="byproduct_item_id[]" class="w-full p-2 border border-gray-300 rounded focus:border-orange-500 bg-white auto-calc-item">
                                <option value="" selected>Select Bran/Husk...</option>
                                @foreach($byproducts as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-xs font-bold text-gray-500 mb-1">Qty Produced</label>
                            <input type="number" step="0.01" name="byproduct_quantity[]" class="w-full p-2 border border-gray-300 rounded focus:border-orange-500" placeholder="Qty">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 mb-1">Unit</label>
                            <select name="byproduct_unit_id[]" class="w-full p-2 border border-gray-300 rounded focus:border-orange-500 bg-white auto-calc-unit">
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ $unit->name == 'KG' ? 'selected' : '' }}>{{ $unit->short_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-xs font-bold text-gray-500 mb-1">Value / Unit</label>
                            <input type="number" step="0.01" name="byproduct_rate[]" class="w-full p-2 border border-gray-300 rounded focus:border-orange-500 font-bold text-orange-700 bg-orange-50 auto-calc-rate" placeholder="৳">
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <input type="text" name="batch_number" class="w-full p-3 border-2 border-gray-200 rounded-lg focus:border-green-500" placeholder="Batch / Reference Number (Optional)">
            </div>

            <button type="submit" class="w-full bg-green-600 text-white font-bold text-lg py-4 rounded-lg shadow-xl hover:bg-green-700 transition mt-4">
                Process Production Batch
            </button>
        </form>
    </div>

    <script>
        // Inject absolute base rates from PHP into JavaScript
        const itemBaseRates = {
            @foreach($rawMaterials->merge($finishedGoods)->merge($byproducts) as $item)
                "{{ $item->id }}": {{ $item->display_rate ?? 0 }},
            @endforeach
        };

        const unitConversions = {
            @foreach($units as $unit)
                "{{ $unit->id }}": {{ $unit->conversion_rate ?? 1 }},
            @endforeach
        };

        // Attach listener to calculate display price based on base price * unit conversion
        function attachRateCalculator(rowElement) {
            const itemSelect = rowElement.querySelector('.auto-calc-item');
            const unitSelect = rowElement.querySelector('.auto-calc-unit');
            const rateInput = rowElement.querySelector('.auto-calc-rate');

            // Safety check
            if (!itemSelect || !unitSelect || !rateInput) return;

            function calculate() {
                let itemId = itemSelect.value;
                let unitId = unitSelect.value;
                
                if (itemId && unitId && itemBaseRates[itemId] !== undefined && unitConversions[unitId] !== undefined) {
                    let baseRate = itemBaseRates[itemId];
                    let convRate = unitConversions[unitId];
                    // Formats rate to 2 decimal places. Users can still manually delete this and type their own!
                    rateInput.value = (baseRate * convRate).toFixed(2); 
                }
            }

            itemSelect.addEventListener('change', calculate);
            unitSelect.addEventListener('change', calculate);
        }

        // Attach to initial rows on page load
        document.addEventListener('DOMContentLoaded', () => {
            // Now safely finds all rows, including the raw material row!
            document.querySelectorAll('.raw-row, .rice-row, .byproduct-row').forEach(row => attachRateCalculator(row));
        });

        const finishedGoodsOptions = `@foreach($finishedGoods as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach`;
        const byproductOptions = `@foreach($byproducts as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach`;
        const unitOptions = `@foreach($units as $unit)<option value="{{ $unit->id }}" {{ $unit->name == 'KG' ? 'selected' : '' }}>{{ $unit->short_name }}</option>@endforeach`;

        function addRiceRow() {
            const row = document.createElement('div');
            row.className = "grid grid-cols-1 md:grid-cols-12 gap-3 pt-3 border-t border-gray-200 rice-row items-end";
            row.innerHTML = `
                <div class="md:col-span-4">
                    <select name="rice_item_id[]" required class="w-full p-2 border border-gray-300 rounded focus:border-green-500 bg-white auto-calc-item">
                        <option value="" disabled selected>Select Rice...</option>
                        ${finishedGoodsOptions}
                    </select>
                </div>
                <div class="md:col-span-3">
                    <input type="number" step="0.01" name="rice_quantity[]" required class="w-full p-2 border border-gray-300 rounded focus:border-green-500" placeholder="Qty">
                </div>
                <div class="md:col-span-2">
                    <select name="rice_unit_id[]" required class="w-full p-2 border border-gray-300 rounded focus:border-green-500 bg-white auto-calc-unit">
                        ${unitOptions}
                    </select>
                </div>
                <div class="md:col-span-3 flex gap-2">
                    <input type="number" step="0.01" name="rice_rate[]" required class="w-full p-2 border border-gray-300 rounded focus:border-green-500 font-bold text-green-700 bg-green-50 auto-calc-rate" placeholder="৳">
                    <button type="button" onclick="this.parentElement.parentElement.remove()" class="bg-red-100 text-red-600 px-3 rounded font-bold hover:bg-red-200" title="Remove">X</button>
                </div>
            `;
            document.getElementById('rice-container').appendChild(row);
            attachRateCalculator(row); // Attach smart calculator to the new row
        }

        function addByproductRow() {
            const row = document.createElement('div');
            row.className = "grid grid-cols-1 md:grid-cols-12 gap-3 pt-3 border-t border-gray-200 byproduct-row items-end";
            row.innerHTML = `
                <div class="md:col-span-4">
                    <select name="byproduct_item_id[]" class="w-full p-2 border border-gray-300 rounded focus:border-orange-500 bg-white auto-calc-item">
                        <option value="" selected>Select Bran/Husk...</option>
                        ${byproductOptions}
                    </select>
                </div>
                <div class="md:col-span-3">
                    <input type="number" step="0.01" name="byproduct_quantity[]" class="w-full p-2 border border-gray-300 rounded focus:border-orange-500" placeholder="Qty">
                </div>
                <div class="md:col-span-2">
                    <select name="byproduct_unit_id[]" class="w-full p-2 border border-gray-300 rounded focus:border-orange-500 bg-white auto-calc-unit">
                        ${unitOptions}
                    </select>
                </div>
                <div class="md:col-span-3 flex gap-2">
                    <input type="number" step="0.01" name="byproduct_rate[]" class="w-full p-2 border border-gray-300 rounded focus:border-orange-500 font-bold text-orange-700 bg-orange-50 auto-calc-rate" placeholder="৳">
                    <button type="button" onclick="this.parentElement.parentElement.remove()" class="bg-red-100 text-red-600 px-3 rounded font-bold hover:bg-red-200" title="Remove">X</button>
                </div>
            `;
            document.getElementById('byproduct-container').appendChild(row);
            attachRateCalculator(row); // Attach smart calculator to the new row
        }
    </script>
@endsection
@extends('layouts.app')

@section('title', 'Edit Item')

@section('content')
    <div class="max-w-3xl mx-auto">
        
        <div class="mb-6 flex items-center gap-4">
            <a href="/stock" class="text-gray-500 hover:text-gray-800 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Edit Stock Item</h1>
                <p class="text-gray-500">Update variant details and current balances</p>
            </div>
        </div>

        <form action="/update-item/{{ $item->id }}" method="POST" class="bg-white p-8 rounded-2xl shadow-xl border border-gray-200" id="edit-form">
            @csrf
            
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1 text-sm uppercase">Item Name</label>
                        <input type="text" name="name" value="{{ $item->name }}" required class="w-full p-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1 text-sm uppercase">Category</label>
                        <select name="category" required class="w-full p-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 outline-none bg-white">
                            <option value="Raw Material" {{ $item->category == 'Raw Material' ? 'selected' : '' }}>Raw Material</option>
                            <option value="Finished Goods" {{ $item->category == 'Finished Goods' ? 'selected' : '' }}>Finished Goods</option>
                            <option value="Byproduct" {{ $item->category == 'Byproduct' ? 'selected' : '' }}>Byproduct</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1 text-sm uppercase">Base Unit</label>
                        <select name="unit" id="unit-select" required class="w-full p-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 outline-none bg-white">
                            <option value="" disabled>Select Unit...</option>
                            @foreach($units as $u)
                                @php $uVal = $u->short_name ?? $u->name; @endphp
                                <option value="{{ $uVal }}" {{ $item->unit == $uVal ? 'selected' : '' }}>{{ $u->name }} ({{ $uVal }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="p-5 bg-orange-50 border border-orange-200 rounded-xl">
                    <h4 class="font-bold text-orange-800 mb-4 text-sm uppercase tracking-wider border-b border-orange-200 pb-2">Update Inventory Valuations</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1 text-xs uppercase">Opening Qty</label>
                            <input type="number" step="0.01" name="opening_stock" id="opening-input" required class="w-full p-3 border-2 border-white shadow-sm rounded-lg focus:border-orange-500 outline-none font-mono">
                            <p class="text-xs text-orange-600 mt-1">For P&L starting balance</p>
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 mb-1 text-xs uppercase">Current Qty</label>
                            <input type="number" step="0.01" name="current_stock" id="current-input" required class="w-full p-3 border-2 border-white shadow-sm rounded-lg focus:border-orange-500 outline-none font-mono">
                            <p class="text-xs text-orange-600 mt-1">Live warehouse stock</p>
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 mb-1 text-xs uppercase">Cost Rate (৳)</label>
                            <input type="number" step="0.01" name="purchase_rate" id="rate-input" required class="w-full p-3 border-2 border-white shadow-sm rounded-lg focus:border-orange-500 outline-none font-mono">
                            <p class="text-xs text-orange-600 mt-1">Used to calculate value</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <a href="/stock" class="px-6 py-3 rounded-lg font-bold text-gray-500 hover:bg-gray-100 transition">Cancel</a>
                    <button type="submit" class="bg-blue-600 text-white font-bold py-3 px-8 rounded-lg shadow-lg hover:bg-blue-700 transition">
                        Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const unitSelect = document.getElementById('unit-select');
            const openingInput = document.getElementById('opening-input');
            const currentInput = document.getElementById('current-input');
            const rateInput = document.getElementById('rate-input');
            const form = document.getElementById('edit-form');

            // Securely grab the absolute values from the database
            const dbOpening = {{ $item->opening_stock ?? 0 }};
            const dbCurrent = {{ $item->current_stock ?? 0 }};
            const dbRate = {{ $item->purchase_rate ?? 0 }};

            // Build a JS dictionary of all your database conversion rates
            const conversionRates = {
                @foreach($units as $u)
                    "{{ $u->short_name ?? $u->name }}": {{ $u->conversion_rate ?? 1 }},
                @endforeach
            };

            // Determine the true base rate (per KG) behind the scenes
            const originalUnit = "{{ $item->unit ?? 'KG' }}";
            const originalConversionRate = conversionRates[originalUnit] || 1;
            const trueBaseRatePerKg = dbRate / originalConversionRate;

            function calculateDisplay() {
                let rate = conversionRates[unitSelect.value] || 1;
                
                // QTY is divided by the conversion rate (e.g. 15000 KG / 1000 = 15 Ton)
                openingInput.value = (dbOpening / rate).toFixed(2);
                currentInput.value = (dbCurrent / rate).toFixed(2);
                
                // 🚨 FIXED: Rate is mathematically converted securely so it NEVER double-multiplies
                // (e.g. 61.33 Tk/KG * 1000 = 61330 Tk/Ton)
                rateInput.value = (trueBaseRatePerKg * rate).toFixed(2);
            }

            // Calculate immediately when the page loads
            calculateDisplay();

            // Calculate instantly whenever you change the dropdown
            unitSelect.addEventListener('change', calculateDisplay);

            // 🚨 CRITICAL FIX: Convert Qty back to Base KG before saving to Database!
            form.addEventListener('submit', function(e) {
                let rate = conversionRates[unitSelect.value] || 1;
                
                // If you type 15 (Ton), multiply by 1000 so the DB securely stores 15000 (KG)
                // This prevents your inventory from magically shrinking to 15 KG upon save!
                openingInput.value = (parseFloat(openingInput.value) * rate).toFixed(4);
                currentInput.value = (parseFloat(currentInput.value) * rate).toFixed(4);
                
                // (We leave the rate exactly as typed so it safely stays 61330 in the DB)
            });
        });
    </script>
@endsection
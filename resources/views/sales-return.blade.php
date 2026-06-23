@extends('layouts.app')
@section('title', 'Sales Return')
@section('content')

    <div class="bg-white w-full max-w-4xl rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-amber-700 p-4 text-center">
            <h2 class="text-2xl font-bold text-white">Sales Return</h2>
            <p class="text-amber-100 text-sm">Record returned goods from customer</p>
        </div>

        <form action="/run-sales-return" method="POST" class="p-6 space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Select Customer</label>
                <select name="party_id" required class="w-full p-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-amber-500 text-lg bg-white">
                    <option value="" disabled selected>Choose a customer...</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-bold text-gray-700 uppercase text-sm tracking-wider">Returned Items</h3>
                    <button type="button" onclick="addSalesReturnRow()" class="text-sm font-bold text-amber-600 hover:text-amber-800 bg-amber-100 px-3 py-1 rounded-full">+ Add Another</button>
                </div>
                <div id="sales-return-container" class="space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Item</label>
                            <select name="item_id[]" required class="w-full p-2 border border-gray-300 rounded focus:border-amber-500 bg-white">
                                <option value="" disabled selected>Select item...</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->category }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Qty</label>
                            <input type="number" name="quantity[]" required class="w-full p-2 border border-gray-300 rounded focus:border-amber-500" placeholder="e.g. 5">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Unit</label>
                            <select name="unit_id[]" required class="w-full p-2 border border-gray-300 rounded focus:border-amber-500 bg-white">
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->short_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Rate / Unit</label>
                            <input type="number" step="0.01" name="rate[]" required class="w-full p-2 border border-gray-300 rounded focus:border-amber-500" placeholder="e.g. 2500">
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Reference / Bill No.</label>
                    <input type="text" name="invoice_number" class="w-full p-3 border-2 border-gray-200 rounded-lg focus:border-amber-500" placeholder="Optional">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Narration</label>
                    <input type="text" name="narration" class="w-full p-3 border-2 border-gray-200 rounded-lg focus:border-amber-500" placeholder="e.g. Customer returned rice bags">
                </div>
            </div>

            <button type="submit" class="w-full bg-amber-700 text-white font-bold text-lg py-3 rounded-lg shadow-lg hover:bg-amber-800 transition mt-4">Confirm Sales Return</button>
        </form>
    </div>

    <script>
        const salesReturnItemOptions = `@foreach($items as $item)<option value="{{ $item->id }}">{{ $item->name }} ({{ $item->category }})</option>@endforeach`;
        const salesReturnUnitOptions = `@foreach($units as $unit)<option value="{{ $unit->id }}">{{ $unit->short_name }}</option>@endforeach`;

        function addSalesReturnRow() {
            const html = `
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 pt-3 border-t border-gray-200">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Item</label>
                        <select name="item_id[]" required class="w-full p-2 border border-gray-300 rounded focus:border-amber-500 bg-white">
                            <option value="" disabled selected>Select item...</option>
                            ${salesReturnItemOptions}
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Qty</label>
                        <input type="number" name="quantity[]" required class="w-full p-2 border border-gray-300 rounded focus:border-amber-500" placeholder="e.g. 5">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Unit</label>
                        <select name="unit_id[]" required class="w-full p-2 border border-gray-300 rounded focus:border-amber-500 bg-white">
                            ${salesReturnUnitOptions}
                        </select>
                    </div>
                    <div class="flex gap-2 items-end">
                        <div class="flex-1">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Rate / Unit</label>
                            <input type="number" step="0.01" name="rate[]" required class="w-full p-2 border border-gray-300 rounded focus:border-amber-500" placeholder="e.g. 2500">
                        </div>
                        <button type="button" onclick="this.parentElement.parentElement.remove()" class="bg-red-100 text-red-600 px-3 py-2 rounded font-bold hover:bg-red-200" title="Remove Row">X</button>
                    </div>
                </div>
            `;
            document.getElementById('sales-return-container').insertAdjacentHTML('beforeend', html);
        }
    </script>
@endsection

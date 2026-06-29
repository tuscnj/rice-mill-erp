@extends('layouts.app')
@section('title', 'Sell Items')
@section('content')

    <div class="bg-white w-full max-w-4xl mx-auto rounded-2xl shadow-sm border border-gray-100 overflow-visible mt-6">
        <div class="bg-gradient-to-r from-purple-700 to-purple-800 p-6 text-center sm:text-left sm:px-8 rounded-t-2xl">
            <h2 class="text-2xl font-extrabold text-white tracking-tight">Sell Inventory</h2>
            <p class="text-purple-100 text-sm mt-1">Create customer invoice for one or more items</p>
        </div>

        <form action="/run-sales" method="POST" class="p-6 sm:p-8 space-y-8" id="transaction-form">
            @csrf
            
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-1">Transaction Date</label>
                <input type="date" name="voucher_date" value="{{ date('Y-m-d') }}" class="w-full p-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Select Party (Customer / Supplier)</label>
                <select name="party_id" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 text-gray-800 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none cursor-pointer">
                    <option value="" disabled selected>Choose a party...</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }} (Customer)</option>
                    @endforeach
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }} (Supplier)</option>
                    @endforeach
                </select>
            </div>

            {{-- 🚨 ADDED: Master Group Filter --}}
            <div class="bg-purple-50 border-2 border-purple-100 rounded-2xl shadow-sm overflow-visible">
                <div class="p-4 sm:p-5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-purple-100">
                    <h3 class="font-bold text-gray-800 text-base flex items-center gap-2">
                        <span class="w-2 h-6 bg-purple-600 rounded-full"></span> ITEMS TO SELL
                    </h3>
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <label class="font-bold text-purple-800 text-xs tracking-wider uppercase whitespace-nowrap">Filter Group:</label>
                        <select id="group-filter" class="w-full sm:w-48 px-3 py-2 bg-white border border-purple-200 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none text-sm font-bold text-purple-900 cursor-pointer shadow-sm">
                            <option value="ALL">Show All Items</option>
                            @php $uniqueGroups = $items->pluck('item_group')->filter()->unique(); @endphp
                            @foreach($uniqueGroups as $group)
                                <option value="{{ $group }}">{{ $group }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="p-5 sm:p-6 overflow-visible">
                    <div id="items-container" class="space-y-4">
                        </div>
                    <button type="button" onclick="addRow()" class="mt-5 w-full sm:w-auto text-sm font-semibold text-purple-700 bg-white hover:bg-purple-100 px-5 py-2.5 rounded-lg transition-colors flex justify-center items-center gap-2 border border-purple-200 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Add Another Item
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Invoice Number</label>
                    <input type="text" name="invoice_number" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none" placeholder="Optional">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Narration</label>
                    <input type="text" name="narration" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none" placeholder="e.g. Sale to customer on credit">
                </div>
            </div>

            <hr class="border-gray-200">
            <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold text-lg py-4 rounded-xl shadow-md hover:shadow-lg transition-all active:scale-[0.99] flex justify-center items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Confirm Sale
            </button>
        </form>
    </div>

    {{-- 🚨 Hidden Template Row --}}
    <div id="row-template" class="hidden">
        <div class="transaction-row grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 p-4 bg-gray-50 rounded-xl border border-gray-200 relative mt-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Item (Search)</label>
                <div class="smart-select-wrapper relative">
                    <input type="hidden" name="item_id[]" class="real-value" disabled>
                    <input type="text" class="search-display w-full px-3 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none cursor-text font-bold text-gray-800 text-sm" placeholder="🔍 Type to search..." autocomplete="off" disabled>
                    
                    <div class="dropdown-menu absolute z-[100] w-full min-w-[250px] bg-white border border-gray-200 rounded-xl shadow-2xl mt-1 hidden max-h-60 overflow-y-auto top-full left-0">
                        @foreach($items as $item)
                            <div class="dropdown-item p-3 hover:bg-purple-50 cursor-pointer border-b border-gray-50 flex justify-between items-center" 
                                 data-value="{{ $item->id }}" 
                                 data-group="{{ $item->item_group }}" 
                                 data-search="{{ strtolower($item->name . ' ' . $item->category . ' ' . $item->item_group) }}">
                                <div>
                                    <div class="item-name font-bold text-sm text-gray-800">{{ $item->name }}</div>
                                    <div class="text-[10px] text-gray-500 uppercase tracking-wider mt-0.5">{{ $item->item_group ?? 'Uncategorized' }} • {{ $item->category }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs font-mono font-bold text-purple-600">Stock: {{ number_format($item->current_stock, 0) }}</div>
                                </div>
                            </div>
                        @endforeach
                        <div class="no-results p-3 text-sm text-gray-400 italic hidden">No items found matching the filter.</div>
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Qty</label>
                <input type="number" step="any" name="quantity[]" class="w-full px-3 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none" placeholder="e.g. 10" required disabled>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Unit</label>
                <select name="unit_id[]" class="w-full px-3 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none cursor-pointer" required disabled>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->short_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2 items-end">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Rate / Unit</label>
                    <input type="number" step="any" name="rate[]" class="w-full px-3 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none" placeholder="e.g. 3000" required disabled>
                </div>
                <button type="button" onclick="this.closest('.transaction-row').remove()" class="bg-red-50 text-red-500 hover:bg-red-500 hover:text-white px-3 py-2.5 rounded-lg font-bold border border-red-100 transition-colors" title="Remove Row">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentGroupFilter = 'ALL';

        document.getElementById('group-filter').addEventListener('change', function() {
            currentGroupFilter = this.value;
            document.querySelectorAll('.transaction-row:not(#row-template .transaction-row)').forEach(row => {
                const hiddenInput = row.querySelector('.real-value');
                const displayInput = row.querySelector('.search-display');
                if(hiddenInput.value) {
                    const selectedItem = row.querySelector(`.dropdown-item[data-value="${hiddenInput.value}"]`);
                    if(selectedItem && currentGroupFilter !== 'ALL' && selectedItem.getAttribute('data-group') !== currentGroupFilter) {
                        hiddenInput.value = '';
                        displayInput.value = '';
                        displayInput.placeholder = '⚠ Re-select item...';
                    }
                }
            });
        });

        function initSmartSelect(wrapper) {
            const hiddenInput = wrapper.querySelector('.real-value');
            const displayInput = wrapper.querySelector('.search-display');
            const dropdown = wrapper.querySelector('.dropdown-menu');
            const items = dropdown.querySelectorAll('.dropdown-item');
            const noResults = dropdown.querySelector('.no-results');

            displayInput.addEventListener('focus', () => {
                closeAllDropdowns();
                dropdown.classList.remove('hidden');
                filterList('');
            });

            document.addEventListener('click', (e) => {
                if (!wrapper.contains(e.target)) {
                    dropdown.classList.add('hidden');
                    if(!hiddenInput.value) displayInput.value = ''; 
                }
            });

            displayInput.addEventListener('input', (e) => {
                dropdown.classList.remove('hidden');
                hiddenInput.value = ''; 
                filterList(e.target.value.toLowerCase().trim());
            });

            items.forEach(item => {
                item.addEventListener('click', () => {
                    hiddenInput.value = item.getAttribute('data-value');
                    displayInput.value = item.querySelector('.item-name').innerText.trim();
                    displayInput.placeholder = "🔍 Search item...";
                    dropdown.classList.add('hidden');
                });
            });

            function filterList(searchTerm) {
                let hasVisible = false;
                items.forEach(item => {
                    const searchStr = item.getAttribute('data-search');
                    const groupStr = item.getAttribute('data-group');
                    
                    const matchesSearch = searchStr.includes(searchTerm);
                    const matchesGroup = (currentGroupFilter === 'ALL' || groupStr === currentGroupFilter);

                    if (matchesSearch && matchesGroup) {
                        item.style.display = 'flex';
                        hasVisible = true;
                    } else {
                        item.style.display = 'none';
                    }
                });
                hasVisible ? noResults.classList.add('hidden') : noResults.classList.remove('hidden');
            }
        }

        function closeAllDropdowns() {
            document.querySelectorAll('.dropdown-menu').forEach(menu => menu.classList.add('hidden'));
        }

        function addRow() {
            const template = document.getElementById('row-template').innerHTML;
            const container = document.getElementById('items-container');
            container.insertAdjacentHTML('beforeend', template);
            
            const newRow = container.lastElementChild;
            newRow.querySelectorAll('input, select').forEach(el => el.removeAttribute('disabled'));
            initSmartSelect(newRow.querySelector('.smart-select-wrapper'));
        }

        document.getElementById('transaction-form').addEventListener('submit', function(e) {
            let isValid = true;
            document.querySelectorAll('#items-container .real-value').forEach(input => {
                if(!input.value) isValid = false;
            });
            if(!isValid) {
                e.preventDefault();
                alert("Please select a valid item from the dropdown list for all rows.");
            }
        });

        document.addEventListener('DOMContentLoaded', addRow);
    </script>
@endsection
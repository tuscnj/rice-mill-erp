@extends('layouts.app')
@section('title', 'Inventory Items')
@section('content')

    <div class="max-w-5xl mx-auto space-y-6">
        
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Inventory Items</h1>
                <p class="text-gray-500">Manage Paddy Variants, Rice Types, and Byproducts</p>
            </div>
            <a href="/" class="bg-gray-800 hover:bg-gray-900 text-white font-bold py-2 px-4 rounded-lg shadow transition">
                ← Dashboard
            </a>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-xl border-t-4 border-indigo-500">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Create New Item Variant</h2>
            <form action="/run-item" method="POST" class="flex flex-col md:flex-row gap-4 items-end">
                @csrf
                <div class="w-full md:w-2/5">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Item Name</label>
                    <input type="text" name="name" required class="w-full p-3 border-2 border-gray-200 rounded-lg focus:border-indigo-500" placeholder="e.g. Miniket Paddy, Bran...">
                </div>
                
                <div class="w-full md:w-1/4">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Category</label>
                    <select name="category" required class="w-full p-3 border-2 border-gray-200 rounded-lg focus:border-indigo-500 bg-white">
                        <option value="Raw Material">Raw Material (Paddy)</option>
                        <option value="Finished Goods">Finished Goods (Rice)</option>
                        <option value="Byproduct">Byproduct (Bran, Husk)</option>
                    </select>
                </div>

                <div class="w-full md:w-1/5">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Opening Stock (KG)</label>
                    <input type="number" step="0.01" name="opening_stock" class="w-full p-3 border-2 border-gray-200 rounded-lg focus:border-indigo-500" placeholder="0.00">
                </div>

                <div class="w-full md:w-1/5">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Opening Rate / Unit</label>
                    <input type="number" step="0.01" name="opening_rate" class="w-full p-3 border-2 border-gray-200 rounded-lg focus:border-indigo-500" placeholder="0.00">
                </div>

                <div class="w-full md:w-auto">
                    <button type="submit" class="w-full bg-indigo-500 text-white font-bold py-3 px-6 rounded-lg shadow hover:bg-indigo-600 transition">
                        + Add Item
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-800 text-white">
                        <th class="p-4 font-bold">Item Name</th>
                        <th class="p-4 font-bold">Category</th>
                        <th class="p-4 font-bold text-right">Current Stock (KG)</th>
                        <th class="p-4 font-bold text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="p-4 font-bold text-gray-800">{{ $item->name }}</td>
                        <td class="p-4 text-gray-500 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs font-bold 
                                {{ $item->category == 'Raw Material' ? 'bg-blue-100 text-blue-700' : 
                                  ($item->category == 'Finished Goods' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700') }}">
                                {{ $item->category }}
                            </span>
                        </td>
                        <td class="p-4 text-right font-bold text-gray-800">
                            {{ number_format($item->current_stock, 2) }} KG
                        </td>
                        <td class="p-4 text-center space-x-3">
                            <a href="/edit-item/{{ $item->id }}" class="text-indigo-600 font-bold text-sm">Edit</a>
                            <a href="/delete-item/{{ $item->id }}" onclick="return confirm('Delete this item?')" class="text-red-600 font-bold text-sm">Delete</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
@endsection
@extends('layouts.app')
@section('title', 'Units')
@section('content')

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Unit Settings</h1>
                <p class="text-gray-500">Define conversion rates for Inventory (Base: KG)</p>
            </div>
            <a href="/" class="bg-gray-800 text-white font-bold py-2 px-4 rounded-lg shadow">← Dashboard</a>
        </div>

        <!-- Add Unit Form -->
        <div class="bg-white p-6 rounded-2xl shadow-xl border-t-4 border-cyan-500">
            <form action="/run-unit" method="POST" class="flex flex-col md:flex-row gap-4 items-end">
                @csrf
                <div class="w-full md:w-1/3">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Unit Name</label>
                    <input type="text" name="name" required class="w-full p-3 border-2 border-gray-200 rounded-lg focus:border-cyan-500" placeholder="e.g. 50KG Bag">
                </div>
                <div class="w-full md:w-1/4">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Short Name</label>
                    <input type="text" name="short_name" required class="w-full p-3 border-2 border-gray-200 rounded-lg focus:border-cyan-500" placeholder="e.g. bag">
                </div>
                <div class="w-full md:w-1/4">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Equals (KG)</label>
                    <input type="number" step="0.01" name="conversion_rate" required class="w-full p-3 border-2 border-gray-200 rounded-lg focus:border-cyan-500" placeholder="e.g. 50">
                </div>
                <div class="w-full md:w-auto">
                    <button type="submit" class="w-full bg-cyan-500 text-white font-bold py-3 px-6 rounded-lg shadow hover:bg-cyan-600 transition">
                        + Add Unit
                    </button>
                </div>
            </form>
        </div>

        <!-- Units Table -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-800 text-white">
                        <th class="p-4 font-bold">Unit Name</th>
                        <th class="p-4 font-bold text-center">Short Name</th>
                        <th class="p-4 font-bold text-right">Conversion (to KG)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-gray-100">
                        <td class="p-4 font-bold text-gray-800">Kilogram (Default)</td>
                        <td class="p-4 text-center text-gray-600">kg</td>
                        <td class="p-4 text-right font-bold text-gray-800">1.00</td>
                    </tr>
                    @foreach($units as $unit)
                    <td class="p-4 text-center space-x-3">
                            <a href="/edit-unit/{{ $unit->id }}" class="text-cyan-600 font-bold text-sm">Edit</a>
                            <a href="/delete-unit/{{ $unit->id }}" onclick="return confirm('Delete this unit?')" class="text-red-600 font-bold text-sm">Delete</a>
                        </td>
                    <tr class="border-b border-gray-100">
                        <td class="p-4 font-bold text-gray-800">{{ $unit->name }}</td>
                        <td class="p-4 text-center text-gray-500">{{ $unit->short_name }}</td>
                        <td class="p-4 text-right font-bold text-cyan-600">{{ $unit->conversion_rate }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
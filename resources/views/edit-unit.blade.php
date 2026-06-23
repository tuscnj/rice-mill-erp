@extends('layouts.app')
@section('title', 'Edit Unit')
@section('content')
    <form action="/update-unit/{{ $unit->id }}" method="POST" class="bg-white p-6 rounded-xl shadow-xl w-full max-w-md">
        @csrf
        <h2 class="text-2xl font-bold mb-4 text-cyan-600">Edit Unit</h2>
        <div class="mb-4">
            <label class="block font-bold mb-1">Unit Name</label>
            <input type="text" name="name" value="{{ $unit->name }}" class="w-full p-2 border rounded">
        </div>
        <div class="mb-4">
            <label class="block font-bold mb-1">Short Name</label>
            <input type="text" name="short_name" value="{{ $unit->short_name }}" class="w-full p-2 border rounded">
        </div>
        <div class="mb-4">
            <label class="block font-bold mb-1">Conversion Rate (to KG)</label>
            <input type="number" step="0.01" name="conversion_rate" value="{{ $unit->conversion_rate }}" class="w-full p-2 border rounded">
        </div>
        <button type="submit" class="w-full bg-cyan-500 text-white font-bold py-2 rounded hover:bg-cyan-600">Save Changes</button>
        <a href="/units" class="block text-center mt-3 text-gray-500">Cancel</a>
    </form>
@endsection
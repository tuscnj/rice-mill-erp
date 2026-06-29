@extends('layouts.app')
@section('title', 'Manage Brands')
@section('content')
    <div class="max-w-4xl mx-auto space-y-6 mt-6">
        <div class="bg-gradient-to-r from-indigo-700 to-indigo-800 p-6 rounded-2xl text-white shadow-md">
            <h2 class="text-2xl font-extrabold tracking-tight">Brand & Group Settings</h2>
            <p class="text-indigo-200 text-sm mt-1">Create predefined item groups to assign to your inventory</p>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 text-emerald-700 p-4 rounded-xl border border-emerald-200 font-bold shadow-sm flex items-center gap-2">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 text-red-700 p-4 rounded-xl border border-red-200 font-bold shadow-sm flex items-center gap-2">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
            <div class="md:col-span-1">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                    <h3 class="font-bold text-gray-800 uppercase tracking-wider mb-4 border-b pb-2">Add New Brand</h3>
                    <form action="/brands" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Brand / Group Name</label>
                            <input type="text" name="name" required class="w-full p-3 bg-gray-50 border border-gray-200 rounded-lg focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all" placeholder="e.g. Atik Special">
                        </div>
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg shadow-md transition-all active:scale-[0.98]">
                            Save Brand
                        </button>
                    </form>
                </div>
            </div>

            <div class="md:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead class="bg-slate-50 border-b border-gray-200 text-gray-500">
                            <tr>
                                <th class="p-4 font-bold uppercase tracking-wider">Brand Name</th>
                                <th class="p-4 font-bold text-right uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($brands as $brand)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-4 font-bold text-gray-800 text-base">
                                    <span class="inline-block w-2 h-2 rounded-full bg-indigo-500 mr-2"></span>
                                    {{ $brand->name }}
                                </td>
                                <td class="p-4 text-right">
                                    <form action="/delete-brand/{{ $brand->id }}" method="GET" onsubmit="return confirm('Delete this brand? Existing inventory will keep the text tag, but it will be removed from future dropdowns.');">
                                        <button type="submit" class="text-red-500 hover:text-red-700 font-bold text-xs bg-red-50 hover:bg-red-100 border border-red-100 hover:border-red-200 px-3 py-1.5 rounded-lg transition shadow-sm">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="p-8 text-center text-gray-400 italic">No brands created yet. Add your first brand to the left!</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
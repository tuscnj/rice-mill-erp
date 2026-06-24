@extends('layouts.app')
@section('title', 'Units')
@section('content')

    <div class="max-w-6xl mx-auto space-y-6 pb-12 mt-4 sm:mt-6">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 tracking-tight">Unit Settings</h1>
                <p class="text-gray-500 text-sm sm:text-base mt-1">Define conversion rates for Inventory (Base: KG)</p>
            </div>
            <div class="w-full md:w-auto">
                <a href="/" class="w-full md:w-auto flex justify-center items-center gap-2 bg-slate-800 hover:bg-slate-900 text-white font-bold py-3 px-6 rounded-xl shadow-md transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Dashboard
                </a>
            </div>
        </div>

        <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-gray-100 border-t-4 border-t-cyan-500">
            <div class="flex items-center gap-2 mb-6">
                <svg class="w-6 h-6 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                <h2 class="text-xl font-bold text-gray-800">Add New Unit</h2>
            </div>

            <form action="/run-unit" method="POST" class="space-y-5">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-end">
                    
                    <div class="md:col-span-5">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Unit Name</label>
                        <input type="text" name="name" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 text-gray-800 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 focus:bg-white transition-all duration-200" placeholder="e.g. 50KG Bag">
                    </div>
                    
                    <div class="md:col-span-4">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Short Name</label>
                        <input type="text" name="short_name" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 text-gray-800 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 focus:bg-white transition-all duration-200" placeholder="e.g. bag">
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Equals (KG)</label>
                        <input type="number" step="0.01" name="conversion_rate" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 text-gray-800 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 focus:bg-white transition-all duration-200" placeholder="e.g. 50">
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-100 mt-6">
                    <button type="submit" class="w-full md:w-auto bg-cyan-500 hover:bg-cyan-600 text-white font-bold py-3 px-8 rounded-xl shadow-md transition-all active:scale-95 flex justify-center items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Add Unit
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-transparent md:bg-white rounded-none md:rounded-2xl shadow-none md:shadow-sm border-none md:border border-gray-100 overflow-hidden">
            
            <table class="w-full text-left border-collapse hidden md:table">
                <thead>
                    <tr class="bg-slate-800 text-white text-sm uppercase tracking-wider">
                        <th class="p-4 font-bold rounded-tl-xl md:rounded-none">Unit Name</th>
                        <th class="p-4 font-bold text-center">Short Name</th>
                        <th class="p-4 font-bold text-right">Conversion (to KG)</th>
                        <th class="p-4 font-bold text-center rounded-tr-xl md:rounded-none">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <tr class="bg-gray-50">
                        <td class="p-4 font-bold text-gray-800 text-base">Kilogram (System Default)</td>
                        <td class="p-4 text-center">
                            <span class="px-3 py-1 bg-white border border-gray-200 text-gray-600 rounded-lg text-xs font-bold inline-block">kg</span>
                        </td>
                        <td class="p-4 text-right font-mono font-bold text-slate-800 text-base">1.00</td>
                        <td class="p-4 text-center text-gray-400 italic text-xs">System Unit</td>
                    </tr>
                    
                    @foreach($units as $unit)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-4 font-bold text-gray-800 text-base">{{ $unit->name }}</td>
                            <td class="p-4 text-center">
                                <span class="px-3 py-1 bg-white border border-gray-200 text-gray-600 rounded-lg text-xs font-bold inline-block">{{ $unit->short_name }}</span>
                            </td>
                            <td class="p-4 text-right font-mono font-bold text-cyan-600 text-base">{{ $unit->conversion_rate }}</td>
                            <td class="p-4 text-center">
                                <div class="flex justify-center items-center gap-2">
                                    <a href="/edit-unit/{{ $unit->id }}" class="bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white border border-blue-100 hover:border-blue-600 px-3 py-1.5 rounded-lg text-xs font-bold transition">Edit</a>
                                    <a href="/delete-unit/{{ $unit->id }}" onclick="return confirm('Delete this unit?')" class="bg-red-50 text-red-600 hover:bg-red-600 hover:text-white border border-red-100 hover:border-red-600 px-3 py-1.5 rounded-lg text-xs font-bold transition">Delete</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="grid grid-cols-1 gap-4 md:hidden">
                <div class="bg-gray-50 p-5 rounded-2xl shadow-sm border border-gray-200 relative">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Kilogram</h3>
                            <span class="text-[10px] uppercase font-bold text-gray-500">System Default</span>
                        </div>
                        <span class="px-2.5 py-1 bg-white border border-gray-200 text-gray-600 rounded-md text-xs font-bold">kg</span>
                    </div>
                    <div class="mt-4 flex justify-between items-center border-t border-gray-200 pt-3">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Conversion (KG)</span>
                        <div class="font-mono font-extrabold text-lg text-slate-800">1.00</div>
                    </div>
                </div>

                @foreach($units as $unit)
                    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 relative">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-lg font-bold text-gray-800">{{ $unit->name }}</h3>
                            <span class="px-2.5 py-1 bg-gray-50 border border-gray-200 text-gray-600 rounded-md text-[10px] font-bold uppercase tracking-wider inline-block">
                                {{ $unit->short_name }}
                            </span>
                        </div>

                        <div class="bg-cyan-50/50 rounded-xl p-4 mb-5 border border-cyan-100 flex justify-between items-center">
                            <span class="text-xs font-bold text-cyan-800 uppercase tracking-wider">Equals KG</span>
                            <div class="font-mono font-extrabold text-lg text-cyan-600">
                                {{ $unit->conversion_rate }}
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <a href="/edit-unit/{{ $unit->id }}" class="text-center text-blue-700 bg-blue-50 py-2.5 rounded-xl font-bold border border-blue-100 text-sm active:bg-blue-100">Edit</a>
                            <a href="/delete-unit/{{ $unit->id }}" onclick="return confirm('Delete this unit?')" class="text-center text-red-700 bg-red-50 py-2.5 rounded-xl font-bold border border-red-100 text-sm active:bg-red-100">Delete</a>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
@endsection
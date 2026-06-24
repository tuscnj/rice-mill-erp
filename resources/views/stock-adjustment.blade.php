@extends('layouts.app')
@section('title', 'Stock Adjustment')
@section('content')

    <div class="bg-white w-full max-w-md mx-auto rounded-2xl shadow-sm border border-gray-100 overflow-hidden mt-6">
        <div class="bg-gradient-to-r from-slate-700 to-slate-800 p-6 text-center sm:text-left sm:px-8">
            <h2 class="text-2xl font-extrabold text-white tracking-tight">Stock Adjustment</h2>
            <p class="text-slate-200 text-sm mt-1">Manually correct inventory quantities</p>
        </div>

        <form action="/run-stock-adjustment" method="POST" class="p-6 sm:p-8 space-y-6">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Select Item Variant</label>
                <select name="item_id" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 text-gray-800 rounded-xl focus:ring-2 focus:ring-slate-500 focus:border-slate-500 focus:bg-white transition-all duration-200 cursor-pointer appearance-none">
                    <option value="" disabled selected>Choose an item...</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}">{{ $item->name }} (Current: {{ $item->current_stock }} KG)</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Adjustment Type</label>
                <select name="adjustment_type" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 text-gray-800 rounded-xl focus:ring-2 focus:ring-slate-500 focus:border-slate-500 focus:bg-white transition-all duration-200 cursor-pointer appearance-none">
                    <option value="Out">Reduce Stock (-)</option>
                    <option value="In">Add Stock (+)</option>
                </select>
            </div>

            <div class="bg-slate-50 p-5 rounded-2xl border-2 border-slate-100">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-3 text-center">Quantity to Adjust (Base Unit / KG)</label>
                <input type="number" step="0.01" name="quantity" required class="w-full text-3xl p-4 border-2 border-slate-200 rounded-xl focus:ring-4 focus:ring-slate-500/20 focus:border-slate-500 text-center font-bold text-slate-800 bg-white placeholder-slate-300 transition-all duration-200" placeholder="0.00">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Reason / Notes</label>
                <input type="text" name="notes" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-slate-500 focus:border-slate-500 focus:bg-white transition-all duration-200" placeholder="e.g. Damaged goods, Counting error">
            </div>

            <hr class="border-gray-200 mt-2 mb-2">

            <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold text-lg py-4 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 active:scale-[0.99] flex justify-center items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                Post Adjustment
            </button>
        </form>
    </div>
@endsection
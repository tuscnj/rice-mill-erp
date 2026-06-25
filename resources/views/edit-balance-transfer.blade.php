@extends('layouts.app')
@section('title', 'Edit Transfer')
@section('content')

@php
    // The "From" account is the one that was Credited (gave money)
    // The "To" account is the one that was Debited (received money)
    $oldDebit = $voucher->entries->where('entry_type', 'Debit')->first();
    $oldCredit = $voucher->entries->where('entry_type', 'Credit')->first();
    
    $currentToId = $oldDebit ? $oldDebit->account_id : null;
    $currentFromId = $oldCredit ? $oldCredit->account_id : null;
    $currentAmount = $oldDebit ? $oldDebit->amount : 0;
@endphp

    <div class="bg-white w-full max-w-2xl mx-auto rounded-2xl shadow-sm border border-gray-100 overflow-hidden mt-6">
        <div class="bg-gradient-to-r from-sky-600 to-sky-700 p-6 flex justify-between items-center text-center sm:text-left sm:px-8">
            <div>
                <h2 class="text-2xl font-extrabold text-white tracking-tight">Edit Transfer #VCH-{{ $voucher->id }}</h2>
                <p class="text-sky-100 text-sm mt-1">Modify journal style transfer between accounts</p>
            </div>
            <a href="/transactions" class="bg-sky-800 text-white px-5 py-2.5 rounded-md font-bold hover:bg-sky-900 transition text-sm shadow-sm">Cancel</a>
        </div>

        <form action="/update-balance-transfer/{{ $voucher->id }}" method="POST" class="p-6 sm:p-8 space-y-6">
            @csrf
            
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-1">Transaction Date</label>
                <input type="date" name="voucher_date" value="{{ \Carbon\Carbon::parse($voucher->voucher_date)->format('Y-m-d') }}" 
                       class="w-full p-3 border-2 border-gray-200 rounded-lg focus:border-sky-500">
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 relative">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-red-400"></span> Out (From Account)
                    </label>
                    <select name="from_account_id" required class="w-full px-4 py-3 bg-white border border-gray-200 text-gray-800 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all duration-200 cursor-pointer appearance-none">
                        <option value="" disabled>Select account...</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" {{ $currentFromId == $account->id ? 'selected' : '' }}>{{ $account->name }} ({{ $account->group_type }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 relative">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-green-400"></span> In (To Account)
                    </label>
                    <select name="to_account_id" required class="w-full px-4 py-3 bg-white border border-gray-200 text-gray-800 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all duration-200 cursor-pointer appearance-none">
                        <option value="" disabled>Select account...</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" {{ $currentToId == $account->id ? 'selected' : '' }}>{{ $account->name }} ({{ $account->group_type }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="bg-sky-50/50 p-5 rounded-2xl border-2 border-sky-100">
                <label class="block text-xs font-bold text-sky-800 uppercase tracking-wider mb-3 text-center">Transfer Amount (৳)</label>
                <input type="number" step="0.01" name="amount" value="{{ $currentAmount }}" required class="w-full text-3xl p-4 border-2 border-sky-200 rounded-xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-500 text-center font-bold text-sky-700 bg-white placeholder-sky-200 transition-all duration-200" placeholder="0.00">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Reference Number</label>
                    <input type="text" name="reference_number" value="{{ $voucher->reference_number }}" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white transition-all duration-200" placeholder="Optional">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Narration</label>
                    <input type="text" name="narration" value="{{ $voucher->notes }}" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white transition-all duration-200" placeholder="e.g. Transfer between accounts">
                </div>
            </div>

            <hr class="border-gray-200 mt-2 mb-2">

            <button type="submit" class="w-full bg-sky-600 hover:bg-sky-700 text-white font-bold text-lg py-4 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 active:scale-[0.99] flex justify-center items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                Update Transfer
            </button>
        </form>
    </div>

@endsection
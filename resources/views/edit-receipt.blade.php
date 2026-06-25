@extends('layouts.app')
@section('title', 'Edit Receipt')
@section('content')

@php
    // Bulletproof fetch to ensure the dropdowns always have data
    $banks = \App\Models\Account::where('group_type', 'Cash')->get();
    // Fetch both Customers and Suppliers just like your main receipt page
    $parties = \App\Models\Account::whereIn('group_type', ['Sundry Creditors', 'Sundry Debtors'])->orderBy('name')->get();
    
    // Extract existing values using the correct 'entries' relationship name
    // For a receipt: Debit is Cash (money coming in), Credit is Party (who gave it)
    $oldDebit = $voucher->entries->where('entry_type', 'Debit')->first();
    $oldCredit = $voucher->entries->where('entry_type', 'Credit')->first();
    
    $currentCashId = $oldDebit ? $oldDebit->account_id : null;
    $currentPartyId = $oldCredit ? $oldCredit->account_id : null;
    $currentAmount = $oldDebit ? $oldDebit->amount : 0;
@endphp

<div class="max-w-4xl mx-auto mt-8 bg-white rounded-xl shadow-md overflow-hidden">
    <div class="bg-teal-600 p-5 flex justify-between items-center text-center md:text-left">
        <div>
            <h2 class="text-2xl font-bold text-white tracking-tight">Edit Receipt #VCH-{{ $voucher->id }}</h2>
            <p class="text-teal-100 text-sm mt-1">Modify previously recorded income</p>
        </div>
        <a href="/transactions" class="bg-teal-800 text-white px-5 py-2.5 rounded-md font-bold hover:bg-teal-900 transition text-sm shadow-sm">Cancel</a>
    </div>

    <form action="/update-receipt/{{ $voucher->id }}" method="POST" class="p-6 sm:p-8">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="col-span-1 md:col-span-2">
                <label class="block text-sm font-bold text-gray-700 mb-1">Transaction Date</label>
                <input type="date" name="voucher_date" value="{{ \Carbon\Carbon::parse($voucher->voucher_date)->format('Y-m-d') }}" 
                       class="w-full p-2.5 border border-gray-300 rounded-md focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Receive Into (Bank/Cash)</label>
                <select name="cash_id" required class="w-full p-2.5 bg-white border border-gray-300 rounded-md focus:border-teal-500 focus:ring-1 focus:ring-teal-500 cursor-pointer">
                    @foreach($banks as $bank)
                        <option value="{{ $bank->id }}" {{ $currentCashId == $bank->id ? 'selected' : '' }}>{{ $bank->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Receive From (Party)</label>
                <select name="party_id" required class="w-full p-2.5 bg-white border border-gray-300 rounded-md focus:border-teal-500 focus:ring-1 focus:ring-teal-500 cursor-pointer">
                    <option value="" disabled>Select Party...</option>
                    @foreach($parties as $party)
                        <option value="{{ $party->id }}" {{ $currentPartyId == $party->id ? 'selected' : '' }}>{{ $party->name }} (Balance: ৳{{ number_format($party->balance, 2) }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Amount Received (৳)</label>
                <input type="number" step="0.01" name="amount" value="{{ $currentAmount }}" required 
                       class="w-full p-2.5 border border-gray-300 rounded-md focus:border-teal-500 focus:ring-1 focus:ring-teal-500" placeholder="0.00">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Reference / Cheque Number</label>
                <input type="text" name="receipt_number" value="{{ $voucher->reference_number }}" 
                       class="w-full p-2.5 border border-gray-300 rounded-md focus:border-teal-500 focus:ring-1 focus:ring-teal-500" placeholder="Optional">
            </div>
        </div>

        <div class="mt-8 flex justify-end">
            <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-2.5 px-6 rounded-md shadow-sm transition-colors active:scale-[0.99] flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Update Receipt
            </button>
        </div>
    </form>
</div>
@endsection
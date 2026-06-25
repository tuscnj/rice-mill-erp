@extends('layouts.app')
@section('title', 'Edit Other Income')
@section('content')

@php
    // Bulletproof fetch for dropdowns
    $banks = \App\Models\Account::where('group_type', 'Cash')->get();
    $incomes = \App\Models\Account::where('group_type', 'Indirect Incomes')->get();
    
    // Extract existing values
    $oldDebit = $voucher->entries->where('entry_type', 'Debit')->first();
    $oldCredit = $voucher->entries->where('entry_type', 'Credit')->first();
    
    $currentCashId = $oldDebit ? $oldDebit->account_id : null;
    $currentIncomeId = $oldCredit ? $oldCredit->account_id : null;
    $currentAmount = $oldDebit ? $oldDebit->amount : 0;
@endphp

<div class="max-w-4xl mx-auto mt-8 bg-white rounded-xl shadow-md overflow-hidden border-t-4 border-green-500">
    <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-5 flex justify-between items-center text-center md:text-left">
        <div>
            <h2 class="text-2xl font-bold text-white tracking-tight">Edit Income #VCH-{{ $voucher->id }}</h2>
            <p class="text-green-100 text-sm mt-1">Modify previously recorded extra income</p>
        </div>
        <a href="/transactions" class="bg-green-700 text-white px-5 py-2.5 rounded-md font-bold hover:bg-green-800 transition text-sm shadow-sm">Cancel</a>
    </div>

    <form action="/update-other-income/{{ $voucher->id }}" method="POST" class="p-6 sm:p-8">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="col-span-1 md:col-span-2">
                <label class="block text-sm font-bold text-gray-700 mb-1">Transaction Date</label>
                <input type="date" name="voucher_date" value="{{ \Carbon\Carbon::parse($voucher->voucher_date)->format('Y-m-d') }}" 
                       class="w-full p-2.5 border border-gray-300 rounded-md focus:border-green-500 focus:ring-1 focus:ring-green-500">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Income Category</label>
                <select name="income_id" required class="w-full p-2.5 bg-white border border-gray-300 rounded-md focus:border-green-500 focus:ring-1 focus:ring-green-500 cursor-pointer">
                    <option value="" disabled>Select Income Type...</option>
                    @foreach($incomes as $income)
                        <option value="{{ $income->id }}" {{ $currentIncomeId == $income->id ? 'selected' : '' }}>{{ $income->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Cash / Bank</label>
                <select name="cash_id" required class="w-full p-2.5 bg-white border border-gray-300 rounded-md focus:border-green-500 focus:ring-1 focus:ring-green-500 cursor-pointer">
                    @foreach($banks as $bank)
                        <option value="{{ $bank->id }}" {{ $currentCashId == $bank->id ? 'selected' : '' }}>{{ $bank->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Amount (৳)</label>
                <input type="number" step="0.01" name="amount" value="{{ $currentAmount }}" required 
                       class="w-full p-2.5 border border-gray-300 rounded-md focus:border-green-500 focus:ring-1 focus:ring-green-500" placeholder="0.00">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Reference / Receipt No.</label>
                <input type="text" name="reference_number" value="{{ $voucher->reference_number }}" 
                       class="w-full p-2.5 border border-gray-300 rounded-md focus:border-green-500 focus:ring-1 focus:ring-green-500" placeholder="Optional">
            </div>

            <div class="col-span-1 md:col-span-2">
                <label class="block text-sm font-bold text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full p-2.5 border border-gray-300 rounded-md focus:border-green-500 focus:ring-1 focus:ring-green-500" placeholder="Brief note about the income">{{ $voucher->notes }}</textarea>
            </div>
        </div>

        <div class="mt-8 flex justify-end">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-6 rounded-md shadow-sm transition-colors active:scale-[0.99] flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Update Other Income
            </button>
        </div>
    </form>
</div>
@endsection
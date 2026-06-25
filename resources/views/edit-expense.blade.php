@extends('layouts.app')
@section('title', 'Edit Expense')
@section('content')

@php
    // Bulletproof fetch to ensure the dropdowns always have data
    $banks = \App\Models\Account::where('group_type', 'Cash')->get();
    $expenses = \App\Models\Account::where('group_type', 'Indirect Expenses')->get();
    
    // Extract existing values using the correct 'entries' relationship name
    $oldDebit = $voucher->entries->where('entry_type', 'Debit')->first();
    $oldCredit = $voucher->entries->where('entry_type', 'Credit')->first();
    
    $currentExpenseId = $oldDebit ? $oldDebit->account_id : null;
    $currentCashId = $oldCredit ? $oldCredit->account_id : null;
    $currentAmount = $oldDebit ? $oldDebit->amount : 0;
@endphp

<div class="max-w-4xl mx-auto mt-8 bg-white rounded-xl shadow-md overflow-hidden">
    <div class="bg-rose-600 p-5 flex justify-between items-center text-center md:text-left">
        <div>
            <h2 class="text-2xl font-bold text-white tracking-tight">Edit Expense #VCH-{{ $voucher->id }}</h2>
            <p class="text-rose-100 text-sm mt-1">Modify previously recorded mill costs</p>
        </div>
        <a href="/transactions" class="bg-rose-800 text-white px-5 py-2.5 rounded-md font-bold hover:bg-rose-900 transition text-sm shadow-sm">Cancel</a>
    </div>

    <form action="/update-expense/{{ $voucher->id }}" method="POST" class="p-6 sm:p-8">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="col-span-1 md:col-span-2">
                <label class="block text-sm font-bold text-gray-700 mb-1">Transaction Date</label>
                <input type="date" name="voucher_date" value="{{ \Carbon\Carbon::parse($voucher->voucher_date)->format('Y-m-d') }}" 
                       class="w-full p-2.5 border border-gray-300 rounded-md focus:border-rose-500 focus:ring-1 focus:ring-rose-500">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Pay From (Bank/Cash)</label>
                <select name="cash_id" required class="w-full p-2.5 bg-white border border-gray-300 rounded-md focus:border-rose-500 focus:ring-1 focus:ring-rose-500 cursor-pointer">
                    @foreach($banks as $bank)
                        <option value="{{ $bank->id }}" {{ $currentCashId == $bank->id ? 'selected' : '' }}>{{ $bank->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Expense Category</label>
                <select name="expense_id" required class="w-full p-2.5 bg-white border border-gray-300 rounded-md focus:border-rose-500 focus:ring-1 focus:ring-rose-500 cursor-pointer">
                    <option value="" disabled>Select what you are paying for...</option>
                    @foreach($expenses as $expense)
                        <option value="{{ $expense->id }}" {{ $currentExpenseId == $expense->id ? 'selected' : '' }}>{{ $expense->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Expense Amount (৳)</label>
                <input type="number" step="0.01" name="amount" value="{{ $currentAmount }}" required 
                       class="w-full p-2.5 border border-gray-300 rounded-md focus:border-rose-500 focus:ring-1 focus:ring-rose-500" placeholder="0.00">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Details / Notes</label>
                <input type="text" name="notes" value="{{ $voucher->notes }}" 
                       class="w-full p-2.5 border border-gray-300 rounded-md focus:border-rose-500 focus:ring-1 focus:ring-rose-500" placeholder="e.g. Weekly worker wages">
            </div>
        </div>

        <div class="mt-8 flex justify-end">
            <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white font-bold py-2.5 px-6 rounded-md shadow-sm transition-colors active:scale-[0.99] flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                Update Expense
            </button>
        </div>
    </form>
</div>
@endsection
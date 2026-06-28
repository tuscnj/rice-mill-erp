@extends('layouts.app')
@section('title', 'Edit Account')
@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-12 mt-6 font-sans">
    
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 tracking-tight">Edit Account</h1>
            <p class="text-gray-500 mt-1">Update ledger details and opening balance</p>
        </div>
        <a href="/accounts" class="bg-gray-800 hover:bg-gray-900 text-white font-bold py-2 px-4 rounded-lg shadow transition">
            ← Back to Accounts
        </a>
    </div>

    {{-- 🚨 ADDED ERROR DISPLAY BOX: This will tell you if a save fails! --}}
    @if($errors->any())
        <div class="p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-lg font-bold">
            ⚠️ {{ $errors->first() }}
        </div>
    @endif

    <div class="bg-white p-8 rounded-2xl shadow-xl border-t-4 border-yellow-500 border border-gray-200">
        <form action="/update-account/{{ $account->id }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Account Name</label>
                    <input type="text" name="name" value="{{ $account->name }}" required class="w-full p-3 border border-gray-300 rounded-xl focus:border-yellow-500 outline-none focus:ring-2 focus:ring-yellow-500 transition">
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Account Type</label>
                    <select name="group_type" required class="w-full p-3 border border-gray-300 rounded-xl focus:border-yellow-500 outline-none focus:ring-2 focus:ring-yellow-500 bg-white transition">
                        <option value="Sundry Debtors" {{ $account->group_type == 'Sundry Debtors' ? 'selected' : '' }}>Customer (Debtor)</option>
                        <option value="Sundry Creditors" {{ $account->group_type == 'Sundry Creditors' ? 'selected' : '' }}>Supplier (Creditor)</option>
                        <option value="Cash" {{ $account->group_type == 'Cash' ? 'selected' : '' }}>Bank / Cash Account</option>
                        <option value="Direct Expenses" {{ $account->group_type == 'Direct Expenses' ? 'selected' : '' }}>Direct Expenses (Purchase)</option>
                        <option value="Direct Incomes" {{ $account->group_type == 'Direct Incomes' ? 'selected' : '' }}>Direct Incomes (Sales)</option>
                        <option value="Indirect Expenses" {{ $account->group_type == 'Indirect Expenses' ? 'selected' : '' }}>Indirect Expenses</option>
                        <option value="Indirect Incomes" {{ $account->group_type == 'Indirect Incomes' ? 'selected' : '' }}>Other Income</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Mobile Number</label>
                    <input type="text" name="mobile_number" value="{{ $account->mobile_number }}" class="w-full p-3 border border-gray-300 rounded-xl focus:border-yellow-500 outline-none focus:ring-2 focus:ring-yellow-500 transition">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Status</label>
                    <select name="is_active" required class="w-full p-3 border border-gray-300 rounded-xl focus:border-yellow-500 outline-none focus:ring-2 focus:ring-yellow-500 bg-white transition">
                        <option value="1" {{ $account->is_active ? 'selected' : '' }}>🟢 Active (Can Trade)</option>
                        <option value="0" {{ !$account->is_active ? 'selected' : '' }}>🔴 Inactive (Blocked)</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Address</label>
                    <input type="text" name="address" value="{{ $account->address }}" class="w-full p-3 border border-gray-300 rounded-xl focus:border-yellow-500 outline-none focus:ring-2 focus:ring-yellow-500 transition">
                </div>
            </div>

            <div class="bg-yellow-50 p-5 rounded-xl border border-yellow-200 mt-4">
                <label class="block text-sm font-bold text-gray-800 mb-2">Opening Balance (৳)</label>
                <input type="number" step="0.01" name="opening_balance" value="{{ $openingBalance }}" class="w-full p-3 border border-gray-300 shadow-sm rounded-lg focus:border-yellow-500 outline-none focus:ring-2 focus:ring-yellow-500 font-mono font-bold text-gray-800 transition">
                <p class="text-xs text-yellow-700 mt-2 font-semibold">Updating this will instantly mathematically recalculate the entire ledger balance.</p>
            </div>

            <button type="submit" class="w-full bg-yellow-500 text-white font-bold text-lg py-4 px-6 rounded-xl shadow-md hover:bg-yellow-600 hover:shadow-lg transition mt-4">
                Save & Recalculate Ledger
            </button>
        </form>
    </div>
</div>
@endsection
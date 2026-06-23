@extends('layouts.app')
@section('title', 'Accounts')
@section('content')

    <div class="max-w-5xl mx-auto space-y-6 pb-12">
        
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Accounts Directory</h1>
                <p class="text-gray-500">Manage Customers, Suppliers, and Banks</p>
            </div>
            <a href="/" class="bg-gray-800 hover:bg-gray-900 text-white font-bold py-2 px-4 rounded-lg shadow transition">
                ← Dashboard
            </a>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-xl border-t-4 border-yellow-500">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Create New Account</h2>
            <form action="/run-account" method="POST" class="flex flex-col md:flex-row gap-4 items-end">
                @csrf
                <div class="w-full md:w-2/5">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Account Name</label>
                    <input type="text" name="name" required class="w-full p-3 border-2 border-gray-200 rounded-lg focus:border-yellow-500" placeholder="e.g. Rahim Traders">
                </div>
                
                <div class="w-full md:w-1/4">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Account Type</label>
                    <select name="group_type" required class="w-full p-3 border-2 border-gray-200 rounded-lg focus:border-yellow-500 bg-white">
                        <option value="Sundry Debtors">Customer (Debtor)</option>
                        <option value="Sundry Creditors">Supplier (Creditor)</option>
                        <option value="Cash">Bank / Cash Account</option>
                        <option value="Indirect Expenses">Expense Category</option>
                        <option value="Indirect Incomes">Other Income Category</option>
                    </select>
                </div>

                <div class="w-full md:w-1/5">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Opening Balance (৳)</label>
                    <input type="number" step="0.01" name="opening_balance" class="w-full p-3 border-2 border-gray-200 rounded-lg focus:border-yellow-500" placeholder="0.00">
                </div>

                <div class="w-full md:w-auto">
                    <button type="submit" class="w-full bg-yellow-500 text-white font-bold py-3 px-6 rounded-lg shadow hover:bg-yellow-600 transition">
                        + Add 
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-800 text-white text-sm">
                        <th class="p-4 font-bold">Account Name</th>
                        <th class="p-4 font-bold">Type</th>
                        <th class="p-4 font-bold text-right">Current Balance</th>
                        <th class="p-4 font-bold text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($accounts as $account)
                    @php
                        // Determine natural sign for display purely from standard logic
                        $isAsset = in_array($account->group_type, ['Sundry Debtors', 'Cash', 'Direct Expenses', 'Indirect Expenses']);
                    @endphp
                    <tr class="hover:bg-gray-50 transition cursor-pointer">
                        <td class="p-4 font-bold text-gray-800">{{ $account->name }}</td>
                        <td class="p-4 text-gray-500 text-sm">
                            <span class="px-2 py-1 bg-gray-100 rounded text-xs font-semibold">{{ $account->group_type }}</span>
                        </td>
                        <td class="p-4 text-right font-mono font-bold {{ $account->balance == 0 ? 'text-gray-400' : ($isAsset ? 'text-green-600' : 'text-red-600') }}">
                            ৳ {{ number_format(abs($account->balance), 2) }}
                            <span class="text-[10px] text-gray-400 ml-1">{{ $account->balance == 0 ? '' : ($isAsset ? 'Dr' : 'Cr') }}</span>
                        </td>
                        <td class="p-4 text-center">
                            <div class="flex justify-center items-center gap-2">
                                <a href="/ledger/{{ $account->id }}" class="bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white border border-blue-200 hover:border-blue-600 px-3 py-1.5 rounded text-xs font-bold transition">Ledger</a>
                                <a href="/edit-account/{{ $account->id }}" class="bg-yellow-50 text-yellow-600 hover:bg-yellow-500 hover:text-white border border-yellow-200 hover:border-yellow-500 px-3 py-1.5 rounded text-xs font-bold transition">Edit</a>
                                <a href="/delete-account/{{ $account->id }}" onclick="return confirm('Are you sure you want to delete this account?')" class="bg-red-50 text-red-600 hover:bg-red-600 hover:text-white border border-red-200 hover:border-red-600 px-3 py-1.5 rounded text-xs font-bold transition">Delete</a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
@endsection
@extends('layouts.app')
@section('title', 'Accounts')
@section('content')

    <div class="max-w-7xl mx-auto space-y-6 pb-12 mt-4 sm:mt-6 font-sans">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 tracking-tight">Accounts Directory</h1>
                <p class="text-gray-500 text-sm sm:text-base mt-1">Manage Customers, Suppliers, and Banks</p>
            </div>
            <div class="w-full md:w-auto">
                <a href="/" class="w-full md:w-auto flex justify-center items-center gap-2 bg-slate-800 hover:bg-slate-900 text-white font-bold py-3 px-6 rounded-xl shadow-md transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Dashboard
                </a>
            </div>
        </div>

        <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-gray-100 border-t-4 border-t-yellow-500">
            <div class="flex items-center gap-2 mb-6">
                <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                <h2 class="text-xl font-bold text-gray-800">Create New Account</h2>
            </div>

            @if($errors->any())
                <div class="mb-5 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-lg text-sm font-semibold flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="/run-account" method="POST" class="space-y-5">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-end">
                    
                    {{-- ROW 1 --}}
                    <div class="md:col-span-4">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Account Name</label>
                        <input type="text" name="name" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 text-gray-800 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:bg-white transition-all duration-200" placeholder="e.g. Rahim Traders">
                    </div>
                    
                    <div class="md:col-span-4">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Account Type</label>
                        <select name="group_type" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 text-gray-800 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:bg-white transition-all duration-200 cursor-pointer appearance-none">
                            <option value="Sundry Debtors">Customer (Debtor)</option>
                            <option value="Sundry Creditors">Supplier (Creditor)</option>
                            <option value="Cash">Bank / Cash Account</option>
                            <option value="Indirect Expenses">Expense Category</option>
                            <option value="Indirect Incomes">Other Income Category</option>
                        </select>
                    </div>

                    <div class="md:col-span-4">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Mobile Number</label>
                        <input type="text" name="mobile_number" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 text-gray-800 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:bg-white transition-all duration-200" placeholder="017...">
                    </div>

                    {{-- ROW 2 --}}
                    <div class="md:col-span-6">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Address</label>
                        <input type="text" name="address" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 text-gray-800 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:bg-white transition-all duration-200" placeholder="Location details...">
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Status</label>
                        <select name="is_active" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 text-gray-800 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:bg-white transition-all duration-200 cursor-pointer appearance-none">
                            <option value="1">🟢 Active (Can Trade)</option>
                            <option value="0">🔴 Inactive (Blocked)</option>
                        </select>
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Opening Bal (৳)</label>
                        <input type="number" step="0.01" name="opening_balance" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 text-gray-800 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:bg-white transition-all duration-200" placeholder="0.00">
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-100 mt-6">
                    <button type="submit" class="w-full md:w-auto bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-3 px-8 rounded-xl shadow-md transition-all active:scale-95 flex justify-center items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Add Account
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-transparent md:bg-white rounded-none md:rounded-2xl shadow-none md:shadow-sm border-none md:border border-gray-100 overflow-hidden">
            
            <table class="w-full text-left border-collapse hidden md:table">
                <thead>
                    <tr class="bg-slate-800 text-white text-sm uppercase tracking-wider">
                        <th class="p-4 font-bold rounded-tl-xl md:rounded-none">Account Details</th>
                        <th class="p-4 font-bold">Type</th>
                        <th class="p-4 font-bold text-center">Status</th>
                        <th class="p-4 font-bold text-right">Current Balance</th>
                        <th class="p-4 font-bold text-center rounded-tr-xl md:rounded-none">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @foreach($accounts as $account)
                        @php
                            $isAsset = in_array($account->group_type, ['Sundry Debtors', 'Cash', 'Direct Expenses', 'Indirect Expenses']);
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-4">
                                <div class="font-bold text-gray-800 text-base">{{ $account->name }}</div>
                                @if($account->mobile_number) <div class="text-xs text-gray-500 mt-0.5">📞 {{ $account->mobile_number }}</div> @endif
                            </td>
                            <td class="p-4">
                                <span class="px-3 py-1 bg-gray-100 border border-gray-200 text-gray-600 rounded-lg text-xs font-bold inline-block">{{ $account->group_type }}</span>
                            </td>
                            <td class="p-4 text-center">
                                @if($account->is_active)
                                    <span class="px-2 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded text-[10px] font-bold uppercase">Active</span>
                                @else
                                    <span class="px-2 py-1 bg-red-50 text-red-700 border border-red-200 rounded text-[10px] font-bold uppercase">Inactive</span>
                                @endif
                            </td>
                            <td class="p-4 text-right font-mono font-bold text-base {{ $account->balance == 0 ? 'text-gray-400' : ($isAsset ? 'text-emerald-600' : 'text-red-600') }}">
                                ৳ {{ number_format(abs($account->balance), 2) }}
                                <span class="text-[10px] text-gray-400 ml-1">{{ $account->balance == 0 ? '' : ($isAsset ? 'Dr' : 'Cr') }}</span>
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex justify-center items-center gap-2">
                                    <a href="/ledger/{{ $account->id }}" class="bg-slate-100 text-slate-700 hover:bg-slate-800 hover:text-white border border-slate-200 hover:border-slate-800 px-3 py-1.5 rounded-lg text-xs font-bold transition">Ledger</a>
                                    <a href="/edit-account/{{ $account->id }}" class="bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white border border-blue-100 hover:border-blue-600 px-3 py-1.5 rounded-lg text-xs font-bold transition">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
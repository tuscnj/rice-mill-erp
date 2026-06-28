@extends('layouts.app')
@section('title', 'Invoice Center')
@section('content')

<div class="max-w-7xl mx-auto space-y-6 pb-12 mt-4 sm:mt-6 font-sans">
    
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 tracking-tight">Invoice Center</h1>
            <p class="text-gray-500 text-sm sm:text-base mt-1">Search, view, and print sales and purchase bills.</p>
        </div>
        
        <form method="GET" action="/invoices" class="flex flex-col lg:flex-row gap-3 items-end bg-white p-4 rounded-xl shadow-sm border border-gray-200 w-full xl:w-auto">
            
            <div class="w-full lg:w-auto">
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Search</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Party name, Ref, or ID..." class="w-full lg:w-48 p-2 text-sm border border-gray-200 rounded-lg outline-none focus:border-blue-500 bg-gray-50">
            </div>

            <div class="w-full lg:w-auto">
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Type</label>
                <select name="type" class="w-full lg:w-auto p-2 text-sm border border-gray-200 rounded-lg outline-none focus:border-blue-500 bg-gray-50">
                    <option value="All" {{ $type == 'All' ? 'selected' : '' }}>All Invoices</option>
                    <option value="Sales" {{ $type == 'Sales' ? 'selected' : '' }}>Sales</option>
                    <option value="Purchase" {{ $type == 'Purchase' ? 'selected' : '' }}>Purchase</option>
                    <option value="Sales Return" {{ $type == 'Sales Return' ? 'selected' : '' }}>Sales Return</option>
                    <option value="Purchase Return" {{ $type == 'Purchase Return' ? 'selected' : '' }}>Purchase Return</option>
                </select>
            </div>

            <div class="w-full lg:w-auto">
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">From</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full lg:w-auto p-2 text-sm border border-gray-200 rounded-lg outline-none focus:border-blue-500 bg-gray-50">
            </div>

            <div class="w-full lg:w-auto">
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">To</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full lg:w-auto p-2 text-sm border border-gray-200 rounded-lg outline-none focus:border-blue-500 bg-gray-50">
            </div>

            <button type="submit" class="w-full lg:w-auto bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-bold text-sm shadow-sm transition h-[38px]">Search</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-xl overflow-hidden border border-gray-200">
        <div class="overflow-x-auto rounded-xl">
            <table class="w-full text-left border-collapse text-sm min-w-[800px]">
                <thead>
                    <tr class="bg-slate-800 text-white border-b-2 border-slate-800">
                        <th class="p-4 font-bold w-28">Date</th>
                        <th class="p-4 font-bold w-32">Invoice #</th>
                        <th class="p-4 font-bold">Party Details</th>
                        <th class="p-4 font-bold w-36 text-right">Total Amount</th>
                        <th class="p-4 font-bold w-24 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($invoices as $inv)
                        @php
                            $party = $inv->entries->whereIn('account.group_type', ['Sundry Debtors', 'Sundry Creditors'])->first();
                            $partyName = $party ? $party->account->name : 'Unknown Party';
                            $amount = $inv->entries->where('entry_type', 'Debit')->sum('amount');
                        @endphp
                        
                        <tr class="hover:bg-slate-50 transition align-middle">
                            <td class="p-4 text-xs font-semibold text-gray-700 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($inv->voucher_date)->format('d-M-y') }}
                            </td>
                            
                            <td class="p-4">
                                {{-- 🚨 ADDED inline-block and whitespace-nowrap so "RETURN" never breaks layout --}}
                                <span class="inline-block px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider whitespace-nowrap
                                    {{ $inv->voucher_type == 'Purchase' ? 'bg-blue-50 text-blue-700' : '' }}
                                    {{ $inv->voucher_type == 'Sales' ? 'bg-purple-50 text-purple-700' : '' }}
                                    {{ str_contains($inv->voucher_type, 'Return') ? 'bg-orange-50 text-orange-700' : '' }}">
                                    {{ $inv->voucher_type }}
                                </span>
                                <div class="text-[10px] text-gray-400 mt-1.5 font-mono tracking-wider whitespace-nowrap">#VCH-{{ $inv->id }}</div>
                            </td>

                            <td class="p-4">
                                <div class="font-bold text-gray-900">{{ $partyName }}</div>
                                @if($inv->reference_number)
                                    <div class="text-[11px] text-gray-500 mt-0.5">Ref: {{ $inv->reference_number }}</div>
                                @endif
                            </td>

                            {{-- 🚨 ADDED whitespace-nowrap so the currency symbol stays beside the numbers --}}
                            <td class="p-4 text-right font-mono font-bold text-gray-900 whitespace-nowrap">
                                ৳ {{ number_format($amount, 2) }}
                            </td>

                            <td class="p-4 text-center">
                                <a href="/invoice/{{ $inv->id }}" class="inline-flex items-center justify-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-1.5 px-3 rounded-lg text-xs transition border border-slate-200">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-400 italic">No invoices found matching your criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
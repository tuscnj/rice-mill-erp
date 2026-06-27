@extends('layouts.app')
@section('title', 'Statement of Account')
@section('content')

<div class="max-w-6xl mx-auto space-y-6 pb-12 font-sans">
    
    {{-- WEB INTERFACE --}}
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 print:hidden">
        <div>
            <h2 class="text-3xl font-extrabold text-gray-800 tracking-tight">{{ $account->name }}</h2>
            <p class="text-gray-500 font-medium mt-1">{{ $account->group_type }} | Ledger Statement</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3 w-full xl:w-auto">
            <form method="GET" action="/ledger/{{ $account->id }}" class="flex flex-col sm:flex-row gap-3 items-end bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex-1">
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">From</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full sm:w-auto p-2 text-sm border border-gray-200 rounded-lg outline-none focus:border-blue-500 bg-gray-50">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">To</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full sm:w-auto p-2 text-sm border border-gray-200 rounded-lg outline-none focus:border-blue-500 bg-gray-50">
                </div>
                
                {{-- DETAILED TOGGLE --}}
                <div class="flex items-center h-[38px] px-2">
                    <label class="flex items-center cursor-pointer relative">
                        <input type="checkbox" name="detailed" value="1" {{ $isDetailed ? 'checked' : '' }} class="peer sr-only">
                        <div class="w-10 h-5 bg-gray-200 rounded-full peer peer-focus:ring-2 peer-focus:ring-blue-300 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                        <span class="ml-2 text-xs font-bold text-gray-600 uppercase tracking-wider">Detailed</span>
                    </label>
                </div>

                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-bold text-sm shadow-sm transition h-[38px]">Filter</button>
            </form>

            <div class="flex gap-2 items-end h-[38px] mt-auto pb-[2px]">
                {{-- CSV BUTTON --}}
                <a href="/ledger/{{ $account->id }}/export?start_date={{ $startDate }}&end_date={{ $endDate }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg font-bold shadow-sm transition flex items-center gap-2 text-sm h-full">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    CSV
                </a>
                
                {{-- PDF BUTTON --}}
                <a href="/ledger/{{ $account->id }}/pdf?start_date={{ $startDate }}&end_date={{ $endDate }}&detailed={{ $isDetailed ? '1' : '' }}" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-bold shadow-sm transition flex items-center gap-2 text-sm h-full">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    PDF
                </a>
            </div>
        </div>
    </div>

    {{-- LEDGER TABLE --}}
    <div class="bg-white rounded-xl shadow-xl overflow-hidden border border-gray-200 mt-6">
        <table class="w-full text-left border-collapse text-sm">
            <thead>
                <tr class="bg-slate-800 text-white border-b-2 border-slate-800">
                    <th class="p-3 font-bold w-24">Date</th>
                    <th class="p-3 font-bold">Particulars</th>
                    <th class="p-3 font-bold w-20">Vch Type</th>
                    <th class="p-3 font-bold text-right text-emerald-400">Debit (Dr)</th>
                    <th class="p-3 font-bold text-right text-rose-400">Credit (Cr)</th>
                    <th class="p-3 font-bold text-right bg-slate-900">Balance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                
                {{-- OPENING BALANCE --}}
                <tr class="bg-amber-50 font-bold text-gray-800">
                    <td class="p-3 text-center uppercase tracking-widest text-xs" colspan="3">Opening Balance</td>
                    <td class="p-3 text-right">-</td>
                    <td class="p-3 text-right">-</td>
                    <td class="p-3 text-right bg-amber-100">
                        {{ number_format(abs($openingBalanceRaw), 2) }} 
                        <span class="text-[10px] text-gray-500 ml-0.5">{{ $openingBalanceRaw >= 0 ? 'Dr' : 'Cr' }}</span>
                    </td>
                </tr>

                {{-- TRANSACTIONS --}}
                @forelse($entries as $row)
                @php $entry = $row['entry']; @endphp
                <tr class="hover:bg-slate-50 transition align-top">
                    <td class="p-3 whitespace-nowrap text-xs font-semibold text-gray-700">
                        {{ \Carbon\Carbon::parse($entry->voucher->voucher_date)->format('d-M-y') }}
                    </td>
                    <td class="p-3">
                        <div class="font-bold text-blue-700 text-sm">
                            @if($row['particulars']->count() > 0)
                                By {{ $row['particulars']->pluck('account.name')->implode(', ') }}
                            @else
                                Self / System Adjustment
                            @endif
                        </div>
                        
                        @if($isDetailed)
                            @if($entry->voucher->reference_number || $entry->voucher->notes)
                            <div class="text-[11px] text-gray-500 mt-1 italic leading-tight">
                                {{ $entry->voucher->reference_number ? 'Ref: '.$entry->voucher->reference_number.' | ' : '' }}
                                {{ $entry->voucher->notes ?? '' }}
                            </div>
                            @endif

                            @if($row['inventory']->count() > 0)
                                <div class="mt-2 bg-slate-50 rounded border border-slate-100 p-2">
                                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1">Inventory Included:</p>
                                    @foreach($row['inventory'] as $inv)
                                        <div class="text-[10px] text-gray-700 flex justify-between">
                                            <span>• {{ $inv->item->name }}</span>
                                            <span class="font-mono text-gray-500">{{ number_format($inv->quantity, 2) }} {{ $inv->item->unit ?? 'KG' }} @ {{ number_format($inv->rate, 2) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    </td>
                    <td class="p-3 text-xs text-gray-600 font-mono">
                        {{ $entry->voucher->voucher_type }}<br>
                        <span class="text-[9px] text-gray-400">#VCH-{{ $entry->voucher->id }}</span>
                    </td>
                    <td class="p-3 text-right font-mono font-bold text-emerald-600">
                        {{ $entry->entry_type == 'Debit' ? number_format($entry->amount, 2) : '' }}
                    </td>
                    <td class="p-3 text-right font-mono font-bold text-rose-600">
                        {{ $entry->entry_type == 'Credit' ? number_format($entry->amount, 2) : '' }}
                    </td>
                    <td class="p-3 text-right font-mono font-bold text-gray-900 bg-slate-50">
                        {{ number_format(abs($row['running_balance']), 2) }}
                        <span class="text-[10px] text-gray-500 ml-0.5">{{ $row['running_balance'] >= 0 ? 'Dr' : 'Cr' }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-8 text-center text-gray-400 italic">No transactions found for this period.</td>
                </tr>
                @endforelse

                {{-- CLOSING BALANCE --}}
                <tr class="bg-slate-800 text-white font-bold border-t-2 border-slate-800">
                    <td class="p-4 text-center tracking-widest uppercase text-xs sm:text-sm" colspan="3">Closing Balance</td>
                    <td class="p-4 text-right"></td>
                    <td class="p-4 text-right"></td>
                    <td class="p-4 text-right text-base sm:text-lg">
                        ৳ {{ number_format(abs($closingBalanceRaw), 2) }} 
                        <span class="text-xs text-gray-400 ml-1">{{ $closingBalanceRaw >= 0 ? 'Dr' : 'Cr' }}</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
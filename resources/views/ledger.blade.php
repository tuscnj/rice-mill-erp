@extends('layouts.app')
@section('title', 'Statement of Account')
@section('content')

<div class="max-w-7xl mx-auto space-y-6 pb-12 font-sans">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 print:hidden">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">{{ $account->name }}</h2>
            <p class="text-gray-500 font-medium">{{ $account->group_type }} | Statement of Account</p>
        </div>
        <div class="flex gap-2">
            <form method="GET" action="/ledger/{{ $account->id }}" class="flex gap-2 items-end bg-white p-2 rounded-lg shadow-sm border border-gray-200">
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">From</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="p-1.5 text-sm border border-gray-200 rounded outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">To</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="p-1.5 text-sm border border-gray-200 rounded outline-none focus:border-blue-500">
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded font-bold text-sm transition">Filter</button>
            </form>
            <a href="/ledger/{{ $account->id }}/export?start_date={{ $startDate }}&end_date={{ $endDate }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg font-bold shadow transition flex items-center">CSV</a>
            <button onclick="window.print()" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2.5 rounded-lg font-bold shadow transition flex items-center">Print</button>
        </div>
    </div>

    <div class="hidden print:block text-center border-b-2 border-gray-800 pb-4 mb-4">
        <h1 class="text-3xl font-black text-gray-900 uppercase tracking-widest">Atik Auto Rice Mills</h1>
        <h2 class="text-xl font-bold text-gray-700 mt-1">Ledger: {{ $account->name }}</h2>
        <p class="text-gray-500 text-sm">Period: {{ \Carbon\Carbon::parse($startDate)->format('d-M-Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d-M-Y') }}</p>
    </div>

    <div class="bg-white rounded-xl shadow-xl overflow-hidden border border-gray-200 print:shadow-none print:border-none">
        
        <table class="w-full text-left border-collapse text-sm">
            <thead>
                <tr class="bg-slate-800 text-white print:bg-gray-200 print:text-gray-900">
                    <th class="p-3 font-bold w-24">Date</th>
                    <th class="p-3 font-bold w-1/3">Particulars (Opposite Account)</th>
                    <th class="p-3 font-bold w-24">Vch Type</th>
                    <th class="p-3 font-bold text-right text-green-400 print:text-gray-800">Debit (Dr)</th>
                    <th class="p-3 font-bold text-right text-red-400 print:text-gray-800">Credit (Cr)</th>
                    <th class="p-3 font-bold text-right bg-slate-900 print:bg-gray-300">Balance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                
                <tr class="bg-yellow-50 font-bold text-gray-800">
                    <td class="p-3 border-r border-gray-200 text-center" colspan="3">OPENING BALANCE</td>
                    <td class="p-3 border-r border-gray-200 text-right">-</td>
                    <td class="p-3 border-r border-gray-200 text-right">-</td>
                    <td class="p-3 text-right bg-yellow-100">
                        ৳ {{ number_format(abs($openingBalanceRaw), 2) }} 
                        <span class="text-[10px] text-gray-500 ml-1">{{ $openingBalanceRaw >= 0 ? 'Dr' : 'Cr' }}</span>
                    </td>
                </tr>

                @forelse($entries as $row)
                @php $entry = $row['entry']; @endphp
                <tr class="hover:bg-slate-50 transition align-top">
                    
                    <td class="p-3 border-r border-gray-100 whitespace-nowrap text-xs font-semibold text-gray-700">
                        {{ \Carbon\Carbon::parse($entry->voucher->voucher_date)->format('d-M-Y') }}
                    </td>
                    
                    <td class="p-3 border-r border-gray-100">
                        <div class="font-bold text-blue-700 text-sm">
                            @if($row['particulars']->count() > 0)
                                By {{ $row['particulars']->pluck('account.name')->implode(', ') }}
                            @else
                                Self / System Adjustment
                            @endif
                        </div>
                        
                        <div class="text-[11px] text-gray-500 mt-1 italic leading-tight">
                            {{ $entry->voucher->reference_number ? 'Ref: '.$entry->voucher->reference_number.' | ' : '' }}
                            {{ $entry->voucher->notes ?? '' }}
                        </div>

                        @if($row['inventory']->count() > 0)
                            <div class="mt-2 bg-slate-100 rounded p-2 border border-slate-200">
                                <p class="text-[10px] font-bold text-gray-500 uppercase mb-1">Inventory Included:</p>
                                @foreach($row['inventory'] as $inv)
                                    <div class="text-[11px] text-gray-700 flex justify-between">
                                        <span>• {{ $inv->item->name }}</span>
                                        <span class="font-mono text-gray-500">{{ number_format($inv->quantity, 2) }} {{ $inv->item->unit ?? 'KG' }} @ ৳{{ number_format($inv->rate, 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </td>

                    <td class="p-3 border-r border-gray-100 text-xs text-gray-600 font-mono">
                        {{ $entry->voucher->voucher_type }}<br>
                        <span class="text-[10px] text-gray-400">#VCH-{{ $entry->voucher->id }}</span>
                    </td>

                    <td class="p-3 border-r border-gray-100 text-right font-mono font-bold text-green-600">
                        {{ $entry->entry_type == 'Debit' ? '৳ '.number_format($entry->amount, 2) : '' }}
                    </td>

                    <td class="p-3 border-r border-gray-100 text-right font-mono font-bold text-red-600">
                        {{ $entry->entry_type == 'Credit' ? '৳ '.number_format($entry->amount, 2) : '' }}
                    </td>

                    <td class="p-3 text-right font-mono font-bold text-gray-900 bg-slate-50">
                        ৳ {{ number_format(abs($row['running_balance']), 2) }}
                        <span class="text-[10px] text-gray-500 ml-1">{{ $row['running_balance'] >= 0 ? 'Dr' : 'Cr' }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-8 text-center text-gray-400 italic">No transactions found for this period.</td>
                </tr>
                @endforelse

                <tr class="bg-gray-800 text-white font-bold print:bg-gray-200 print:text-gray-900 border-t-4 border-gray-900">
                    <td class="p-4 text-center tracking-widest uppercase" colspan="3">CLOSING BALANCE</td>
                    <td class="p-4 text-right"></td>
                    <td class="p-4 text-right"></td>
                    <td class="p-4 text-right text-lg">
                        ৳ {{ number_format(abs($closingBalanceRaw), 2) }} 
                        <span class="text-xs text-gray-400 ml-1 print:text-gray-700">{{ $closingBalanceRaw >= 0 ? 'Dr' : 'Cr' }}</span>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
</div>

<style>
    @media print {
        body { background-color: white; }
        .print\:hidden { display: none !important; }
        .print\:shadow-none { box-shadow: none !important; }
        .print\:border-none { border: none !important; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd !important; padding: 8px !important; }
    }
</style>
@endsection
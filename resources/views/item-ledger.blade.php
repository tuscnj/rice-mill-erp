@extends('layouts.app')
@section('title', 'Item Ledger')
@section('content')

<div class="max-w-7xl mx-auto space-y-6 pb-10">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">{{ $item->name }} Ledger</h1>
            <p class="text-gray-500">Detailed inventory movement report</p>
        </div>
        <div class="flex gap-2 print:hidden">
            <form method="GET" action="/item-ledger/{{ $item->id }}" class="flex gap-2 items-end">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">From</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="p-2 border rounded">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">To</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="p-2 border rounded">
                </div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded font-bold">Filter</button>
            </form>
            <a href="/item-ledger/{{ $item->id }}/export?start_date={{ $startDate }}&end_date={{ $endDate }}" class="bg-green-600 text-white px-4 py-2 rounded font-bold print:hidden">Export CSV</a>
            <button onclick="window.print()" class="bg-slate-800 text-white px-4 py-2 rounded font-bold print:hidden">Print</button>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-slate-800 text-white p-4">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-xs uppercase tracking-wider text-slate-300">Opening Balance</p>
                    <p class="text-2xl font-bold">{{ number_format($openingBalance, 2) }} {{ $item->unit }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs uppercase tracking-wider text-slate-300">Closing Balance</p>
                    <p class="text-2xl font-bold">{{ number_format($runningBalance, 2) }} {{ $item->unit }}</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b">
                        <th class="p-3 text-left">Date</th>
                        <th class="p-3 text-left">Voucher</th>
                        <th class="p-3 text-left">Reference</th>
                        <th class="p-3 text-left">Narration</th>
                        <th class="p-3 text-right">In Qty</th>
                        <th class="p-3 text-right">Out Qty</th>
                        <th class="p-3 text-right">Rate</th>
                        <th class="p-3 text-right">Amount</th>
                        <th class="p-3 text-right">Running Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($details as $row)
                        @php
                            $movement = $row['movement'];
                            $voucher = $movement->voucher;
                        @endphp
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3">{{ \Carbon\Carbon::parse($voucher->voucher_date)->format('d M Y') }}</td>
                            <td class="p-3">{{ $voucher->voucher_type }}</td>
                            <td class="p-3">{{ $voucher->reference_number ?? '-' }}</td>
                            <td class="p-3">{{ $voucher->notes ?? '-' }}</td>
                            <td class="p-3 text-right text-green-700">{{ $row['in_qty'] > 0 ? number_format($row['in_qty'], 2) : '-' }}</td>
                            <td class="p-3 text-right text-red-700">{{ $row['out_qty'] > 0 ? number_format($row['out_qty'], 2) : '-' }}</td>
                            <td class="p-3 text-right">{{ number_format($movement->rate, 2) }}</td>
                            <td class="p-3 text-right">{{ number_format(($movement->quantity * $movement->rate), 2) }}</td>
                            <td class="p-3 text-right font-bold">{{ number_format($row['running_balance'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
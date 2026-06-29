@extends('layouts.app')
@section('title', 'Item Ledger')
@section('content')

<div class="max-w-7xl mx-auto space-y-4 sm:space-y-6 pb-12 font-sans print:max-w-full print:w-full print:mx-0 print:p-0 mt-4 sm:mt-0">
    
    {{-- WEB INTERFACE --}}
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 print:hidden px-4 sm:px-0">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 tracking-tight">{{ $item->name }}</h1>
            <p class="text-gray-500 font-medium mt-1 text-sm sm:text-base">Detailed inventory movement & tracking</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3 w-full xl:w-auto">
            {{-- COMPACT MOBILE GRID FILTER --}}
            <form method="GET" action="/item-ledger/{{ $item->id }}" class="grid grid-cols-2 sm:flex sm:flex-row gap-3 items-end bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex-1">
                <div class="col-span-1">
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">From</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full sm:w-auto p-2.5 sm:p-2 text-sm border border-gray-200 rounded-lg outline-none focus:border-blue-500 bg-gray-50">
                </div>
                <div class="col-span-1">
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">To</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full sm:w-auto p-2.5 sm:p-2 text-sm border border-gray-200 rounded-lg outline-none focus:border-blue-500 bg-gray-50">
                </div>
                
                <button type="submit" class="col-span-2 sm:col-span-1 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 sm:py-2 rounded-lg font-bold text-sm shadow-sm transition h-[42px] sm:h-[38px] w-full sm:w-auto">Filter</button>
            </form>

            <div class="flex gap-2 items-end h-[42px] sm:h-[38px] mt-auto pb-[2px]">
                <a href="/item-ledger/{{ $item->id }}/export?start_date={{ $startDate }}&end_date={{ $endDate }}" class="flex-1 sm:flex-none justify-center bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg font-bold shadow-sm transition flex items-center gap-2 text-sm h-full">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    CSV
                </a>
                <button onclick="window.print()" class="hidden sm:flex bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg font-bold shadow-sm transition items-center gap-2 text-sm h-full">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Print
                </button>
            </div>
        </div>
    </div>

    {{-- PRINT HEADER --}}
    <div class="hidden print:block mb-8">
        <div class="flex justify-between items-end border-b-2 border-slate-800 pb-4">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">{{ $item->name }}</h1>
                <p class="text-sm text-slate-600 mt-1">Inventory Stock Ledger</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-slate-800 font-bold">Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Generated: {{ now()->format('d M Y, h:i A') }}</p>
            </div>
        </div>
    </div>

    {{-- BALANCE BANNER --}}
    <div class="bg-slate-800 text-white rounded-2xl p-6 sm:p-8 shadow-lg flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 sm:gap-0 mx-4 sm:mx-0 print:mx-0 print:rounded-none print:shadow-none print:bg-slate-100 print:text-slate-900 print:p-4">
        <div>
            <p class="text-xs uppercase tracking-widest text-slate-400 font-bold mb-1 print:text-slate-500">Opening Stock</p>
            <p class="text-3xl sm:text-4xl font-black font-mono">
                {{ number_format($openingBalance, 2) }} <span class="text-base text-slate-300 ml-1 font-sans">{{ $item->unit }}</span>
            </p>
        </div>
        <div class="sm:text-right w-full sm:w-auto pt-4 sm:pt-0 border-t sm:border-t-0 border-slate-700 print:border-slate-300">
            <p class="text-xs uppercase tracking-widest text-slate-400 font-bold mb-1 print:text-slate-500">Closing Stock</p>
            <p class="text-3xl sm:text-4xl font-black font-mono text-emerald-400 print:text-slate-900">
                {{ number_format($runningBalance, 2) }} <span class="text-base text-slate-300 ml-1 font-sans print:text-slate-600">{{ $item->unit }}</span>
            </p>
        </div>
    </div>

    {{-- 🚨 MOBILE CARD LAYOUT (Hidden on Desktop & Print) --}}
    <div class="md:hidden flex flex-col gap-3 print:hidden px-4 mt-6">
        @forelse($details as $row)
        @php
            $movement = $row['movement'];
            $voucher = $movement->voucher;
            $isIn = $row['in_qty'] > 0;
        @endphp
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm relative overflow-hidden">
             
             {{-- Color Strip Indicator (+ IN = Green, - OUT = Red) --}}
             <div class="absolute left-0 top-0 bottom-0 w-1.5 {{ $isIn ? 'bg-emerald-400' : 'bg-rose-400' }}"></div>
             
             <div class="flex justify-between items-start mb-3 pl-3 border-b border-slate-50 pb-3">
                 <div>
                     <p class="text-xs font-black text-slate-400 tracking-wider mb-1">{{ \Carbon\Carbon::parse($voucher->voucher_date)->format('d M Y') }}</p>
                     <p class="font-bold text-slate-800 text-sm leading-tight">{{ $voucher->voucher_type }}</p>
                 </div>
                 <div class="text-right shrink-0">
                     <p class="text-[10px] text-slate-400 font-mono font-bold bg-slate-100 px-2 py-1 rounded">#VCH-{{ $voucher->id }}</p>
                 </div>
             </div>
             
             @if($voucher->reference_number || $voucher->notes)
                 <div class="pl-3 mb-3">
                     <p class="text-[11px] text-slate-500 italic bg-slate-50 p-2 rounded-lg leading-tight border border-slate-100">
                        {{ $voucher->reference_number ? 'Ref: '.$voucher->reference_number.' | ' : '' }}{{ $voucher->notes ?? '' }}
                     </p>
                 </div>
             @endif

             <div class="grid grid-cols-3 gap-2 bg-slate-50 p-3 rounded-xl ml-3 border border-slate-100 items-center">
                 <div>
                     <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Qty Move</p>
                     @if($isIn)
                        <p class="text-sm font-bold text-emerald-600 font-mono mt-0.5">+ {{ number_format($row['in_qty'], 2) }}</p>
                     @else
                        <p class="text-sm font-bold text-rose-600 font-mono mt-0.5">- {{ number_format($row['out_qty'], 2) }}</p>
                     @endif
                 </div>
                 <div class="text-center">
                     <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Rate</p>
                     <p class="text-xs font-bold text-slate-600 font-mono mt-0.5">৳ {{ number_format($movement->rate, 2) }}</p>
                 </div>
                 <div class="text-right">
                     <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Balance</p>
                     <p class="text-sm font-black text-slate-900 font-mono mt-0.5">
                        {{ number_format($row['running_balance'], 2) }}
                     </p>
                 </div>
             </div>
        </div>
        @empty
            <div class="text-center p-8 bg-white rounded-2xl border border-slate-200 shadow-sm text-slate-400 italic text-sm">No inventory movements recorded for this period.</div>
        @endforelse
    </div>

    {{-- DESKTOP / PRINT TABLE (Hidden on Mobile) --}}
    <div class="bg-white sm:rounded-xl sm:shadow-xl border-y sm:border border-slate-200 mt-6 print:shadow-none print:border-none print:rounded-none print:mt-4 hidden md:block print:block">
        <div class="overflow-x-auto rounded-xl print:overflow-visible">
            <table class="w-full text-left border-collapse text-sm print:text-xs min-w-[900px] print:min-w-full hidden md:table print:table">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 print:bg-slate-100 print:text-slate-900 border-b-2 border-slate-300">
                        <th class="p-4 print:py-2 print:px-1 font-bold w-24 uppercase tracking-wider text-xs">Date</th>
                        <th class="p-4 print:py-2 print:px-1 font-bold w-28 uppercase tracking-wider text-xs">Voucher</th>
                        <th class="p-4 print:py-2 print:px-1 font-bold uppercase tracking-wider text-xs">Reference / Narration</th>
                        <th class="p-4 print:py-2 print:px-1 font-bold text-right text-emerald-600 uppercase tracking-wider text-xs">In Qty (+)</th>
                        <th class="p-4 print:py-2 print:px-1 font-bold text-right text-rose-600 uppercase tracking-wider text-xs">Out Qty (-)</th>
                        <th class="p-4 print:py-2 print:px-1 font-bold text-right uppercase tracking-wider text-xs w-24">Rate (৳)</th>
                        <th class="p-4 print:py-2 print:px-1 font-bold text-right uppercase tracking-wider text-xs w-28">Amount (৳)</th>
                        <th class="p-4 print:py-2 print:px-1 font-bold text-right bg-slate-100 print:bg-slate-200 uppercase tracking-wider text-xs border-l border-white">Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 print:divide-gray-300">
                    @forelse($details as $row)
                        @php
                            $movement = $row['movement'];
                            $voucher = $movement->voucher;
                        @endphp
                        <tr class="hover:bg-slate-50 transition align-top print:break-inside-avoid">
                            <td class="p-4 print:py-2 print:px-1 whitespace-nowrap text-xs font-semibold text-gray-700">
                                {{ \Carbon\Carbon::parse($voucher->voucher_date)->format('d-M-y') }}
                            </td>
                            <td class="p-4 print:py-2 print:px-1 text-xs font-bold text-slate-800">
                                {{ $voucher->voucher_type }}<br>
                                <span class="text-[9px] text-gray-400 font-mono font-normal">#VCH-{{ $voucher->id }}</span>
                            </td>
                            <td class="p-4 print:py-2 print:px-1">
                                @if($voucher->reference_number)
                                    <div class="font-bold text-slate-700 text-xs mb-0.5">{{ $voucher->reference_number }}</div>
                                @endif
                                <div class="text-[11px] text-gray-500 italic">{{ $voucher->notes ?? '-' }}</div>
                            </td>
                            <td class="p-4 print:py-2 print:px-1 text-right font-mono font-bold text-emerald-600 print:text-slate-800">
                                {{ $row['in_qty'] > 0 ? number_format($row['in_qty'], 2) : '-' }}
                            </td>
                            <td class="p-4 print:py-2 print:px-1 text-right font-mono font-bold text-rose-600 print:text-slate-800">
                                {{ $row['out_qty'] > 0 ? number_format($row['out_qty'], 2) : '-' }}
                            </td>
                            <td class="p-4 print:py-2 print:px-1 text-right font-mono text-gray-600">
                                {{ number_format($movement->rate, 2) }}
                            </td>
                            <td class="p-4 print:py-2 print:px-1 text-right font-mono text-gray-800 font-semibold">
                                {{ number_format(($movement->quantity * $movement->rate), 2) }}
                            </td>
                            <td class="p-4 print:py-2 print:px-1 text-right font-mono font-black text-gray-900 bg-slate-50 print:bg-transparent border-l border-white print:border-none">
                                {{ number_format($row['running_balance'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-gray-400 italic">No inventory movements recorded for this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    @media print {
        @page { size: A4 landscape; margin: 10mm; }
        body { background-color: white !important; font-size: 10pt; margin: 0; padding: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .print\:max-w-full { max-width: 100% !important; }
        .print\:w-full { width: 100% !important; }
        .print\:mx-0 { margin-left: 0 !important; margin-right: 0 !important; }
        .print\:p-0 { padding: 0 !important; }
        .print\:overflow-visible { overflow: visible !important; }
        .print\:hidden { display: none !important; }
        .print\:block { display: block !important; }
        .print\:flex { display: flex !important; }
        .print\:shadow-none { box-shadow: none !important; }
        .print\:border-none { border: none !important; }
        .print\:bg-transparent { background-color: transparent !important; }
        .print\:bg-slate-100 { background-color: #f1f5f9 !important; }
        .print\:bg-slate-200 { background-color: #e2e8f0 !important; }
        .print\:text-slate-900 { color: #0f172a !important; }
        .print\:text-slate-800 { color: #1e293b !important; }
        table { width: 100%; border-collapse: collapse; }
        th { border-bottom: 2px solid #1e293b !important; }
        td { border-bottom: 1px solid #cbd5e1 !important; }
        .print\:break-inside-avoid { break-inside: avoid; page-break-inside: avoid; }
    }
</style>
@endsection
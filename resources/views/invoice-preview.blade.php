@extends('layouts.app')
@section('title', 'Invoice Preview')
@section('content')
@php
    $logoData = '';
    if($setting->logo_path && file_exists(public_path($setting->logo_path))) {
        $type = pathinfo(public_path($setting->logo_path), PATHINFO_EXTENSION);
        $data = file_get_contents(public_path($setting->logo_path));
        $logoData = 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
@endphp

<div class="max-w-5xl mx-auto space-y-6 pb-12 font-sans print:max-w-full print:w-full print:mx-0 print:p-0">
    
    {{-- ACTION BAR (Hidden when printing) --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-4 rounded-xl shadow-sm border border-gray-200 print:hidden">
        <div>
            <a href="/invoices" class="text-blue-600 hover:text-blue-800 font-bold text-sm flex items-center gap-1 bg-blue-50 px-4 py-2 rounded-lg transition">
                &larr; Back to Invoices
            </a>
        </div>
        <div class="flex gap-2 w-full sm:w-auto">
            <button onclick="window.print()" class="flex-1 sm:flex-none justify-center sm:justify-start bg-slate-800 hover:bg-slate-900 text-white px-5 py-2.5 rounded-lg font-bold shadow-sm transition flex items-center gap-2 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print
            </button>
            <a href="/invoice/{{ $voucher->id }}/pdf" class="flex-1 sm:flex-none justify-center sm:justify-start bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-lg font-bold shadow-sm transition flex items-center gap-2 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                Download PDF
            </a>
        </div>
    </div>

    {{-- INVOICE PAPER --}}
    <div class="bg-white p-8 sm:p-12 rounded-xl shadow-xl border border-gray-200 print:shadow-none print:border-none print:p-0 mt-6">
        
        {{-- HEADER --}}
        <div class="flex justify-between items-start border-b-2 border-slate-800 pb-6 mb-8">
            <div class="flex items-center gap-4">
                @if($logoData)
                    <img src="{{ $logoData }}" class="h-16 w-auto object-contain">
                @endif
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">{{ $setting->company_name }}</h1>
                    @if($setting->address) <p class="text-sm text-slate-600 mt-1">{{ $setting->address }}</p> @endif
                    <p class="text-sm text-slate-600">
                        @if($setting->phone) Phone: {{ $setting->phone }} @endif 
                        @if($setting->phone && $setting->email) | @endif 
                        @if($setting->email) Email: {{ $setting->email }} @endif
                    </p>
                </div>
            </div>
            <div class="text-right">
                <h2 class="text-3xl font-bold {{ str_contains($voucher->voucher_type, 'Return') ? 'text-orange-600' : 'text-blue-600' }} uppercase tracking-widest">{{ $voucher->voucher_type }}</h2>
                <p class="text-slate-500 font-mono mt-1">#VCH-{{ $voucher->id }}</p>
                <p class="text-sm text-slate-600 mt-1 font-bold">Date: {{ \Carbon\Carbon::parse($voucher->voucher_date)->format('d M Y') }}</p>
            </div>
        </div>

        {{-- BILL TO & INFO --}}
        <div class="flex justify-between items-start mb-8">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Bill To:</p>
                <h3 class="text-xl font-bold text-slate-800">{{ $party ? $party->name : 'Walk-in / General' }}</h3>
                @if($party) <p class="text-sm text-slate-500">{{ $party->group_type }}</p> @endif
            </div>
            <div class="text-right">
                @if($voucher->reference_number)
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Reference:</p>
                    <p class="text-sm font-semibold text-slate-800">{{ $voucher->reference_number }}</p>
                @endif
            </div>
        </div>

        {{-- ITEMS TABLE --}}
        @if($voucher->inventoryMovements->count() > 0)
        <table class="w-full text-left border-collapse mb-8">
            <thead>
                <tr class="bg-slate-100 text-slate-800 border-y-2 border-slate-800">
                    <th class="p-3 font-bold text-sm">Description</th>
                    <th class="p-3 font-bold text-sm text-right">Quantity</th>
                    <th class="p-3 font-bold text-sm text-right">Rate</th>
                    <th class="p-3 font-bold text-sm text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($voucher->inventoryMovements as $item)
                <tr>
                    <td class="p-3 text-sm text-slate-700 font-semibold">{{ $item->item->name ?? 'Unknown Item' }}</td>
                    <td class="p-3 text-sm text-slate-700 text-right">{{ number_format($item->quantity, 2) }} {{ $item->item->unit ?? 'KG' }}</td>
                    <td class="p-3 text-sm text-slate-700 text-right font-mono">৳ {{ number_format($item->rate, 2) }}</td>
                    <td class="p-3 text-sm text-slate-900 text-right font-mono font-bold">৳ {{ number_format($item->quantity * $item->rate, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="bg-slate-50 p-4 rounded-lg border border-slate-200 mb-8 text-center text-slate-500 text-sm italic">
            No inventory items associated with this transaction.
        </div>
        @endif

        {{-- 🚨 REDESIGNED TOTALS & BALANCES --}}
        <div class="flex flex-col sm:flex-row justify-between items-start gap-8">
            <div class="flex-1 w-full">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Narration / Notes:</p>
                <p class="text-sm text-slate-600 bg-slate-50 p-3 rounded border border-slate-100">{{ $voucher->notes ?? 'No additional notes provided.' }}</p>
            </div>
            
            <div class="w-full sm:w-2/5 xl:w-1/3">
                <table class="w-full text-right">
                    <tr class="border-b border-gray-200">
                        <td class="py-2 text-slate-500 font-bold text-sm">Invoice Amount:</td>
                        <td class="py-2 font-mono text-slate-800 font-bold">৳ {{ number_format($totalAmount, 2) }}</td>
                    </tr>
                    
                    @if($party)
                    <tr class="border-b border-gray-200">
                        <td class="py-2 text-slate-500 font-bold text-sm">Previous Balance:</td>
                        <td class="py-2 font-mono text-slate-800">
                            {{ number_format(abs($previousBalanceRaw), 2) }} <span class="text-[10px] text-slate-500">{{ $previousBalanceRaw >= 0 ? 'Dr' : 'Cr' }}</span>
                        </td>
                    </tr>
                    <tr class="bg-slate-800 text-white border border-slate-800">
                        <td class="py-3 px-4 font-bold text-base rounded-l-lg">NET BALANCE:</td>
                        <td class="py-3 px-4 font-mono font-bold text-base rounded-r-lg">
                            {{ number_format(abs($currentBalanceRaw), 2) }} <span class="text-xs text-slate-300">{{ $currentBalanceRaw >= 0 ? 'Dr' : 'Cr' }}</span>
                        </td>
                    </tr>
                    @else
                    <tr class="bg-slate-800 text-white border border-slate-800">
                        <td class="py-3 px-4 font-bold text-lg rounded-l-lg">TOTAL:</td>
                        <td class="py-3 px-4 font-mono font-bold text-lg rounded-r-lg">৳ {{ number_format($totalAmount, 2) }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- SIGNATURES --}}
        <div class="flex justify-between items-end mt-24 pt-8 border-t border-gray-200">
            <div class="text-center w-48 border-t border-slate-400 pt-2">
                <p class="text-xs font-bold text-slate-800">Customer / Supplier</p>
            </div>
            <div class="text-center w-48 border-t border-slate-400 pt-2">
                <p class="text-xs font-bold text-slate-800">Authorized Signature</p>
                <p class="text-[10px] text-slate-500">{{ $setting->company_name }}</p>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        @page { size: A4 portrait; margin: 10mm; }
        body { background-color: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .print\:hidden { display: none !important; }
        .print\:max-w-full { max-width: 100% !important; }
        .print\:w-full { width: 100% !important; }
        .print\:mx-0 { margin-left: 0 !important; margin-right: 0 !important; }
        .print\:p-0 { padding: 0 !important; }
        .print\:shadow-none { box-shadow: none !important; }
        .print\:border-none { border: none !important; }
    }
</style>
@endsection
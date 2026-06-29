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
            <a href="/invoices" class="text-blue-600 hover:text-blue-800 font-bold text-sm flex items-center gap-1 bg-blue-50 hover:bg-blue-100 px-4 py-2.5 rounded-lg transition">
                &larr; Back to Invoices
            </a>
        </div>
        <div class="flex gap-3 w-full sm:w-auto">
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

    {{-- INVOICE PAPER (Exact match of PDF structure on an A4 Canvas) --}}
    <div class="bg-white border border-gray-200 shadow-sm print:shadow-none print:border-none mt-6 relative overflow-hidden mx-auto w-full" style="max-width: 210mm; min-height: 297mm; padding: 40px;">
        
        {{-- Decorative Top Accent --}}
        <div class="absolute top-0 left-0 w-full h-2 {{ str_contains($voucher->voucher_type, 'Return') ? 'bg-orange-500' : 'bg-blue-600' }}"></div>

        {{-- ROW 1: BILLED FROM & BILLED TO --}}
        <div class="flex flex-row justify-between items-start gap-8 mb-6 mt-4">
            
            {{-- BILLED FROM --}}
            <div class="w-1/2 pr-4">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 border-b border-slate-200 pb-1.5">Billed From:</p>
                <div class="flex items-start gap-4">
                    @if($logoData)
                        <img src="{{ $logoData }}" class="max-h-16 max-w-[150px] object-contain">
                    @endif
                    <div>
                        <h1 class="text-2xl font-black text-slate-900 uppercase tracking-tight m-0">{{ $setting->company_name }}</h1>
                        @if($setting->address) <p class="text-[11px] text-slate-500 mt-1 mb-0">{{ $setting->address }}</p> @endif
                        <p class="text-[11px] text-slate-500 mt-0.5 mb-0">
                            @if($setting->phone) <span class="font-bold text-slate-700">P:</span> {{ $setting->phone }} @endif 
                            @if($setting->phone && $setting->email) | @endif 
                            @if($setting->email) <span class="font-bold text-slate-700">E:</span> {{ $setting->email }} @endif
                        </p>
                    </div>
                </div>
            </div>

            {{-- BILLED TO --}}
            <div class="w-1/2 pl-4">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 border-b border-slate-200 pb-1.5">Billed To:</p>
                <h3 class="text-xl font-black text-slate-900 mb-2 m-0">{{ $party ? $party->name : 'Walk-in / General' }}</h3>
                
                @if($party)                     
                    <div class="space-y-0.5 mt-2">
                        @if($party->mobile_number)
                            <p class="text-[11px] text-slate-600 m-0"><strong class="text-slate-700">Phone:</strong> {{ $party->mobile_number }}</p>
                        @endif
                        @if($party->address)
                            <p class="text-[11px] text-slate-600 m-0"><strong class="text-slate-700">Address:</strong> {{ $party->address }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- ROW 2: INVOICE META (Middle Row Ribbon) --}}
        <div class="bg-slate-50 border-y border-slate-200 py-3 mb-6 grid {{ $voucher->reference_number ? 'grid-cols-4' : 'grid-cols-3' }} divide-x divide-slate-200 text-center">
            <div class="px-2">
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Invoice Type</p>
                <h2 class="text-base font-black {{ str_contains($voucher->voucher_type, 'Return') ? 'text-orange-600' : 'text-blue-600' }} uppercase m-0">{{ $voucher->voucher_type }}</h2>
            </div>
            <div class="px-2">
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Invoice No.</p>
                <p class="text-base font-black text-slate-900 m-0">#VCH-{{ $voucher->id }}</p>
            </div>
            <div class="px-2">
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Date</p>
                <p class="text-sm font-bold text-slate-900 m-0 mt-0.5">{{ \Carbon\Carbon::parse($voucher->voucher_date)->format('d M Y') }}</p>
            </div>
            @if($voucher->reference_number)
            <div class="px-2">
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Reference</p>
                <p class="text-sm font-bold text-slate-900 m-0 mt-0.5">{{ $voucher->reference_number }}</p>
            </div>
            @endif
        </div>

        {{-- ITEMS TABLE --}}
        @if($voucher->inventoryMovements->count() > 0)
        <div class="mb-6">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th class="bg-slate-800 text-white p-3 font-bold text-[11px] tracking-widest uppercase">Description</th>
                        <th class="bg-slate-800 text-white p-3 font-bold text-[11px] tracking-widest uppercase text-right">Quantity</th>
                        <th class="bg-slate-800 text-white p-3 font-bold text-[11px] tracking-widest uppercase text-right">Rate</th>
                        <th class="bg-slate-800 text-white p-3 font-bold text-[11px] tracking-widest uppercase text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($voucher->inventoryMovements as $item)
                    <tr>
                        <td class="p-3 text-[12px] text-slate-800 font-bold border-b border-slate-200">{{ $item->item->name ?? 'Unknown Item' }}</td>
                        <td class="p-3 text-[12px] text-slate-700 text-right border-b border-slate-200">{{ number_format($item->quantity, 2) }} <span class="text-[10px] text-slate-400">{{ $item->item->unit ?? 'KG' }}</span></td>
                        <td class="p-3 text-[12px] text-slate-700 text-right border-b border-slate-200">৳ {{ number_format($item->rate, 2) }}</td>
                        <td class="p-3 text-[12px] text-slate-900 text-right font-bold border-b border-slate-200">৳ {{ number_format($item->quantity * $item->rate, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="bg-slate-50 p-5 border border-slate-200 mb-6 text-center text-slate-400 font-medium italic text-xs rounded">
            No inventory items associated with this transaction.
        </div>
        @endif

        {{-- TOTALS & BALANCES --}}
        <div class="flex flex-row justify-between items-start gap-8">
            <div class="w-1/2 pr-6">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Narration / Notes:</p>
                <p class="text-[11px] text-slate-600 bg-slate-50 p-4 border border-slate-200 rounded font-medium italic m-0">{{ $voucher->notes ?? 'No additional notes provided.' }}</p>
            </div>
            
            <div class="w-1/2">
                <table class="w-full text-right border-collapse">
                    <tr>
                        <td class="py-2.5 px-4 text-slate-500 font-bold text-[11px] uppercase tracking-widest border-b border-slate-200">Invoice Amount:</td>
                        <td class="py-2.5 px-4 text-slate-900 font-bold text-sm border-b border-slate-200">৳ {{ number_format($totalAmount, 2) }}</td>
                    </tr>
                    
                    @if($party)
                    <tr>
                        <td class="py-2.5 px-4 text-slate-500 font-bold text-[11px] uppercase tracking-widest border-b border-slate-200">Previous Balance:</td>
                        <td class="py-2.5 px-4 text-slate-700 font-bold text-sm border-b border-slate-200">
                            {{ number_format(abs($previousBalanceRaw), 2) }} <span class="text-[9px] bg-slate-200 text-slate-600 px-1 py-0.5 rounded ml-1 align-middle">{{ $previousBalanceRaw >= 0 ? 'Dr' : 'Cr' }}</span>
                        </td>
                    </tr>
                    <tr class="bg-slate-900 text-white">
                        <td class="py-3.5 px-4 font-black text-sm tracking-widest rounded-l">NET BALANCE:</td>
                        <td class="py-3.5 px-4 font-black text-base rounded-r">
                            {{ number_format(abs($currentBalanceRaw), 2) }} <span class="text-[10px] text-slate-400 ml-1">{{ $currentBalanceRaw >= 0 ? 'Dr' : 'Cr' }}</span>
                        </td>
                    </tr>
                    @else
                    <tr class="bg-slate-900 text-white">
                        <td class="py-3.5 px-4 font-black text-sm tracking-widest rounded-l">TOTAL:</td>
                        <td class="py-3.5 px-4 font-black text-base rounded-r">৳ {{ number_format($totalAmount, 2) }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- SIGNATURES --}}
        <div class="flex justify-between items-end mt-20 pt-2">
            <div class="text-center w-[220px] border-t border-slate-700 pt-2">
                <p class="text-[11px] font-bold text-slate-800 uppercase tracking-widest m-0">Customer / Supplier</p>
            </div>
            <div class="text-center w-[220px] border-t border-slate-700 pt-2">
                <p class="text-[11px] font-bold text-slate-800 uppercase tracking-widest m-0">Authorized Signature</p>
                <p class="text-[9px] font-bold text-slate-400 mt-1 m-0">{{ $setting->company_name }}</p>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        @page { size: A4 portrait; margin: 5mm -5mm 0mm -5mm; }
        body { background-color: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; margin: 0; padding: 0;}
        .print\:hidden { display: none !important; }
        .print\:shadow-none { box-shadow: none !important; }
        .print\:border-none { border: none !important; }
        .print\:p-0 { padding: 0 !important; }
        .mx-auto { margin: 0 !important; }
    }
</style>
@endsection
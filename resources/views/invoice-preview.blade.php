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

    {{-- INVOICE PAPER (Shadow removed, clean flat border applied) --}}
    <div class="bg-white p-8 sm:p-12 border border-gray-200 print:border-none print:p-0 mt-6 relative overflow-hidden">
        
        {{-- Decorative Top Accent --}}
        <div class="absolute top-0 left-0 w-full h-2 {{ str_contains($voucher->voucher_type, 'Return') ? 'bg-orange-500' : 'bg-blue-600' }}"></div>

        {{-- ROW 1: BILLED FROM & BILLED TO --}}
        <div class="flex flex-col md:flex-row justify-between items-start gap-8 mb-6 mt-4">
            
            {{-- BILLED FROM --}}
            <div class="w-full md:w-1/2">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3 border-b border-slate-100 pb-2">Billed From:</p>
                <div class="flex items-center gap-4">
                    @if($logoData)
                        <img src="{{ $logoData }}" class="h-16 w-auto object-contain">
                    @endif
                    <div>
                        <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ $setting->company_name }}</h1>
                        @if($setting->address) <p class="text-sm text-slate-500 mt-1">{{ $setting->address }}</p> @endif
                        <p class="text-sm text-slate-500 mt-0.5">
                            @if($setting->phone) <span class="font-semibold text-slate-700">P:</span> {{ $setting->phone }} @endif 
                            @if($setting->phone && $setting->email) | @endif 
                            @if($setting->email) <span class="font-semibold text-slate-700">E:</span> {{ $setting->email }} @endif
                        </p>
                    </div>
                </div>
            </div>

            {{-- BILLED TO --}}
            <div class="w-full md:w-1/2">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3 border-b border-slate-100 pb-2">Billed To:</p>
                <h3 class="text-2xl font-extrabold text-slate-900">{{ $party ? $party->name : 'Walk-in / General' }}</h3>
                
                @if($party) 
                    <p class="text-[10px] font-bold uppercase tracking-wider text-blue-600 bg-blue-50 border border-blue-100 inline-block px-2 py-0.5 rounded mt-1 mb-2">{{ $party->group_type }}</p> 
                    
                    <div class="space-y-1">
                        @if($party->mobile_number)
                        <div class="flex items-center gap-2 text-slate-600">
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            <span class="text-sm font-medium">{{ $party->mobile_number }}</span>
                        </div>
                        @endif
                        @if($party->address)
                        <div class="flex items-start gap-2 text-slate-600">
                            <svg class="w-3.5 h-3.5 text-slate-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <span class="text-sm font-medium leading-tight">{{ $party->address }}</span>
                        </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- ROW 2: INVOICE META (Middle Row Ribbon) --}}
        <div class="bg-slate-50 border-y border-slate-200 py-4 px-6 mb-8 flex flex-wrap justify-between items-center gap-4 rounded">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Invoice Type</p>
                <h2 class="text-xl font-black {{ str_contains($voucher->voucher_type, 'Return') ? 'text-orange-600' : 'text-blue-600' }} uppercase tracking-widest">{{ $voucher->voucher_type }}</h2>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Invoice No.</p>
                <p class="text-lg font-black text-slate-800 font-mono">#VCH-{{ $voucher->id }}</p>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Date</p>
                <p class="text-lg font-bold text-slate-800">{{ \Carbon\Carbon::parse($voucher->voucher_date)->format('d M Y') }}</p>
            </div>
            @if($voucher->reference_number)
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Reference</p>
                <p class="text-lg font-bold text-slate-800">{{ $voucher->reference_number }}</p>
            </div>
            @endif
        </div>

        {{-- ITEMS TABLE --}}
        @if($voucher->inventoryMovements->count() > 0)
        <div class="rounded overflow-hidden border border-slate-200 mb-8">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-800 text-white">
                        <th class="p-4 font-bold text-sm tracking-wider uppercase">Description</th>
                        <th class="p-4 font-bold text-sm tracking-wider uppercase text-right">Quantity</th>
                        <th class="p-4 font-bold text-sm tracking-wider uppercase text-right">Rate</th>
                        <th class="p-4 font-bold text-sm tracking-wider uppercase text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($voucher->inventoryMovements as $item)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 text-sm text-slate-800 font-bold">{{ $item->item->name ?? 'Unknown Item' }}</td>
                        <td class="p-4 text-sm text-slate-700 text-right font-mono">{{ number_format($item->quantity, 2) }} <span class="text-xs text-slate-400">{{ $item->item->unit ?? 'KG' }}</span></td>
                        <td class="p-4 text-sm text-slate-700 text-right font-mono">৳ {{ number_format($item->rate, 2) }}</td>
                        <td class="p-4 text-sm text-slate-900 text-right font-mono font-black">৳ {{ number_format($item->quantity * $item->rate, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="bg-slate-50 p-6 rounded-xl border border-slate-200 mb-8 text-center text-slate-500 font-medium">
            No inventory items associated with this transaction.
        </div>
        @endif

        {{-- TOTALS & BALANCES --}}
        <div class="flex flex-col sm:flex-row justify-between items-start gap-8">
            <div class="flex-1 w-full">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Narration / Notes:</p>
                <p class="text-sm text-slate-600 bg-slate-50 p-4 rounded border border-slate-100 font-medium italic">{{ $voucher->notes ?? 'No additional notes provided.' }}</p>
            </div>
            
            <div class="w-full sm:w-1/2 xl:w-2/5">
                <table class="w-full text-right border-collapse">
                    <tr class="border-b border-gray-100">
                        <td class="py-3 text-slate-500 font-bold text-sm uppercase tracking-wider">Invoice Amount:</td>
                        <td class="py-3 font-mono text-slate-900 font-black text-lg">৳ {{ number_format($totalAmount, 2) }}</td>
                    </tr>
                    
                    @if($party)
                    <tr class="border-b border-gray-100">
                        <td class="py-3 text-slate-500 font-bold text-sm uppercase tracking-wider">Previous Balance:</td>
                        <td class="py-3 font-mono text-slate-700 font-bold text-lg">
                            {{ number_format(abs($previousBalanceRaw), 2) }} <span class="text-[10px] bg-slate-200 px-1.5 py-0.5 rounded text-slate-600">{{ $previousBalanceRaw >= 0 ? 'Dr' : 'Cr' }}</span>
                        </td>
                    </tr>
                    <tr class="bg-slate-900 text-white shadow-md overflow-hidden rounded">
                        <td class="py-4 px-5 font-black text-lg rounded-l tracking-wider">NET BALANCE:</td>
                        <td class="py-4 px-5 font-mono font-black text-xl rounded-r">
                            {{ number_format(abs($currentBalanceRaw), 2) }} <span class="text-xs text-slate-400 ml-1">{{ $currentBalanceRaw >= 0 ? 'Dr' : 'Cr' }}</span>
                        </td>
                    </tr>
                    @else
                    <tr class="bg-slate-900 text-white shadow-md overflow-hidden rounded">
                        <td class="py-4 px-5 font-black text-lg rounded-l tracking-wider">TOTAL:</td>
                        <td class="py-4 px-5 font-mono font-black text-xl rounded-r">৳ {{ number_format($totalAmount, 2) }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- SIGNATURES --}}
        <div class="flex justify-between items-end mt-24 pt-8 border-t border-gray-200">
            <div class="text-center w-48 border-t border-slate-400 pt-2">
                <p class="text-xs font-bold text-slate-800 uppercase tracking-widest">Customer / Supplier</p>
            </div>
            <div class="text-center w-48 border-t border-slate-400 pt-2">
                <p class="text-xs font-bold text-slate-800 uppercase tracking-widest">Authorized Signature</p>
                <p class="text-[10px] font-bold text-slate-400 mt-1">{{ $setting->company_name }}</p>
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
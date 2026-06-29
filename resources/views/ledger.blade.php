@extends('layouts.app')
@section('title', 'Statement of Account')
@section('content')

@php
    // Securely convert image to Base64 so the Print Preview never drops the logo
    $logoData = '';
    if($setting->logo_path && file_exists(public_path($setting->logo_path))) {
        $type = pathinfo(public_path($setting->logo_path), PATHINFO_EXTENSION);
        $data = file_get_contents(public_path($setting->logo_path));
        $logoData = 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
@endphp

<div class="max-w-6xl mx-auto space-y-4 sm:space-y-6 pb-12 font-sans print:max-w-full print:w-full print:mx-0 print:p-0 mt-4 sm:mt-0">
    
    {{-- WEB INTERFACE --}}
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 print:hidden px-4 sm:px-0">
        <div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-800 tracking-tight">{{ $account->name }}</h2>
            <p class="text-gray-500 font-medium mt-1 text-sm sm:text-base">{{ $account->group_type }} | Ledger Statement</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3 w-full xl:w-auto">
            {{-- COMPACT MOBILE GRID FILTER --}}
            <form method="GET" action="/ledger/{{ $account->id }}" class="grid grid-cols-2 sm:flex sm:flex-row gap-3 items-end bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex-1">
                <div class="col-span-1">
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">From</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full sm:w-auto p-2.5 sm:p-2 text-sm border border-gray-200 rounded-lg outline-none focus:border-blue-500 bg-gray-50">
                </div>
                <div class="col-span-1">
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">To</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full sm:w-auto p-2.5 sm:p-2 text-sm border border-gray-200 rounded-lg outline-none focus:border-blue-500 bg-gray-50">
                </div>
                
                {{-- DETAILED TOGGLE --}}
                <div class="col-span-1 flex items-center h-[42px] sm:h-[38px] px-1 sm:px-2">
                    <label class="flex items-center cursor-pointer relative">
                        <input type="checkbox" name="detailed" value="1" {{ $isDetailed ? 'checked' : '' }} class="peer sr-only">
                        <div class="w-10 h-5 bg-gray-200 rounded-full peer peer-focus:ring-2 peer-focus:ring-blue-300 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                        <span class="ml-2 text-xs font-bold text-gray-600 uppercase tracking-wider">Detailed</span>
                    </label>
                </div>

                <button type="submit" class="col-span-1 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 sm:py-2 rounded-lg font-bold text-sm shadow-sm transition h-[42px] sm:h-[38px] w-full sm:w-auto">Filter</button>
            </form>

            <div class="flex gap-2 items-end h-[42px] sm:h-[38px] mt-auto pb-[2px]">
                <a href="/ledger/{{ $account->id }}/export?start_date={{ $startDate }}&end_date={{ $endDate }}" class="flex-1 sm:flex-none justify-center bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg font-bold shadow-sm transition flex items-center gap-2 text-sm h-full">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    CSV
                </a>
                <button onclick="window.print()" class="hidden sm:flex bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg font-bold shadow-sm transition items-center gap-2 text-sm h-full">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Print
                </button>
                <a href="/ledger/{{ $account->id }}/pdf?start_date={{ $startDate }}&end_date={{ $endDate }}&detailed={{ $isDetailed ? '1' : '' }}" class="flex-1 sm:flex-none justify-center bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-bold shadow-sm transition flex items-center gap-2 text-sm h-full">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    PDF
                </a>
            </div>
        </div>
    </div>

    {{-- PRINT LETTERHEAD --}}
    <div class="hidden print:block mb-8">
        <div class="flex justify-between items-start border-b-2 border-slate-800 pb-6">
            <div class="flex items-center gap-4">
                @if($logoData)
                    <img src="{{ $logoData }}" alt="Logo" class="h-16 w-auto object-contain">
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
                <h2 class="text-2xl font-bold text-slate-800 uppercase tracking-widest">Statement</h2>
                <p class="text-slate-600 font-bold mt-1 text-lg">{{ $account->name }}</p>
                <p class="text-sm text-slate-500 mt-1">Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
                <p class="text-xs text-slate-400 mt-1">Generated: {{ now()->format('d M Y, h:i A') }}</p>
            </div>
        </div>
    </div>

    {{-- 🚨 MOBILE CARD LAYOUT (Hidden on Desktop & Print) --}}
    <div class="md:hidden flex flex-col gap-3 print:hidden px-4 pb-4 mt-6">
        
        {{-- Opening Balance Card --}}
        <div class="bg-amber-50 rounded-2xl p-5 border border-amber-200 shadow-sm flex flex-col gap-1">
             <p class="text-[10px] font-black text-amber-600 uppercase tracking-widest">Opening Balance</p>
             <p class="text-2xl font-black text-amber-900 font-mono">
                 ৳ {{ number_format(abs($openingBalanceRaw), 2) }} 
                 <span class="text-xs text-amber-700 bg-amber-200/50 px-1.5 py-0.5 rounded">{{ $openingBalanceRaw >= 0 ? 'Dr' : 'Cr' }}</span>
             </p>
        </div>

        {{-- Transactions Cards --}}
        @forelse($entries as $row)
        @php $entry = $row['entry']; @endphp
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm relative overflow-hidden">
             
             {{-- Color Strip Indicator --}}
             <div class="absolute left-0 top-0 bottom-0 w-1.5 {{ $entry->entry_type == 'Debit' ? 'bg-emerald-400' : 'bg-rose-400' }}"></div>
             
             <div class="flex justify-between items-start mb-3 pl-3 border-b border-slate-50 pb-3">
                 <div>
                     <p class="text-xs font-black text-slate-400 tracking-wider mb-1">{{ \Carbon\Carbon::parse($entry->voucher->voucher_date)->format('d M Y') }}</p>
                     <p class="font-bold text-blue-700 text-sm leading-tight">
                         @if($row['particulars']->count() > 0)
                             By {{ $row['particulars']->pluck('account.name')->implode(', ') }}
                         @else
                             Self / System Adjustment
                         @endif
                     </p>
                 </div>
                 <div class="text-right shrink-0">
                     <span class="inline-block bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-widest mb-1">{{ $entry->voucher->voucher_type }}</span>
                     <p class="text-[10px] text-slate-400 font-mono font-bold">#VCH-{{ $entry->voucher->id }}</p>
                 </div>
             </div>
             
             @if($isDetailed && ($entry->voucher->reference_number || $entry->voucher->notes))
                 <div class="pl-3 mb-3">
                     <p class="text-[11px] text-slate-500 italic bg-slate-50 p-2 rounded-lg leading-tight border border-slate-100">
                        {{ $entry->voucher->reference_number ? 'Ref: '.$entry->voucher->reference_number.' | ' : '' }}{{ $entry->voucher->notes ?? '' }}
                     </p>
                 </div>
             @endif

             @if($isDetailed && $row['inventory']->count() > 0)
                <div class="pl-3 mb-3">
                    <div class="bg-slate-50 rounded-lg border border-slate-100 p-2">
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Inventory Included:</p>
                        @foreach($row['inventory'] as $inv)
                            <div class="text-[10px] text-slate-700 flex justify-between">
                                <span>• {{ $inv->item->name }}</span>
                                <span class="font-mono text-slate-500">{{ number_format($inv->quantity, 2) }} {{ $inv->item->unit ?? 'KG' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
             @endif

             <div class="grid grid-cols-3 gap-2 bg-slate-50 p-3 rounded-xl ml-3 border border-slate-100">
                 <div>
                     <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Debit (In)</p>
                     <p class="text-xs font-bold text-emerald-600 font-mono mt-0.5">{{ $entry->entry_type == 'Debit' ? number_format($entry->amount, 2) : '-' }}</p>
                 </div>
                 <div>
                     <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Credit (Out)</p>
                     <p class="text-xs font-bold text-rose-600 font-mono mt-0.5">{{ $entry->entry_type == 'Credit' ? number_format($entry->amount, 2) : '-' }}</p>
                 </div>
                 <div class="text-right">
                     <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Balance</p>
                     <p class="text-xs font-black text-slate-900 font-mono mt-0.5">
                        {{ number_format(abs($row['running_balance']), 2) }} 
                        <span class="text-[9px] text-slate-500 bg-slate-200 px-1 py-0.5 rounded ml-0.5">{{ $row['running_balance'] >= 0 ? 'Dr' : 'Cr' }}</span>
                     </p>
                 </div>
             </div>
        </div>
        @empty
            <div class="text-center p-8 bg-white rounded-2xl border border-slate-200 shadow-sm text-slate-400 italic text-sm">No transactions found for this period.</div>
        @endforelse

        {{-- Closing Balance Card --}}
        <div class="bg-slate-800 rounded-2xl p-5 shadow-lg flex flex-col gap-1 mt-2">
             <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Closing Balance</p>
             <p class="text-2xl font-black text-white font-mono">
                 ৳ {{ number_format(abs($closingBalanceRaw), 2) }} 
                 <span class="text-xs text-slate-300 bg-slate-700 px-1.5 py-0.5 rounded">{{ $closingBalanceRaw >= 0 ? 'Dr' : 'Cr' }}</span>
             </p>
        </div>
    </div>

    {{-- DESKTOP / PRINT TABLE (Hidden on Mobile) --}}
    <div class="bg-white sm:rounded-xl sm:shadow-xl border-y sm:border border-slate-200 mt-6 print:shadow-none print:border-none print:rounded-none print:mt-0 hidden md:block print:block">
        <div class="overflow-x-auto rounded-xl print:overflow-visible">
            <table class="w-full text-left border-collapse text-sm print:text-xs min-w-[800px] print:min-w-full hidden md:table print:table">
                <thead>
                    <tr class="bg-slate-800 text-white print:bg-slate-100 print:text-slate-900 border-b-2 border-slate-800">
                        <th class="p-4 print:py-2 print:px-1 font-bold w-24">Date</th>
                        <th class="p-4 print:py-2 print:px-1 font-bold">Particulars</th>
                        <th class="p-4 print:py-2 print:px-1 font-bold w-28">Vch Type</th>
                        <th class="p-4 print:py-2 print:px-1 font-bold text-right text-emerald-400 print:text-slate-900">Debit (Dr)</th>
                        <th class="p-4 print:py-2 print:px-1 font-bold text-right text-rose-400 print:text-slate-900">Credit (Cr)</th>
                        <th class="p-4 print:py-2 print:px-1 font-bold text-right bg-slate-900 print:bg-slate-200">Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 print:divide-gray-300">
                    
                    {{-- OPENING BALANCE --}}
                    <tr class="bg-amber-50 font-bold text-gray-800 print:bg-transparent">
                        <td class="p-4 print:py-2 print:px-1 text-center uppercase tracking-widest text-xs" colspan="3">Opening Balance</td>
                        <td class="p-4 print:py-2 print:px-1 text-right">-</td>
                        <td class="p-4 print:py-2 print:px-1 text-right">-</td>
                        <td class="p-4 print:py-2 print:px-1 text-right bg-amber-100 print:bg-transparent">
                            {{ number_format(abs($openingBalanceRaw), 2) }} 
                            <span class="text-[10px] text-gray-500 ml-0.5">{{ $openingBalanceRaw >= 0 ? 'Dr' : 'Cr' }}</span>
                        </td>
                    </tr>

                    {{-- TRANSACTIONS --}}
                    @forelse($entries as $row)
                    @php $entry = $row['entry']; @endphp
                    <tr class="hover:bg-slate-50 transition align-top print:break-inside-avoid">
                        <td class="p-4 print:py-2 print:px-1 whitespace-nowrap text-xs font-semibold text-gray-700">
                            {{ \Carbon\Carbon::parse($entry->voucher->voucher_date)->format('d-M-y') }}
                        </td>
                        <td class="p-4 print:py-2 print:px-1">
                            <div class="font-bold text-blue-700 print:text-slate-900 text-sm print:text-xs">
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
                                    <div class="mt-2 bg-slate-50 print:bg-transparent rounded border border-slate-100 print:border-slate-300 p-2 print:p-1">
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
                        <td class="p-4 print:py-2 print:px-1 text-xs text-gray-600 font-mono">
                            {{ $entry->voucher->voucher_type }}<br>
                            <span class="text-[9px] text-gray-400">#VCH-{{ $entry->voucher->id }}</span>
                        </td>
                        <td class="p-4 print:py-2 print:px-1 text-right font-mono font-bold text-emerald-600 print:text-slate-800">
                            {{ $entry->entry_type == 'Debit' ? number_format($entry->amount, 2) : '' }}
                        </td>
                        <td class="p-4 print:py-2 print:px-1 text-right font-mono font-bold text-rose-600 print:text-slate-800">
                            {{ $entry->entry_type == 'Credit' ? number_format($entry->amount, 2) : '' }}
                        </td>
                        <td class="p-4 print:py-2 print:px-1 text-right font-mono font-bold text-gray-900 bg-slate-50 print:bg-transparent">
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
                    <tr class="bg-slate-800 text-white font-bold print:bg-slate-100 print:text-slate-900 border-t-2 border-slate-800">
                        <td class="p-5 print:py-3 print:px-1 text-center tracking-widest uppercase text-xs sm:text-sm" colspan="3">Closing Balance</td>
                        <td class="p-5 print:py-3 print:px-1 text-right"></td>
                        <td class="p-5 print:py-3 print:px-1 text-right"></td>
                        <td class="p-5 print:py-3 print:px-1 text-right text-base sm:text-lg">
                            ৳ {{ number_format(abs($closingBalanceRaw), 2) }} 
                            <span class="text-xs text-gray-400 ml-1 print:text-slate-600">{{ $closingBalanceRaw >= 0 ? 'Dr' : 'Cr' }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        {{-- PRINT SIGNATURES --}}
        <div class="hidden print:flex justify-between items-end mt-16 pt-8 px-4 pb-4">
            <div class="text-center w-48 border-t border-slate-400 pt-2">
                <p class="text-xs font-bold text-slate-800">Prepared By</p>
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
        body { background-color: white !important; font-size: 11pt; margin: 0; padding: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
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
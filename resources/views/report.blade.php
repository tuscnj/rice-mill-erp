@extends('layouts.app')

@section('title', 'Financial Reports')

@section('content')
    <div class="max-w-6xl mx-auto pb-12">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Profit & Loss A/c</h1>
                <p class="text-gray-500">Standard T-Format Statement</p>
            </div>
            
            <form method="GET" action="/report" class="flex gap-3 items-end bg-white p-3 rounded-lg shadow-sm border border-gray-200 print:hidden">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">From Date</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="p-2 border border-gray-300 rounded focus:border-blue-500 outline-none text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">To Date</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="p-2 border border-gray-300 rounded focus:border-blue-500 outline-none text-sm">
                </div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded text-sm font-bold hover:bg-blue-700 transition">Filter</button>
                <a href="/report" class="text-gray-400 hover:text-gray-700 underline text-sm ml-1 mb-2">Reset</a>
            </form>

            <button onclick="window.print()" class="bg-slate-800 hover:bg-slate-900 text-white font-bold py-2 px-4 rounded-lg shadow transition flex items-center gap-2 print:hidden">
                🖨️ Print
            </button>
        </div>

        <div class="bg-white shadow-2xl overflow-hidden border-2 border-slate-800 print:shadow-none print:border-none">
            
            <div class="text-center py-4 border-b-2 border-slate-800 bg-slate-50">
                <p class="text-lg font-bold text-slate-800 uppercase tracking-wider">Profit & Loss Account</p>
                <p class="text-xs text-slate-500 mt-1">For the period: <span class="font-bold text-slate-700">{{ $displayStartDate }} to {{ $displayEndDate }}</span></p>
            </div>

            <div class="grid grid-cols-2 text-sm border-b-2 border-slate-800 bg-slate-100 font-bold text-slate-800">
                <div class="p-3 border-r-2 border-slate-800">Particulars</div>
                <div class="p-3">Particulars</div>
            </div>

            <div class="grid grid-cols-2 text-sm border-b-4 border-double border-slate-800 min-h-[150px]">
                
                <div class="flex flex-col border-r-2 border-slate-800 bg-white">
                    <div class="p-4 space-y-4 flex-1">
                        <div>
                            <div class="flex justify-between font-bold text-slate-800 mb-1">
                                <span>Opening Stock</span>
                                <span>{{ number_format($totalOpeningStock, 2) }}</span>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between font-bold text-slate-800 mb-1">
                                <span>Purchase Accounts</span>
                                <span>{{ number_format($totalPurchases, 2) }}</span>
                            </div>
                            @foreach($purchases as $p)
                                @if($p->total > 0)
                                <div class="flex justify-between text-slate-600 pl-4 text-xs">
                                    <span>{{ $p->name }}</span>
                                    <span>{{ number_format($p->total, 2) }}</span>
                                </div>
                                @endif
                            @endforeach
                        </div>

                        @if($grossProfit > 0)
                        <div class="flex justify-between font-bold text-blue-700 pt-4">
                            <span>Gross Profit c/o</span>
                            <span>{{ number_format($grossProfit, 2) }}</span>
                        </div>
                        @endif
                    </div>
                    <div class="flex justify-between font-extrabold text-slate-900 bg-slate-50 p-3 border-t-2 border-slate-800">
                        <span>Total</span>
                        <span>{{ number_format($tradingTotal, 2) }}</span>
                    </div>
                </div>

                <div class="flex flex-col bg-white">
                    <div class="p-4 space-y-4 flex-1">
                        <div>
                            <div class="flex justify-between font-bold text-slate-800 mb-1">
                                <span>Sales Accounts</span>
                                <span>{{ number_format($totalSales, 2) }}</span>
                            </div>
                            @foreach($sales as $s)
                                @if($s->total > 0)
                                <div class="flex justify-between text-slate-600 pl-4 text-xs">
                                    <span>{{ $s->name }}</span>
                                    <span>{{ number_format($s->total, 2) }}</span>
                                </div>
                                @endif
                            @endforeach
                        </div>

                        <div>
                            <div class="flex justify-between font-bold text-slate-800 mb-1 mt-2">
                                <span>Closing Stock</span>
                                <span>{{ number_format($totalClosingStock, 2) }}</span>
                            </div>
                        </div>

                        @if($grossLoss > 0)
                        <div class="flex justify-between font-bold text-red-600 pt-4">
                            <span>Gross Loss c/o</span>
                            <span>{{ number_format($grossLoss, 2) }}</span>
                        </div>
                        @endif
                    </div>
                    <div class="flex justify-between font-extrabold text-slate-900 bg-slate-50 p-3 border-t-2 border-slate-800">
                        <span>Total</span>
                        <span>{{ number_format($tradingTotal, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 text-sm min-h-[300px]">
                
                <div class="flex flex-col border-r-2 border-slate-800 bg-white">
                    <div class="p-4 space-y-4 flex-1">
                        @if($grossLoss > 0)
                        <div class="flex justify-between font-bold text-red-600 pb-2 border-b border-gray-100">
                            <span>Gross Loss b/f</span>
                            <span>{{ number_format($grossLoss, 2) }}</span>
                        </div>
                        @endif

                        <div>
                            <div class="flex justify-between font-bold text-slate-800 mb-1">
                                <span>Indirect Expenses</span>
                                <span>{{ number_format($totalIndirectExpenses, 2) }}</span>
                            </div>
                            @foreach($indirectExpenses as $expense)
                                <div class="flex justify-between text-slate-600 pl-4 text-xs mb-1">
                                    <span>{{ $expense->name }}</span>
                                    <span>{{ number_format($expense->total, 2) }}</span>
                                </div>
                            @endforeach
                        </div>

                        @if($netProfit > 0)
                        <div class="flex justify-between font-extrabold text-green-700 pt-8 text-base">
                            <span>Nett Profit</span>
                            <span>{{ number_format($netProfit, 2) }}</span>
                        </div>
                        @endif
                    </div>
                    <div class="flex justify-between font-extrabold text-slate-900 bg-slate-100 p-3 border-t-4 border-slate-800">
                        <span>Total</span>
                        <span>{{ number_format($plTotal, 2) }}</span>
                    </div>
                </div>

                <div class="flex flex-col bg-white">
                    <div class="p-4 space-y-4 flex-1">
                        @if($grossProfit > 0)
                        <div class="flex justify-between font-bold text-blue-700 pb-2 border-b border-gray-100">
                            <span>Gross Profit b/f</span>
                            <span>{{ number_format($grossProfit, 2) }}</span>
                        </div>
                        @endif

                        @if($totalIndirectIncomes > 0)
                        <div>
                            <div class="flex justify-between font-bold text-slate-800 mb-1">
                                <span>Indirect Incomes</span>
                                <span>{{ number_format($totalIndirectIncomes, 2) }}</span>
                            </div>
                            @foreach($indirectIncomes as $income)
                                <div class="flex justify-between text-slate-600 pl-4 text-xs mb-1">
                                    <span>{{ $income->name }}</span>
                                    <span>{{ number_format($income->total, 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                        @endif

                        @if($netLoss > 0)
                        <div class="flex justify-between font-extrabold text-red-700 pt-8 text-base">
                            <span>Nett Loss</span>
                            <span>{{ number_format($netLoss, 2) }}</span>
                        </div>
                        @endif
                    </div>
                    <div class="flex justify-between font-extrabold text-slate-900 bg-slate-100 p-3 border-t-4 border-slate-800">
                        <span>Total</span>
                        <span>{{ number_format($plTotal, 2) }}</span>
                    </div>
                </div>

            </div>

        </div>
    </div>
@endsection
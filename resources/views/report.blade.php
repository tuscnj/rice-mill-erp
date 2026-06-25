@extends('layouts.app')
@section('title', 'Master Reports')
@section('content')

    <div class="max-w-7xl mx-auto pb-12 mt-4 sm:mt-6">
        
        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-6 gap-6">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 tracking-tight">Master Reports Hub</h1>
                <p class="text-gray-500 text-sm sm:text-base mt-1">Generate dynamic statements, lists, and yields</p>
            </div>
            
            <div class="w-full xl:w-auto flex flex-col sm:flex-row gap-3 print:hidden">
                <form method="GET" action="/report" class="flex flex-col sm:flex-row gap-3 items-start sm:items-end bg-white p-4 rounded-xl shadow-sm border border-gray-100 w-full sm:w-auto">
                    <div class="w-full sm:w-auto">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Report Type</label>
                        <select name="report_type" class="w-full sm:w-auto px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white font-bold text-gray-700">
                            <option value="Profit_Loss" {{ $reportType == 'Profit_Loss' ? 'selected' : '' }}>1. Profit & Loss</option>
                            <option value="Sales" {{ $reportType == 'Sales' ? 'selected' : '' }}>2. Sales</option>
                            <option value="Production" {{ $reportType == 'Production' ? 'selected' : '' }}>3. Production (Yield)</option>
                            <option value="Purchase" {{ $reportType == 'Purchase' ? 'selected' : '' }}>4. Purchase</option>
                            <option value="Sales_Return" {{ $reportType == 'Sales_Return' ? 'selected' : '' }}>5. Sales Return</option>
                            <option value="Purchase_Return" {{ $reportType == 'Purchase_Return' ? 'selected' : '' }}>6. Purchase Return</option>
                            <option value="Expense" {{ $reportType == 'Expense' ? 'selected' : '' }}>7. Expenses</option>
                            <option value="Other_Income" {{ $reportType == 'Other_Income' ? 'selected' : '' }}>8. Other Incomes</option>
                            <option value="Stock_Adjustment" {{ $reportType == 'Stock_Adjustment' ? 'selected' : '' }}>9. Stock Adjustment</option>
                            <option value="Balance_Transfer" {{ $reportType == 'Balance_Transfer' ? 'selected' : '' }}>10. Balance Transfer</option>
                            <option value="Receipt" {{ $reportType == 'Receipt' ? 'selected' : '' }}>11. Received Money</option>
                            <option value="Payment" {{ $reportType == 'Payment' ? 'selected' : '' }}>12. Payment</option>
                        </select>
                    </div>
                    <div class="w-full sm:w-auto">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">From</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="w-full sm:w-auto px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 bg-gray-50 text-sm font-medium text-gray-700">
                    </div>
                    <div class="w-full sm:w-auto">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">To</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="w-full sm:w-auto px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 bg-gray-50 text-sm font-medium text-gray-700">
                    </div>
                    <div class="flex gap-2 w-full sm:w-auto mt-2 sm:mt-0">
                        <button type="submit" class="flex-1 sm:flex-none bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-bold shadow-sm transition">Generate</button>
                    </div>
                </form>

                <button onclick="window.print()" class="w-full sm:w-auto bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 px-6 rounded-xl shadow-md transition-all flex justify-center items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Print
                </button>
            </div>
        </div>

        @if($reportType === 'Profit_Loss')
            <div class="bg-white shadow-xl sm:rounded-xl overflow-hidden border border-slate-300 print:shadow-none print:border-none print:rounded-none">
                <div class="text-center py-6 border-b-2 border-slate-800 bg-slate-50/50">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-slate-800 uppercase tracking-widest">Profit & Loss Account</h2>
                    <p class="text-sm text-slate-500 mt-1">For the period: <span class="font-bold text-slate-700">{{ $displayStartDate }} to {{ $displayEndDate }}</span></p>
                </div>

                <div class="hidden md:grid grid-cols-2 text-sm border-b-2 border-slate-800 bg-slate-100 font-bold text-slate-800 uppercase tracking-wider">
                    <div class="p-3 border-r-2 border-slate-800 text-center">Dr. (Particulars)</div>
                    <div class="p-3 text-center">Cr. (Particulars)</div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 text-sm sm:text-base border-b-4 border-double border-slate-800 min-h-[150px]">
                    <div class="flex flex-col border-b-2 md:border-b-0 md:border-r-2 border-slate-800 bg-white">
                        <div class="md:hidden p-2 bg-slate-100 border-b border-slate-200 font-bold text-slate-700 text-xs uppercase tracking-wider text-center">Dr. (Trading)</div>
                        <div class="p-4 sm:p-5 space-y-4 flex-1">
                            <div>
                                <div class="flex justify-between font-bold text-slate-800 mb-1">
                                    <span>Opening Stock</span>
                                    <span>{{ number_format($totalOpeningStock, 2) }}</span>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between font-bold text-slate-800 mb-1">
                                    <span>Direct Expenses (Purchases)</span>
                                    <span>{{ number_format($totalPurchases, 2) }}</span>
                                </div>
                                @foreach($purchases as $p)
                                    @if($p->total > 0)
                                    <div class="flex justify-between text-slate-600 pl-4 text-xs sm:text-sm mt-1">
                                        <span>{{ $p->name }}</span>
                                        <span>{{ number_format($p->total, 2) }}</span>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                            @if($grossProfit > 0)
                            <div class="flex justify-between font-extrabold text-blue-700 pt-4 mt-2 border-t border-dashed border-gray-200">
                                <span>Gross Profit c/o</span>
                                <span>{{ number_format($grossProfit, 2) }}</span>
                            </div>
                            @endif
                        </div>
                        <div class="flex justify-between font-extrabold text-slate-900 bg-slate-50 p-4 border-t-2 border-slate-800 text-base sm:text-lg">
                            <span>Total</span>
                            <span>{{ number_format($tradingTotal, 2) }}</span>
                        </div>
                    </div>

                    <div class="flex flex-col bg-white">
                        <div class="md:hidden p-2 bg-slate-100 border-b border-slate-200 font-bold text-slate-700 text-xs uppercase tracking-wider text-center">Cr. (Trading)</div>
                        <div class="p-4 sm:p-5 space-y-4 flex-1">
                            <div>
                                <div class="flex justify-between font-bold text-slate-800 mb-1">
                                    <span>Direct Incomes (Sales)</span>
                                    <span>{{ number_format($totalSales, 2) }}</span>
                                </div>
                                @foreach($sales as $s)
                                    @if($s->total > 0)
                                    <div class="flex justify-between text-slate-600 pl-4 text-xs sm:text-sm mt-1">
                                        <span>{{ $s->name }}</span>
                                        <span>{{ number_format($s->total, 2) }}</span>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                            <div>
                                <div class="flex justify-between font-bold text-slate-800 mb-1 mt-4">
                                    <span>Closing Stock</span>
                                    <span>{{ number_format($totalClosingStock, 2) }}</span>
                                </div>
                            </div>
                            @if($grossLoss > 0)
                            <div class="flex justify-between font-extrabold text-red-600 pt-4 mt-2 border-t border-dashed border-gray-200">
                                <span>Gross Loss c/o</span>
                                <span>{{ number_format($grossLoss, 2) }}</span>
                            </div>
                            @endif
                        </div>
                        <div class="flex justify-between font-extrabold text-slate-900 bg-slate-50 p-4 border-t-2 border-slate-800 text-base sm:text-lg">
                            <span>Total</span>
                            <span>{{ number_format($tradingTotal, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 text-sm sm:text-base min-h-[300px]">
                    <div class="flex flex-col border-b-2 md:border-b-0 md:border-r-2 border-slate-800 bg-white">
                        <div class="md:hidden p-2 bg-slate-100 border-b border-slate-200 font-bold text-slate-700 text-xs uppercase tracking-wider text-center">Dr. (P&L)</div>
                        <div class="p-4 sm:p-5 space-y-4 flex-1">
                            @if($grossLoss > 0)
                            <div class="flex justify-between font-extrabold text-red-600 pb-3 border-b border-gray-200 mb-3">
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
                                    <div class="flex justify-between text-slate-600 pl-4 text-xs sm:text-sm mt-1">
                                        <span>{{ $expense->name }}</span>
                                        <span>{{ number_format($expense->total, 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                            @if($netProfit > 0)
                            <div class="flex justify-between font-black text-emerald-600 pt-6 mt-4 border-t border-dashed border-gray-200 text-lg sm:text-xl">
                                <span>Nett Profit</span>
                                <span>{{ number_format($netProfit, 2) }}</span>
                            </div>
                            @endif
                        </div>
                        <div class="flex justify-between font-extrabold text-slate-900 bg-slate-100 p-4 border-t-4 border-slate-800 text-base sm:text-lg">
                            <span>Total</span>
                            <span>{{ number_format($plTotal, 2) }}</span>
                        </div>
                    </div>

                    <div class="flex flex-col bg-white">
                        <div class="md:hidden p-2 bg-slate-100 border-b border-slate-200 font-bold text-slate-700 text-xs uppercase tracking-wider text-center">Cr. (P&L)</div>
                        <div class="p-4 sm:p-5 space-y-4 flex-1">
                            @if($grossProfit > 0)
                            <div class="flex justify-between font-extrabold text-blue-700 pb-3 border-b border-gray-200 mb-3">
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
                                    <div class="flex justify-between text-slate-600 pl-4 text-xs sm:text-sm mt-1">
                                        <span>{{ $income->name }}</span>
                                        <span>{{ number_format($income->total, 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                            @endif
                            @if($netLoss > 0)
                            <div class="flex justify-between font-black text-red-600 pt-6 mt-4 border-t border-dashed border-gray-200 text-lg sm:text-xl">
                                <span>Nett Loss</span>
                                <span>{{ number_format($netLoss, 2) }}</span>
                            </div>
                            @endif
                        </div>
                        <div class="flex justify-between font-extrabold text-slate-900 bg-slate-100 p-4 border-t-4 border-slate-800 text-base sm:text-lg">
                            <span>Total</span>
                            <span>{{ number_format($plTotal, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

        @elseif($reportType === 'Production')
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border-l-4 border-amber-500">
                    <p class="text-sm font-bold text-gray-500 uppercase">Paddy Crushed (Out)</p>
                    <p class="text-2xl font-black text-amber-600 mt-1">{{ number_format($productionStats['raw'], 2) }} KG</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border-l-4 border-emerald-500">
                    <p class="text-sm font-bold text-gray-500 uppercase">Rice Produced (In)</p>
                    <p class="text-2xl font-black text-emerald-600 mt-1">{{ number_format($productionStats['rice'], 2) }} KG</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border-l-4 border-gray-400">
                    <p class="text-sm font-bold text-gray-500 uppercase">Byproducts (In)</p>
                    <p class="text-2xl font-black text-gray-700 mt-1">{{ number_format($productionStats['byproduct'], 2) }} KG</p>
                </div>
                <div class="bg-gradient-to-br from-green-500 to-green-600 p-6 rounded-2xl shadow-sm text-white text-center">
                    <p class="text-sm font-bold text-green-100 uppercase tracking-widest">Yield Ratio (Paddy to Rice)</p>
                    <p class="text-4xl font-black mt-1">{{ number_format($productionStats['yield'], 2) }}%</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800">Production Log</h3>
                    <span class="text-sm text-gray-500">{{ $displayStartDate }} to {{ $displayEndDate }}</span>
                </div>
                <table class="w-full text-left text-sm">
                    <thead class="bg-white text-gray-500 border-b border-gray-200">
                        <tr>
                            <th class="p-4 font-bold">Date / Ref</th>
                            <th class="p-4 font-bold">Raw Material Used</th>
                            <th class="p-4 font-bold">Finished Goods Gained</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($vouchers as $v)
                        <tr class="hover:bg-gray-50">
                            <td class="p-4">
                                <p class="font-bold text-gray-800">{{ \Carbon\Carbon::parse($v->voucher_date)->format('d M Y') }}</p>
                                <p class="text-xs text-gray-500 font-mono mt-1">{{ $v->reference_number ?: '#VCH-'.$v->id }}</p>
                            </td>
                            <td class="p-4">
                                @foreach($v->inventoryMovements as $m)
                                    @if($m->movement_type == 'Out' && $m->item && $m->item->category == 'Raw Material')
                                        <div class="text-amber-600 font-bold mb-1">- {{ number_format($m->quantity, 2) }} KG <span class="text-xs font-normal text-gray-500">({{ $m->item->name }})</span></div>
                                    @endif
                                @endforeach
                            </td>
                            <td class="p-4">
                                @foreach($v->inventoryMovements as $m)
                                    @if($m->movement_type == 'In' && $m->item && $m->item->category == 'Finished Goods')
                                        <div class="text-emerald-600 font-bold mb-1">+ {{ number_format($m->quantity, 2) }} KG <span class="text-xs font-normal text-gray-500">({{ $m->item->name }})</span></div>
                                    @endif
                                @endforeach
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="p-8 text-center text-gray-400 italic">No production records found for this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        @else
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200 bg-gray-50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                    <div>
                        <h3 class="font-extrabold text-gray-800 text-lg uppercase tracking-wider">{{ str_replace('_', ' ', $reportType) }} Report</h3>
                        <p class="text-sm text-gray-500">{{ $displayStartDate }} to {{ $displayEndDate }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">Total Period Value</p>
                        <p class="text-2xl font-black text-blue-600">৳{{ number_format($totalAmount, 2) }}</p>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-white text-gray-500 border-b border-gray-200">
                            <tr>
                                <th class="p-4 font-bold">Date</th>
                                <th class="p-4 font-bold">Voucher / Ref</th>
                                <th class="p-4 font-bold w-1/2">Notes / Details</th>
                                <th class="p-4 font-bold text-right">Amount (৳)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($vouchers as $v)
                            <tr class="hover:bg-gray-50">
                                <td class="p-4 font-bold text-gray-800">{{ \Carbon\Carbon::parse($v->voucher_date)->format('d M Y') }}</td>
                                <td class="p-4 font-mono text-xs text-gray-500">
                                    <a href="/edit-transaction/{{ $v->id }}" class="text-blue-600 hover:underline">#VCH-{{ $v->id }}</a><br>
                                    {{ $v->reference_number }}
                                </td>
                                <td class="p-4 text-gray-600 truncate max-w-xs" title="{{ $v->notes }}">{{ $v->notes ?: '-' }}</td>
                                <td class="p-4 text-right font-mono font-bold text-gray-800">{{ number_format($v->display_amount, 2) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="p-8 text-center text-gray-400 italic">No records found for this period.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                            <tr>
                                <td colspan="3" class="p-4 text-right font-extrabold text-gray-600 uppercase tracking-widest">Grand Total</td>
                                <td class="p-4 text-right font-black text-blue-600 text-lg">৳{{ number_format($totalAmount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif

    </div>
@endsection
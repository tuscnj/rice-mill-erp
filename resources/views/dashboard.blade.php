@extends('layouts.app')

@section('title', 'Live Business Overview')

@section('content')
    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm font-semibold text-gray-500">Cash & Bank</p>
                <h3 class="mt-2 text-3xl font-bold text-blue-600">৳ {{ number_format($cashBalance, 2) }}</h3>
                <p class="mt-1 text-xs text-gray-400">Available liquidity</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm font-semibold text-gray-500">Receivables</p>
                <h3 class="mt-2 text-3xl font-bold text-green-600">৳ {{ number_format($receivables, 2) }}</h3>
                <p class="mt-1 text-xs text-gray-400">Customers owe you</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm font-semibold text-gray-500">Payables</p>
                <h3 class="mt-2 text-3xl font-bold text-red-600">৳ {{ number_format($payables, 2) }}</h3>
                <p class="mt-1 text-xs text-gray-400">You owe suppliers</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm font-semibold text-gray-500">Inventory Items</p>
                <h3 class="mt-2 text-3xl font-bold text-slate-800">{{ $totalItems }}</h3>
                <p class="mt-1 text-xs text-gray-400">Total item variants</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm font-semibold text-gray-500">Today Purchases</p>
                <h3 class="mt-2 text-2xl font-bold text-blue-700">৳ {{ number_format($todayPurchases, 2) }}</h3>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm font-semibold text-gray-500">Today Sales</p>
                <h3 class="mt-2 text-2xl font-bold text-green-700">৳ {{ number_format($todaySales, 2) }}</h3>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm font-semibold text-gray-500">Today Expenses</p>
                <h3 class="mt-2 text-2xl font-bold text-rose-700">৳ {{ number_format($todayExpenses, 2) }}</h3>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm font-semibold text-gray-500">Today Other Income</p>
                <h3 class="mt-2 text-2xl font-bold text-emerald-700">৳ {{ number_format($todayOtherIncome, 2) }}</h3>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm font-semibold text-gray-500">Monthly Purchases</p>
                <h3 class="mt-2 text-2xl font-bold text-blue-700">৳ {{ number_format($monthlyPurchases, 2) }}</h3>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm font-semibold text-gray-500">Monthly Sales</p>
                <h3 class="mt-2 text-2xl font-bold text-green-700">৳ {{ number_format($monthlySales, 2) }}</h3>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm font-semibold text-gray-500">Stock Value</p>
                <h3 class="mt-2 text-2xl font-bold text-slate-800">৳ {{ number_format($stockValue, 2) }}</h3>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm font-semibold text-gray-500">Net Position</p>
                <h3 class="mt-2 text-2xl font-bold {{ ($cashBalance - $payables + $receivables) >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                    ৳ {{ number_format($cashBalance - $payables + $receivables, 2) }}
                </h3>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 xl:col-span-2">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Recent Transactions</h3>
                    <a href="/transactions" class="text-sm font-semibold text-blue-600 hover:underline">View all</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b text-gray-500">
                                <th class="pb-2 text-left">Date</th>
                                <th class="pb-2 text-left">Type</th>
                                <th class="pb-2 text-left">Reference</th>
                                <th class="pb-2 text-left">Narration</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentVouchers as $voucher)
                                <tr class="border-b last:border-0">
                                    <td class="py-2 text-gray-700">{{ \Carbon\Carbon::parse($voucher->voucher_date)->format('d M Y') }}</td>
                                    <td class="py-2">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $voucher->voucher_type == 'Purchase' ? 'bg-blue-100 text-blue-700' : ($voucher->voucher_type == 'Sales' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-700') }}">
                                            {{ $voucher->voucher_type }}
                                        </span>
                                    </td>
                                    <td class="py-2 text-gray-600">{{ $voucher->reference_number ?? '-' }}</td>
                                    <td class="py-2 text-gray-600">{{ $voucher->notes ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Top Customers</h3>
                <div class="space-y-3">
                    @foreach($topCustomers as $customer)
                        <div class="flex justify-between items-center border-b pb-2 last:border-0 last:pb-0">
                            <span class="text-sm text-gray-700">{{ $customer->name }}</span>
                            <span class="text-sm font-semibold text-green-700">৳ {{ number_format($customer->balance, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Top Suppliers</h3>
                <div class="space-y-3">
                    @foreach($topSuppliers as $supplier)
                        <div class="flex justify-between items-center border-b pb-2 last:border-0 last:pb-0">
                            <span class="text-sm text-gray-700">{{ $supplier->name }}</span>
                            <span class="text-sm font-semibold text-red-700">৳ {{ number_format($supplier->balance, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="/purchase" class="block bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold rounded-xl p-3">📥 New Purchase</a>
                    <a href="/sales" class="block bg-purple-50 hover:bg-purple-100 text-purple-700 font-semibold rounded-xl p-3">📤 New Sales</a>
                    <a href="/payment" class="block bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold rounded-xl p-3">💸 Payment</a>
                    <a href="/receipt" class="block bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold rounded-xl p-3">💰 Receipt</a>
                    <a href="/report" class="block bg-slate-50 hover:bg-slate-100 text-slate-700 font-semibold rounded-xl p-3">📈 P&L Report</a>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800">Low Stock Alert</h3>
                <a href="/stock" class="text-sm font-semibold text-blue-600 hover:underline">Inventory</a>
            </div>
            @if($lowStockItems->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                    @foreach($lowStockItems as $item)
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-3">
                            <p class="font-semibold text-gray-800">{{ $item->name }}</p>
                            <p class="text-sm text-amber-700">{{ number_format($item->current_stock, 2) }} {{ $item->unit }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500">No low stock items at the moment.</p>
            @endif
        </div>
    </div>
@endsection
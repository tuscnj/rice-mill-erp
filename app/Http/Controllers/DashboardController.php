<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Item;
use App\Models\Voucher;
use App\Models\VoucherEntry;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->startOfDay();
        $endOfToday = now()->endOfDay();
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $totalItems = Item::count();
        $lowStockItems = Item::where('current_stock', '<=', 10)->orderBy('current_stock', 'asc')->get();

        $cashBalance = Account::where('group_type', 'Cash')->sum('balance');
        $receivables = Account::where('group_type', 'Sundry Debtors')->sum('balance');
        $payables = Account::where('group_type', 'Sundry Creditors')->sum('balance');

        // FIXED: Now calculates exactly like the Stock and P&L pages using the true base rate
        $stockValue = Item::all()->sum(function ($item) {
            return $item->current_stock * $item->purchase_rate;
        });

        $todayPurchases = VoucherEntry::whereHas('voucher', function ($query) use ($today, $endOfToday) {
            $query->where('voucher_type', 'Purchase')
                ->whereBetween('voucher_date', [$today, $endOfToday]);
        })
            ->where('account_id', 1)
            ->where('entry_type', 'Debit')
            ->sum('amount');

        $todaySales = VoucherEntry::whereHas('voucher', function ($query) use ($today, $endOfToday) {
            $query->where('voucher_type', 'Sales')
                ->whereBetween('voucher_date', [$today, $endOfToday]);
        })
            ->where('account_id', 5)
            ->where('entry_type', 'Credit')
            ->sum('amount');

        $todayExpenses = VoucherEntry::whereHas('voucher', function ($query) use ($today, $endOfToday) {
            $query->where('voucher_type', 'Expense')
                ->whereBetween('voucher_date', [$today, $endOfToday]);
        })
            ->where('entry_type', 'Debit')
            ->sum('amount');

        $todayOtherIncome = VoucherEntry::whereHas('voucher', function ($query) use ($today, $endOfToday) {
            $query->where('voucher_type', 'Other Income')
                ->whereBetween('voucher_date', [$today, $endOfToday]);
        })
            ->where('entry_type', 'Credit')
            ->sum('amount');

        $monthlyPurchases = VoucherEntry::whereHas('voucher', function ($query) use ($monthStart, $monthEnd) {
            $query->where('voucher_type', 'Purchase')
                ->whereBetween('voucher_date', [$monthStart, $monthEnd]);
        })
            ->where('account_id', 1)
            ->where('entry_type', 'Debit')
            ->sum('amount');

        $monthlySales = VoucherEntry::whereHas('voucher', function ($query) use ($monthStart, $monthEnd) {
            $query->where('voucher_type', 'Sales')
                ->whereBetween('voucher_date', [$monthStart, $monthEnd]);
        })
            ->where('account_id', 5)
            ->where('entry_type', 'Credit')
            ->sum('amount');

        $topCustomers = Account::where('group_type', 'Sundry Debtors')
            ->orderByDesc('balance')
            ->limit(5)
            ->get();

        $topSuppliers = Account::where('group_type', 'Sundry Creditors')
            ->orderByDesc('balance')
            ->limit(5)
            ->get();

        $recentVouchers = Voucher::orderBy('voucher_date', 'desc')
            ->limit(8)
            ->get();

        return view('dashboard', compact(
            'totalItems',
            'lowStockItems',
            'cashBalance',
            'receivables',
            'payables',
            'stockValue',
            'todayPurchases',
            'todaySales',
            'todayExpenses',
            'todayOtherIncome',
            'monthlyPurchases',
            'monthlySales',
            'topCustomers',
            'topSuppliers',
            'recentVouchers'
        ));
    }
}
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

        $stockValue = Item::all()->sum(function ($item) {
            return $item->current_stock * $item->purchase_rate;
        });

        // --- DYNAMIC PURCHASE CALCULATIONS (Using Group Type) ---
        $purchaseAccountIds = Account::where('group_type', 'Direct Expenses')->pluck('id');
        
        $todayPurchases = VoucherEntry::whereHas('voucher', function ($query) use ($today, $endOfToday) {
            $query->where('voucher_type', 'Purchase')->whereBetween('voucher_date', [$today, $endOfToday]);
        })->whereIn('account_id', $purchaseAccountIds)->where('entry_type', 'Debit')->sum('amount');

        $monthlyPurchases = VoucherEntry::whereHas('voucher', function ($query) use ($monthStart, $monthEnd) {
            $query->where('voucher_type', 'Purchase')->whereBetween('voucher_date', [$monthStart, $monthEnd]);
        })->whereIn('account_id', $purchaseAccountIds)->where('entry_type', 'Debit')->sum('amount');

        // --- DYNAMIC SALES CALCULATIONS (Using Group Type) ---
        $salesAccountIds = Account::where('group_type', 'Direct Incomes')->pluck('id');

        $todaySales = VoucherEntry::whereHas('voucher', function ($query) use ($today, $endOfToday) {
            $query->where('voucher_type', 'Sales')->whereBetween('voucher_date', [$today, $endOfToday]);
        })->whereIn('account_id', $salesAccountIds)->where('entry_type', 'Credit')->sum('amount');

        $monthlySales = VoucherEntry::whereHas('voucher', function ($query) use ($monthStart, $monthEnd) {
            $query->where('voucher_type', 'Sales')->whereBetween('voucher_date', [$monthStart, $monthEnd]);
        })->whereIn('account_id', $salesAccountIds)->where('entry_type', 'Credit')->sum('amount');

        // --- EXPENSES & OTHER INCOME ---
        $todayExpenses = VoucherEntry::whereHas('voucher', function ($query) use ($today, $endOfToday) {
            $query->where('voucher_type', 'Expense')->whereBetween('voucher_date', [$today, $endOfToday]);
        })->where('entry_type', 'Debit')->sum('amount');

        $todayOtherIncome = VoucherEntry::whereHas('voucher', function ($query) use ($today, $endOfToday) {
            $query->where('voucher_type', 'Other Income')->whereBetween('voucher_date', [$today, $endOfToday]);
        })->where('entry_type', 'Credit')->sum('amount');

        $topCustomers = Account::where('group_type', 'Sundry Debtors')->orderByDesc('balance')->limit(5)->get();
        $topSuppliers = Account::where('group_type', 'Sundry Creditors')->orderByDesc('balance')->limit(5)->get();

        $recentVouchers = Voucher::orderBy('voucher_date', 'desc')->limit(8)->get();

        return view('dashboard', compact(
            'totalItems', 'lowStockItems', 'cashBalance', 'receivables', 'payables', 'stockValue',
            'todayPurchases', 'todaySales', 'todayExpenses', 'todayOtherIncome',
            'monthlyPurchases', 'monthlySales', 'topCustomers', 'topSuppliers', 'recentVouchers'
        ));
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Voucher;
use App\Models\VoucherEntry;
use App\Models\InventoryMovement;
use App\Models\Account;
use App\Models\Item;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function profitAndLoss(Request $request)
    {
        // 1. Handle Date Filtering
        $startDate = $request->input('start_date', '2020-01-01'); // Default far past
        $endDate = $request->input('end_date', date('Y-m-d')); // Default today

        // Find all voucher IDs that fall within the selected dates for Financial calculations
        $voucherIds = Voucher::whereDate('voucher_date', '>=', $startDate)
                             ->whereDate('voucher_date', '<=', $endDate)
                             ->pluck('id');

        // 2. Helper function to get exact balances FOR SELECTED DATES (Financials)
        $getBalances = function($groupType, $normalBalance) use ($voucherIds) {
            $accounts = Account::where('group_type', $groupType)->get();
            foreach($accounts as $acc) {
                $debit = VoucherEntry::whereIn('voucher_id', $voucherIds)
                                     ->where('account_id', $acc->id)
                                     ->where('entry_type', 'Debit')
                                     ->sum('amount');
                $credit = VoucherEntry::whereIn('voucher_id', $voucherIds)
                                      ->where('account_id', $acc->id)
                                      ->where('entry_type', 'Credit')
                                      ->sum('amount');
                $acc->total = $normalBalance == 'Debit' ? ($debit - $credit) : ($credit - $debit);
            }
            return $accounts->filter(function($acc) { return $acc->total != 0; });
        };

        // --- PART 1: TRADING ACCOUNT (Top Section) ---
        
        $items = Item::all();
        $totalOpeningStock = 0;
        $totalClosingStock = 0;

        // Calculate Historical Inventory Valuations
        foreach ($items as $item) {
            
            // A. Calculate Opening Stock (Inventory state strictly BEFORE start_date)
            $inBefore = InventoryMovement::where('item_id', $item->id)
                ->where('movement_type', 'In')
                ->whereHas('voucher', function($q) use ($startDate) {
                    $q->whereDate('voucher_date', '<', $startDate);
                })->sum('quantity');

            $outBefore = InventoryMovement::where('item_id', $item->id)
                ->where('movement_type', 'Out')
                ->whereHas('voucher', function($q) use ($startDate) {
                    $q->whereDate('voucher_date', '<', $startDate);
                })->sum('quantity');

            $historicalOpeningQty = $item->opening_stock + $inBefore - $outBefore;
            $totalOpeningStock += ($historicalOpeningQty * $item->purchase_rate);


            // B. Calculate Closing Stock (Inventory state UP TO end_date)
            $inUpTo = InventoryMovement::where('item_id', $item->id)
                ->where('movement_type', 'In')
                ->whereHas('voucher', function($q) use ($endDate) {
                    $q->whereDate('voucher_date', '<=', $endDate);
                })->sum('quantity');

            $outUpTo = InventoryMovement::where('item_id', $item->id)
                ->where('movement_type', 'Out')
                ->whereHas('voucher', function($q) use ($endDate) {
                    $q->whereDate('voucher_date', '<=', $endDate);
                })->sum('quantity');

            $historicalClosingQty = $item->opening_stock + $inUpTo - $outUpTo;
            $totalClosingStock += ($historicalClosingQty * $item->purchase_rate);
        }

        // Calculate Purchases within date range
        $totalPurchases = VoucherEntry::whereIn('voucher_id', $voucherIds)
                                      ->where('account_id', 1)
                                      ->where('entry_type', 'Debit')
                                      ->sum('amount');
        $purchases = collect([(object)['name' => 'Purchase Account', 'total' => $totalPurchases]]);

        // Calculate Sales within date range
        $totalSales = VoucherEntry::whereIn('voucher_id', $voucherIds)
                                  ->where('account_id', 5)
                                  ->where('entry_type', 'Credit')
                                  ->sum('amount');
        $sales = collect([(object)['name' => 'Sales Account', 'total' => $totalSales]]);

        // Trading Account Balancing Math
        $leftTrading = $totalOpeningStock + $totalPurchases;
        $rightTrading = $totalSales + $totalClosingStock;

        $grossProfit = $rightTrading > $leftTrading ? $rightTrading - $leftTrading : 0;
        $grossLoss = $leftTrading > $rightTrading ? $leftTrading - $rightTrading : 0;
        
        $tradingTotal = max($leftTrading, $rightTrading);


        // --- PART 2: PROFIT & LOSS ACCOUNT (Bottom Section) ---

        $indirectExpenses = $getBalances('Indirect Expenses', 'Debit');
        $totalIndirectExpenses = $indirectExpenses->sum('total');

        $indirectIncomes = $getBalances('Indirect Incomes', 'Credit');
        $totalIndirectIncomes = $indirectIncomes->sum('total');

        $totalLeftPL = $grossLoss + $totalIndirectExpenses;
        $totalRightPL = $grossProfit + $totalIndirectIncomes;

        $netProfit = $totalRightPL > $totalLeftPL ? $totalRightPL - $totalLeftPL : 0;
        $netLoss = $totalLeftPL > $totalRightPL ? $totalLeftPL - $totalRightPL : 0;

        $plTotal = max($totalLeftPL, $totalRightPL);

        // Format dates nicely for the view
        $displayStartDate = Carbon::parse($startDate)->format('d-M-Y');
        $displayEndDate = Carbon::parse($endDate)->format('d-M-Y');

        return view('report', compact(
            'startDate', 'endDate', 'displayStartDate', 'displayEndDate',
            'totalOpeningStock', 'totalClosingStock',
            'purchases', 'totalPurchases',
            'sales', 'totalSales',
            'grossProfit', 'grossLoss', 'tradingTotal',
            'indirectExpenses', 'totalIndirectExpenses',
            'indirectIncomes', 'totalIndirectIncomes',
            'netProfit', 'netLoss', 'plTotal'
        ));
    }
}
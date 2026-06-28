<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Voucher;
use App\Models\VoucherEntry;
use App\Models\InventoryMovement;
use App\Models\Account;
use App\Models\Item;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf; // 🚨 Added for PDF Generation

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // 1. Handle Global Filters
        $reportType = $request->input('report_type', 'Profit_Loss');
        
        $defaultStart = $reportType === 'Profit_Loss' ? Carbon::now()->subYear()->format('Y-m-d') : Carbon::now()->startOfMonth()->format('Y-m-d');
        
        $startDate = $request->input('start_date', $defaultStart);
        $endDate = $request->input('end_date', date('Y-m-d')); 
        
        $displayStartDate = Carbon::parse($startDate)->format('d-M-Y');
        $displayEndDate = Carbon::parse($endDate)->format('d-M-Y');

        // ==========================================
        // REPORT 1: PROFIT & LOSS (Tally ERP Logic)
        // ==========================================
        if ($reportType === 'Profit_Loss') {
            $voucherIds = Voucher::whereDate('voucher_date', '>=', $startDate)->whereDate('voucher_date', '<=', $endDate)->pluck('id');

            $getBalances = function($groupType, $normalBalance) use ($voucherIds) {
                $accounts = Account::where('group_type', $groupType)->get();
                foreach($accounts as $acc) {
                    $debit = VoucherEntry::whereIn('voucher_id', $voucherIds)->where('account_id', $acc->id)->where('entry_type', 'Debit')->sum('amount');
                    $credit = VoucherEntry::whereIn('voucher_id', $voucherIds)->where('account_id', $acc->id)->where('entry_type', 'Credit')->sum('amount');
                    $acc->total = $normalBalance == 'Debit' ? ($debit - $credit) : ($credit - $debit);
                }
                return $accounts->filter(function($acc) { return $acc->total != 0; });
            };

            $items = Item::all();
            $totalOpeningStock = 0;
            $totalClosingStock = 0;

            foreach ($items as $item) {
                $rate = (float)($item->purchase_rate ?? 0);
                
                // Tally Logic: Opening stock is all movements BEFORE the start date
                $inBefore = InventoryMovement::where('item_id', $item->id)->where('movement_type', 'In')->whereHas('voucher', function($q) use ($startDate) { $q->whereDate('voucher_date', '<', $startDate); })->sum('quantity');
                $outBefore = InventoryMovement::where('item_id', $item->id)->where('movement_type', 'Out')->whereHas('voucher', function($q) use ($startDate) { $q->whereDate('voucher_date', '<', $startDate); })->sum('quantity');
                $totalOpeningStock += (((float)$item->opening_stock + $inBefore - $outBefore) * $rate);

                // Tally Logic: Closing stock is all movements UP TO the end date
                $inUpTo = InventoryMovement::where('item_id', $item->id)->where('movement_type', 'In')->whereHas('voucher', function($q) use ($endDate) { $q->whereDate('voucher_date', '<=', $endDate); })->sum('quantity');
                $outUpTo = InventoryMovement::where('item_id', $item->id)->where('movement_type', 'Out')->whereHas('voucher', function($q) use ($endDate) { $q->whereDate('voucher_date', '<=', $endDate); })->sum('quantity');
                $totalClosingStock += (((float)$item->opening_stock + $inUpTo - $outUpTo) * $rate);
            }

            $purchases = $getBalances('Direct Expenses', 'Debit');
            $totalPurchases = $purchases->sum('total');
            $sales = $getBalances('Direct Incomes', 'Credit');
            $totalSales = $sales->sum('total');

            $leftTrading = $totalOpeningStock + $totalPurchases;
            $rightTrading = $totalSales + $totalClosingStock;
            $grossProfit = $rightTrading > $leftTrading ? $rightTrading - $leftTrading : 0;
            $grossLoss = $leftTrading > $rightTrading ? $leftTrading - $rightTrading : 0;
            $tradingTotal = max($leftTrading, $rightTrading);

            $indirectExpenses = $getBalances('Indirect Expenses', 'Debit');
            $totalIndirectExpenses = $indirectExpenses->sum('total');
            $indirectIncomes = $getBalances('Indirect Incomes', 'Credit');
            $totalIndirectIncomes = $indirectIncomes->sum('total');

            $totalLeftPL = $grossLoss + $totalIndirectExpenses;
            $totalRightPL = $grossProfit + $totalIndirectIncomes;
            $netProfit = $totalRightPL > $totalLeftPL ? $totalRightPL - $totalLeftPL : 0;
            $netLoss = $totalLeftPL > $totalRightPL ? $totalLeftPL - $totalRightPL : 0;
            $plTotal = max($totalLeftPL, $totalRightPL);
        }
        // ==========================================
        // REPORT 2: TRANSACTION LISTS & RATIOS
        // ==========================================
        else {
            $dbType = str_replace('_', ' ', $reportType); 
            if ($reportType === 'Stock_Adjustment') $dbType = 'Journal'; 

            $vouchers = Voucher::with(['entries.account', 'inventoryMovements.item'])
                ->where('voucher_type', $dbType)
                ->whereDate('voucher_date', '>=', $startDate)
                ->whereDate('voucher_date', '<=', $endDate)
                ->orderBy('voucher_date', 'desc')
                ->get();

            $totalAmount = 0;
            $productionStats = ['raw' => 0, 'rice' => 0, 'byproduct' => 0, 'yield' => 0];

            if ($reportType === 'Production') {
                foreach($vouchers as $v) {
                    foreach($v->inventoryMovements as $m) {
                        if (!$m->item) continue;
                        if ($m->movement_type == 'Out' && $m->item->category == 'Raw Material') $productionStats['raw'] += $m->quantity;
                        if ($m->movement_type == 'In' && $m->item->category == 'Finished Goods') $productionStats['rice'] += $m->quantity;
                        if ($m->movement_type == 'In' && $m->item->category == 'Byproduct') $productionStats['byproduct'] += $m->quantity;
                    }
                }
                $productionStats['yield'] = $productionStats['raw'] > 0 ? ($productionStats['rice'] / $productionStats['raw']) * 100 : 0;
            } else {
                foreach($vouchers as $v) {
                    $vAmount = $v->entries->where('entry_type', 'Debit')->sum('amount');
                    $v->display_amount = $vAmount;
                    $totalAmount += $vAmount;
                }
            }
        }

        // 🚨 NEW: EXPORT TO PDF LOGIC
        $setting = \App\Models\Setting::firstOrCreate(['id' => 1]);
        if ($request->query('export') === 'pdf') {
            $pdf = Pdf::loadView('report-pdf', get_defined_vars());
            return $pdf->download($reportType . '-Report-' . $displayStartDate . '-to-' . $displayEndDate . '.pdf');
        }

        return view('report', get_defined_vars());
    }
}
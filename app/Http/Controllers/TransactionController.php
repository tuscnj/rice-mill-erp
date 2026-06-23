<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Voucher;
use App\Models\VoucherEntry;
use App\Models\InventoryMovement;
use App\Models\Item;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class TransactionController extends Controller
{
    // Show all transactions (The Detailed Daybook)
    public function index(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $vouchers = Voucher::whereDate('voucher_date', '>=', $startDate)
            ->whereDate('voucher_date', '<=', $endDate)
            ->orderBy('voucher_date', 'desc')
            ->get();

        $voucherIds = $vouchers->pluck('id');

        // BULLETPROOF FETCH: Grab all associated inventory & financial records for these specific vouchers
        $movements = InventoryMovement::whereIn('voucher_id', $voucherIds)
            ->leftJoin('items', 'inventory_movements.item_id', '=', 'items.id')
            ->select('inventory_movements.*', 'items.name as item_name', 'items.unit as item_unit')
            ->get()
            ->groupBy('voucher_id');

        $entries = VoucherEntry::whereIn('voucher_id', $voucherIds)
            ->leftJoin('accounts', 'voucher_entries.account_id', '=', 'accounts.id')
            ->select('voucher_entries.*', 'accounts.name as account_name', 'accounts.group_type')
            ->get()
            ->groupBy('voucher_id');

        return view('transactions', compact('vouchers', 'startDate', 'endDate', 'movements', 'entries'));
    }

    public function export(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $vouchers = Voucher::whereDate('voucher_date', '>=', $startDate)
            ->whereDate('voucher_date', '<=', $endDate)
            ->orderBy('voucher_date', 'asc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="daybook-export.csv"',
        ];

        $callback = function () use ($vouchers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Type', 'Reference', 'Narration']);
            foreach ($vouchers as $voucher) {
                fputcsv($handle, [
                    $voucher->voucher_date,
                    $voucher->voucher_type,
                    $voucher->reference_number ?? '',
                    $voucher->notes ?? '',
                ]);
            }
            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }

    // Safely delete and reverse a transaction
    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $voucher = Voucher::findOrFail($id);

            // 1. REVERSE PHYSICAL STOCK
            $movements = InventoryMovement::where('voucher_id', $id)->get();
            foreach ($movements as $movement) {
                if ($movement->movement_type == 'In') {
                    Item::where('id', $movement->item_id)->decrement('current_stock', $movement->quantity);
                } else if ($movement->movement_type == 'Out') {
                    Item::where('id', $movement->item_id)->increment('current_stock', $movement->quantity);
                }
            }
            InventoryMovement::where('voucher_id', $id)->delete();

            // 2. REVERSE FINANCIAL BALANCES
            $entries = VoucherEntry::where('voucher_id', $id)->get();
            foreach ($entries as $entry) {
                $account = Account::find($entry->account_id);
                if ($account) {
                    if ($account->group_type == 'Sundry Debtors') {
                        $entry->entry_type == 'Debit' ? $account->decrement('balance', $entry->amount) : $account->increment('balance', $entry->amount);
                    } 
                    elseif ($account->group_type == 'Sundry Creditors') {
                        $entry->entry_type == 'Credit' ? $account->decrement('balance', $entry->amount) : $account->increment('balance', $entry->amount);
                    } 
                    elseif ($account->group_type == 'Cash') {
                        $entry->entry_type == 'Debit' ? $account->decrement('balance', $entry->amount) : $account->increment('balance', $entry->amount);
                    } 
                    elseif ($account->group_type == 'Indirect Expenses') {
                        if ($entry->entry_type == 'Debit') $account->decrement('balance', $entry->amount);
                    }
                    elseif ($account->group_type == 'Indirect Incomes') {
                        if ($entry->entry_type == 'Credit') $account->increment('balance', $entry->amount);
                    }
                    elseif ($account->group_type == 'Direct Incomes') {
                        if ($entry->entry_type == 'Credit') $account->decrement('balance', $entry->amount); // Reverse Sales
                    }
                    elseif ($account->group_type == 'Direct Expenses') {
                        if ($entry->entry_type == 'Debit') $account->decrement('balance', $entry->amount); // Reverse Purchases
                    }
                }
            }
            VoucherEntry::where('voucher_id', $id)->delete();

            // 3. FINALLY, DELETE THE VOUCHER RECORD
            $voucher->delete();
        });

        return redirect('/transactions');
    }
}
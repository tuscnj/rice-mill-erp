<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Voucher;
use App\Models\VoucherEntry;
use App\Models\InventoryMovement;
use App\Models\Item;
use App\Models\Account;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    // 🚨 NEW SALES METHOD (This was the missing piece causing the crash!)
    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            // A. CREATE VOUCHER HEADER
            $narration = trim((string) $request->input('narration', ''));
            if ($narration === '') $narration = 'Goods sold on credit';

            $voucher = Voucher::create([
                'voucher_type' => 'Sales',
                'voucher_date' => $request->input('voucher_date', now()),
                'reference_number' => $request->invoice_number,
                'notes' => $narration,
            ]);

            // B. PROCESS ITEMS AND PHYSICAL STOCK (OUT)
            $itemIds = $request->input('item_id', []);
            $quantities = $request->input('quantity', []);
            $unitIds = $request->input('unit_id', []);
            $rates = $request->input('rate', []);
            $totalAmount = 0;

            foreach ($itemIds as $index => $itemId) {
                if (empty($itemId) || !isset($quantities[$index]) || !isset($unitIds[$index]) || !isset($rates[$index])) continue;

                $qty = (float) $quantities[$index];
                $rate = (float) $rates[$index];
                if ($qty <= 0 || $rate < 0) continue;

                $unit = Unit::find($unitIds[$index]);
                $baseQuantity = $unit ? $qty * $unit->conversion_rate : $qty;
                $lineTotal = $qty * $rate;
                $totalAmount += $lineTotal;

                // Stock leaves the warehouse
                InventoryMovement::create([
                    'voucher_id' => $voucher->id,
                    'item_id' => $itemId,
                    'movement_type' => 'Out', 
                    'quantity' => $baseQuantity,
                    'rate' => $rate,
                ]);
                Item::where('id', $itemId)->decrement('current_stock', $baseQuantity);
            }

            // C. PROCESS FINANCIAL ENTRIES
            if ($totalAmount > 0) {
                $party = Account::findOrFail($request->party_id);

                // Debit Customer (They owe us money)
                VoucherEntry::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $party->id,
                    'amount' => $totalAmount,
                    'entry_type' => 'Debit',
                ]);
                $party->increment('balance', $totalAmount);

                // Credit Sales Account (Revenue)
                $salesAccount = Account::firstOrCreate(['name' => 'Sales Account'], ['group_type' => 'Direct Incomes', 'balance' => 0, 'is_active' => true]);
                VoucherEntry::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $salesAccount->id,
                    'amount' => $totalAmount,
                    'entry_type' => 'Credit',
                ]);
                $salesAccount->increment('balance', $totalAmount);
            }
        });

        return redirect('/sales')->with('success', 'Sale recorded successfully!');
    }

    // 🚨 EXISTING UPDATE METHOD (Fixed the math for editing sales)
    public function update(Request $request, $id)
    {
        DB::transaction(function () use ($request, $id) {
            $voucher = Voucher::with(['entries', 'inventoryMovements'])->findOrFail($id);

            // A. REVERSE OLD PHYSICAL STOCK (Put items back temporarily)
            foreach ($voucher->inventoryMovements as $movement) {
                if ($movement->movement_type == 'Out') {
                    Item::where('id', $movement->item_id)->increment('current_stock', $movement->quantity);
                }
            }
            $voucher->inventoryMovements()->delete();

            // B. REVERSE OLD FINANCIAL BALANCES
            foreach ($voucher->entries as $entry) {
                $account = Account::find($entry->account_id);
                if ($account) {
                    if ($entry->entry_type == 'Debit') {
                        $account->decrement('balance', $entry->amount); 
                    } else {
                        $account->decrement('balance', $entry->amount); 
                    }
                }
            }
            $voucher->entries()->delete();

            // C. UPDATE VOUCHER HEADER
            $narration = trim((string) $request->input('narration', ''));
            if ($narration === '') $narration = 'Goods sold on credit';

            $voucher->update([
                'voucher_date' => $request->input('voucher_date', now()),
                'reference_number' => $request->invoice_number,
                'notes' => $narration,
            ]);

            // D. PROCESS NEW ITEMS AND BALANCES (OUT)
            $itemIds = $request->input('item_id', []);
            $quantities = $request->input('quantity', []);
            $unitIds = $request->input('unit_id', []);
            $rates = $request->input('rate', []);
            $totalAmount = 0;

            foreach ($itemIds as $index => $itemId) {
                if (empty($itemId) || !isset($quantities[$index]) || !isset($unitIds[$index]) || !isset($rates[$index])) continue;

                $qty = (float) $quantities[$index];
                $rate = (float) $rates[$index];
                if ($qty <= 0 || $rate < 0) continue;

                $unit = Unit::find($unitIds[$index]);
                $baseQuantity = $unit ? $qty * $unit->conversion_rate : $qty;
                $lineTotal = $qty * $rate;
                $totalAmount += $lineTotal;

                // Stock leaves the warehouse
                InventoryMovement::create([
                    'voucher_id' => $voucher->id,
                    'item_id' => $itemId,
                    'movement_type' => 'Out',
                    'quantity' => $baseQuantity,
                    'rate' => $rate,
                ]);
                Item::where('id', $itemId)->decrement('current_stock', $baseQuantity);
            }

            // E. PROCESS NEW FINANCIAL ENTRIES
            if ($totalAmount > 0) {
                $party = Account::findOrFail($request->party_id);

                // Debit Customer
                VoucherEntry::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $party->id,
                    'amount' => $totalAmount,
                    'entry_type' => 'Debit',
                ]);
                $party->increment('balance', $totalAmount);

                // Credit Sales Account
                $salesAccount = Account::firstOrCreate(['name' => 'Sales Account'], ['group_type' => 'Direct Incomes', 'balance' => 0, 'is_active' => true]);
                VoucherEntry::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $salesAccount->id,
                    'amount' => $totalAmount,
                    'entry_type' => 'Credit',
                ]);
                $salesAccount->increment('balance', $totalAmount);
            }
        });

        return redirect('/transactions')->with('success', 'Sale updated successfully!');
    }
}
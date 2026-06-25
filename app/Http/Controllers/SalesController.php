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
   public function update(Request $request, $id)
    {
        DB::transaction(function () use ($request, $id) {
            $voucher = Voucher::with(['entries', 'inventoryMovements'])->findOrFail($id);

            // 1. REVERSE PHYSICAL STOCK (Remove the returned items back out of stock)
            foreach ($voucher->inventoryMovements as $movement) {
                if ($movement->movement_type == 'In') {
                    Item::where('id', $movement->item_id)->decrement('current_stock', $movement->quantity);
                }
            }
            $voucher->inventoryMovements()->delete();

            // 2. REVERSE FINANCIAL BALANCES
            foreach ($voucher->entries as $entry) {
                $account = Account::find($entry->account_id);
                if ($account && $account->group_type == 'Sundry Debtors' && $entry->entry_type == 'Credit') {
                    // Reverse customer credit (adds balance back)
                    $account->increment('balance', $entry->amount);
                }
            }
            $voucher->entries()->delete();

            // 3. UPDATE VOUCHER HEADER
            $narration = trim((string) $request->input('narration', ''));
            if ($narration === '') {
                $narration = 'Sales return';
            }

            $voucher->update([
                'voucher_date' => $request->input('voucher_date', now()),
                'reference_number' => $request->invoice_number,
                'notes' => $narration,
            ]);

            // 4. PROCESS NEW ITEMS AND BALANCES
            $itemIds = $request->input('item_id', []);
            $quantities = $request->input('quantity', []);
            $unitIds = $request->input('unit_id', []);
            $rates = $request->input('rate', []);
            $totalAmount = 0;

            foreach ($itemIds as $index => $itemId) {
                if (empty($itemId) || !isset($quantities[$index]) || !isset($unitIds[$index]) || !isset($rates[$index])) {
                    continue;
                }

                $qty = (float) $quantities[$index];
                $rate = (float) $rates[$index];

                if ($qty <= 0 || $rate < 0) continue;

                $unit = Unit::findOrFail($unitIds[$index]);
                $baseQuantity = $qty * $unit->conversion_rate;
                $lineTotal = $qty * $rate;
                $totalAmount += $lineTotal;

                // Bring new stock In
                InventoryMovement::create([
                    'voucher_id' => $voucher->id,
                    'item_id' => $itemId,
                    'movement_type' => 'In',
                    'quantity' => $baseQuantity,
                    'rate' => $rate,
                ]);
                Item::where('id', $itemId)->increment('current_stock', $baseQuantity);
            }

            // 5. PROCESS NEW FINANCIAL ENTRIES
            if ($totalAmount > 0) {
                $party = Account::findOrFail($request->party_id);

                // Credit the customer (they owe less)
                VoucherEntry::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $party->id,
                    'amount' => $totalAmount,
                    'entry_type' => 'Credit',
                ]);
                $party->decrement('balance', $totalAmount);

                // Debit the Sales Account (reversing revenue)
                VoucherEntry::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => 5, // Sales Account ID
                    'amount' => $totalAmount,
                    'entry_type' => 'Debit',
                ]);
            }
        });

        return redirect('/transactions')->with('success', 'Sales Return updated successfully!');
    }
}
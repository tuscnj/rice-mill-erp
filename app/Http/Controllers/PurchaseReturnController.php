<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\InventoryMovement;
use App\Models\Item;
use App\Models\Unit;
use App\Models\Voucher;
use App\Models\VoucherEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
{
    public function update(Request $request, $id)
    {
        DB::transaction(function () use ($request, $id) {
            $voucher = Voucher::with(['entries', 'inventoryMovements'])->findOrFail($id);

            // 1. REVERSE PHYSICAL STOCK (Put returned items back into stock temporarily)
            foreach ($voucher->inventoryMovements as $movement) {
                if ($movement->movement_type == 'Out') {
                    Item::where('id', $movement->item_id)->increment('current_stock', $movement->quantity);
                }
            }
            $voucher->inventoryMovements()->delete();

            // 2. REVERSE FINANCIAL BALANCES
            foreach ($voucher->entries as $entry) {
                $account = Account::find($entry->account_id);
                if ($account && $account->group_type == 'Sundry Creditors' && $entry->entry_type == 'Debit') {
                    // Reverse supplier debit (adds the payable balance back to what it was)
                    $account->increment('balance', $entry->amount);
                }
            }
            $voucher->entries()->delete();

            // 3. UPDATE VOUCHER HEADER
            $narration = trim((string) $request->input('narration', ''));
            if ($narration === '') {
                $narration = 'Purchase return';
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

                // Send new stock Out
                InventoryMovement::create([
                    'voucher_id' => $voucher->id,
                    'item_id' => $itemId,
                    'movement_type' => 'Out',
                    'quantity' => $baseQuantity,
                    'rate' => $rate,
                ]);
                Item::where('id', $itemId)->decrement('current_stock', $baseQuantity);
            }

            // 5. PROCESS NEW FINANCIAL ENTRIES
            if ($totalAmount > 0) {
                $party = Account::findOrFail($request->party_id);

                // Credit Purchase Account
                VoucherEntry::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => 1,
                    'amount' => $totalAmount,
                    'entry_type' => 'Credit',
                ]);

                // Debit Supplier (we owe them less)
                VoucherEntry::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $party->id,
                    'amount' => $totalAmount,
                    'entry_type' => 'Debit',
                ]);
                $party->decrement('balance', $totalAmount);
            }
        });

        return redirect('/transactions')->with('success', 'Purchase Return updated successfully!');
    }
}

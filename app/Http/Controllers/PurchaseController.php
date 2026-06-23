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

class PurchaseController extends Controller
{
    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            $itemIds = $request->input('item_id', []);
            $quantities = $request->input('quantity', []);
            $unitIds = $request->input('unit_id', []);
            $rates = $request->input('rate', []);

            $totalAmount = 0;

            $narration = trim((string) $request->input('narration', ''));
            if ($narration === '') {
                $narration = 'Purchased inventory';
            }

            // 1. Create Voucher Header
            $voucher = Voucher::create([
                'voucher_type' => 'Purchase',
                'voucher_date' => now(),
                'reference_number' => $request->invoice_number,
                'notes' => $narration
            ]);

            // 2. Process each item row
            foreach ($itemIds as $index => $itemId) {
                if (empty($itemId) || !isset($quantities[$index]) || !isset($unitIds[$index]) || !isset($rates[$index])) {
                    continue;
                }

                $qty = (float) $quantities[$index];
                $rate = (float) $rates[$index];

                if ($qty <= 0 || $rate < 0) {
                    continue;
                }

                $unit = Unit::findOrFail($unitIds[$index]);
                
                $baseQuantity = $qty * $unit->conversion_rate;
                $lineTotal = $qty * $rate;
                $totalAmount += $lineTotal;

                $ratePerBaseUnit = $baseQuantity > 0 ? ($lineTotal / $baseQuantity) : 0;

                // --- MOVING AVERAGE COST CALCULATION ---
                $item = Item::find($itemId);
                $oldStock = $item->current_stock > 0 ? $item->current_stock : 0;
                $oldRate = $item->purchase_rate ?? 0;
                
                $totalOldValue = $oldStock * $oldRate;
                $totalNewValue = $baseQuantity * $ratePerBaseUnit;
                
                $newStockTotal = $oldStock + $baseQuantity;
                $movingAverageCost = $newStockTotal > 0 ? (($totalOldValue + $totalNewValue) / $newStockTotal) : 0;

                InventoryMovement::create([
                    'voucher_id' => $voucher->id,
                    'item_id' => $itemId,
                    'movement_type' => 'In',
                    'quantity' => $baseQuantity,
                    'rate' => $ratePerBaseUnit,
                ]);
                
                $item->update([
                    'purchase_rate' => $movingAverageCost,
                    'current_stock' => $item->current_stock + $baseQuantity
                ]);
            }

            // 3. Update Financial Ledgers
            if ($totalAmount > 0) {
                $party = Account::findOrFail($request->party_id);

                $purchaseAccount = Account::firstOrCreate(
                    ['name' => 'Purchase Account'],
                    ['group_type' => 'Direct Expenses', 'balance' => 0]
                );

                VoucherEntry::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $purchaseAccount->id,
                    'amount' => $totalAmount,
                    'entry_type' => 'Debit'
                ]);

                VoucherEntry::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $party->id,
                    'amount' => $totalAmount,
                    'entry_type' => 'Credit'
                ]);

                // Update BOTH static balances
                $party->increment('balance', $totalAmount);
                $purchaseAccount->increment('balance', $totalAmount); // <- This is the fix!
            }
        });

        return redirect('/stock');
    }
}
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
                $narration = 'Purchase return';
            }

            $voucher = Voucher::create([
                'voucher_type' => 'Purchase Return',
                'voucher_date' => now(),
                'reference_number' => $request->invoice_number,
                'notes' => $narration,
            ]);

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

                InventoryMovement::create([
                    'voucher_id' => $voucher->id,
                    'item_id' => $itemId,
                    'movement_type' => 'Out',
                    'quantity' => $baseQuantity,
                    'rate' => $rate,
                ]);

                Item::where('id', $itemId)->decrement('current_stock', $baseQuantity);
            }

            if ($totalAmount > 0) {
                $party = Account::findOrFail($request->party_id);

                VoucherEntry::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => 1,
                    'amount' => $totalAmount,
                    'entry_type' => 'Credit',
                ]);

                VoucherEntry::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $party->id,
                    'amount' => $totalAmount,
                    'entry_type' => 'Debit',
                ]);
                $party->decrement('balance', $totalAmount);
            }
        });

        return redirect('/transactions');
    }
}

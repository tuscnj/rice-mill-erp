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
    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            $itemIds = $request->input('item_id', []);
            $quantities = $request->input('quantity', []);
            $unitIds = $request->input('unit_id', []);
            $rates = $request->input('rate', []);

            $totalAmount = 0;

            // 1. Create Voucher Header
            $voucher = Voucher::create([
                'voucher_type' => 'Sales',
                'voucher_date' => $request->input('voucher_date', now()),
                'reference_number' => $request->invoice_number,
                'notes' => $request->narration ?? 'Sales transaction'
            ]);

            // 2. Process each item row safely
            foreach ($itemIds as $index => $itemId) {
                if (empty($itemId) || !isset($quantities[$index]) || !isset($rates[$index])) {
                    continue;
                }

                $qty = (float) $quantities[$index];
                $rate = (float) $rates[$index];

                if ($qty <= 0) continue;

                $conversionRate = 1; 
                if (isset($unitIds[$index]) && !empty($unitIds[$index])) {
                    $unit = Unit::find($unitIds[$index]);
                    if ($unit && $unit->conversion_rate > 0) {
                        $conversionRate = $unit->conversion_rate;
                    }
                }
                
                $baseQuantity = $qty * $conversionRate;
                $lineTotal = $qty * $rate;
                $totalAmount += $lineTotal;

                $ratePerBaseUnit = $baseQuantity > 0 ? ($lineTotal / $baseQuantity) : 0;

                InventoryMovement::create([
                    'voucher_id' => $voucher->id,
                    'item_id' => $itemId,
                    'movement_type' => 'Out',
                    'quantity' => $baseQuantity,
                    'rate' => $ratePerBaseUnit,
                ]);
                
                Item::where('id', $itemId)->decrement('current_stock', $baseQuantity);
            }

            // 3. Update Financial Ledgers
            if ($totalAmount > 0) {
                $customer = Account::find($request->party_id);
                if (!$customer) {
                    throw new \Exception("Invalid Customer Selected. Please select a party from the dropdown.");
                }

                $salesAccount = Account::firstOrCreate(
                    ['name' => 'Sales Account'],
                    ['group_type' => 'Direct Incomes', 'balance' => 0]
                );

                VoucherEntry::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $customer->id,
                    'amount' => $totalAmount,
                    'entry_type' => 'Debit'
                ]);

                VoucherEntry::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $salesAccount->id, 
                    'amount' => $totalAmount,
                    'entry_type' => 'Credit'
                ]);

                $customer->increment('balance', $totalAmount);
                $salesAccount->increment('balance', $totalAmount);
            }
        });

        return redirect('/sales');
    }

    public function update(Request $request, $id)
    {
        DB::transaction(function () use ($request, $id) {
            $voucher = Voucher::findOrFail($id);

            // 1. REVERSE OLD STOCK & FINANCIALS
            $oldMovements = InventoryMovement::where('voucher_id', $id)->get();
            foreach ($oldMovements as $movement) {
                if ($movement->movement_type == 'Out') {
                    // Sales deducts stock, so reversing it means adding it back
                    Item::where('id', $movement->item_id)->increment('current_stock', $movement->quantity);
                }
            }
            InventoryMovement::where('voucher_id', $id)->delete();

            $oldEntries = VoucherEntry::where('voucher_id', $id)->get();
            foreach ($oldEntries as $entry) {
                $account = Account::find($entry->account_id);
                if ($account) {
                    if ($account->group_type == 'Sundry Debtors') {
                        // Reversing a Debit to Customer
                        $entry->entry_type == 'Debit' ? $account->decrement('balance', $entry->amount) : $account->increment('balance', $entry->amount);
                    } elseif ($account->group_type == 'Direct Incomes') {
                        // Reversing a Credit to Sales
                        if ($entry->entry_type == 'Credit') $account->decrement('balance', $entry->amount);
                    }
                }
            }
            VoucherEntry::where('voucher_id', $id)->delete();

            // 2. UPDATE VOUCHER HEADER
            $voucher->update([
                'reference_number' => $request->invoice_number,
                'notes' => $request->narration ?? 'Sales transaction'
            ]);

            // 3. APPLY NEW DATA
            $itemIds = $request->input('item_id', []);
            $quantities = $request->input('quantity', []);
            $unitIds = $request->input('unit_id', []);
            $rates = $request->input('rate', []);

            $totalAmount = 0;

            foreach ($itemIds as $index => $itemId) {
                if (empty($itemId) || !isset($quantities[$index]) || !isset($rates[$index])) continue;

                $qty = (float) $quantities[$index];
                $rate = (float) $rates[$index];
                if ($qty <= 0) continue;

                $conversionRate = 1; 
                if (isset($unitIds[$index]) && !empty($unitIds[$index])) {
                    $unit = Unit::find($unitIds[$index]);
                    if ($unit && $unit->conversion_rate > 0) $conversionRate = $unit->conversion_rate;
                }
                
                $baseQuantity = $qty * $conversionRate;
                $lineTotal = $qty * $rate;
                $totalAmount += $lineTotal;

                $ratePerBaseUnit = $baseQuantity > 0 ? ($lineTotal / $baseQuantity) : 0;

                InventoryMovement::create([
                    'voucher_id' => $voucher->id,
                    'item_id' => $itemId,
                    'movement_type' => 'Out',
                    'quantity' => $baseQuantity,
                    'rate' => $ratePerBaseUnit,
                ]);
                
                Item::where('id', $itemId)->decrement('current_stock', $baseQuantity);
            }

            // 4. RE-APPLY FINANCIAL LEDGERS
            if ($totalAmount > 0) {
                $customer = Account::findOrFail($request->party_id);
                $salesAccount = Account::where('name', 'Sales Account')->first();

                VoucherEntry::create(['voucher_id' => $voucher->id, 'account_id' => $customer->id, 'amount' => $totalAmount, 'entry_type' => 'Debit']);
                VoucherEntry::create(['voucher_id' => $voucher->id, 'account_id' => $salesAccount->id, 'amount' => $totalAmount, 'entry_type' => 'Credit']);

                $customer->increment('balance', $totalAmount);
                $salesAccount->increment('balance', $totalAmount);
            }
        });

        return redirect('/transactions');
    }
}
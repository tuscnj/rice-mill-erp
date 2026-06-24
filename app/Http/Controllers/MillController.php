<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Voucher;
use App\Models\InventoryMovement;
use App\Models\Item;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class MillController extends Controller
{
    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            
            // 1. Create the Milling Voucher
            $voucher = Voucher::create([
                'voucher_type' => 'Production',
                'voucher_date' => $request->input('voucher_date', now()),
                'reference_number' => $request->batch_number,
                'notes' => 'Milled paddy into multiple goods/byproducts'
            ]);

            // 2. Deduct the Raw Paddy from Stock using the MANUAL Rate
            $rawUnit = Unit::findOrFail($request->raw_unit_id);
            $rawKg = $request->raw_quantity * $rawUnit->conversion_rate;
            $rawBaseRate = $request->raw_rate / $rawUnit->conversion_rate; // Convert display rate to Base KG rate

            InventoryMovement::create([
                'voucher_id' => $voucher->id,
                'item_id' => $request->raw_item_id,
                'movement_type' => 'Out',
                'quantity' => $rawKg,
                'rate' => $rawBaseRate,
            ]);
            Item::where('id', $request->raw_item_id)->decrement('current_stock', $rawKg);

            // 3. Process Finished Goods (Rice) using the MANUAL Rate
            if ($request->has('rice_item_id')) {
                foreach ($request->rice_item_id as $index => $itemId) {
                    if ($itemId && !empty($request->rice_quantity[$index]) && isset($request->rice_rate[$index])) {
                        $riceUnit = Unit::findOrFail($request->rice_unit_id[$index]);
                        $riceKg = $request->rice_quantity[$index] * $riceUnit->conversion_rate;
                        $riceBaseRate = $request->rice_rate[$index] / $riceUnit->conversion_rate;

                        InventoryMovement::create([
                            'voucher_id' => $voucher->id,
                            'item_id' => $itemId,
                            'movement_type' => 'In',
                            'quantity' => $riceKg,
                            'rate' => $riceBaseRate,
                        ]);
                        
                        // Update the warehouse stock AND dynamically update the item's valuation rate!
                        Item::where('id', $itemId)->update(['purchase_rate' => $riceBaseRate]);
                        Item::where('id', $itemId)->increment('current_stock', $riceKg);
                    }
                }
            }

            // 4. Process Byproducts using the MANUAL Rate
            if ($request->has('byproduct_item_id')) {
                foreach ($request->byproduct_item_id as $index => $itemId) {
                    if ($itemId && !empty($request->byproduct_quantity[$index]) && isset($request->byproduct_rate[$index])) {
                        $byproductUnit = Unit::findOrFail($request->byproduct_unit_id[$index]);
                        $byproductKg = $request->byproduct_quantity[$index] * $byproductUnit->conversion_rate;
                        $byproductBaseRate = $request->byproduct_rate[$index] / $byproductUnit->conversion_rate;

                        InventoryMovement::create([
                            'voucher_id' => $voucher->id,
                            'item_id' => $itemId,
                            'movement_type' => 'In',
                            'quantity' => $byproductKg,
                            'rate' => $byproductBaseRate,
                        ]);
                        
                        // Update the warehouse stock AND dynamically update the item's valuation rate!
                        Item::where('id', $itemId)->update(['purchase_rate' => $byproductBaseRate]);
                        Item::where('id', $itemId)->increment('current_stock', $byproductKg);
                    }
                }
            }
        });

        return redirect('/stock');
    }

    public function update(Request $request, $id)
    {
        DB::transaction(function () use ($request, $id) {
            $voucher = Voucher::findOrFail($id);

            // 1. REVERSE OLD STOCK MOVEMENTS
            $oldMovements = InventoryMovement::where('voucher_id', $id)->get();
            foreach ($oldMovements as $movement) {
                if ($movement->movement_type == 'Out') {
                    // It was raw paddy deducted, so we put it back
                    Item::where('id', $movement->item_id)->increment('current_stock', $movement->quantity);
                } elseif ($movement->movement_type == 'In') {
                    // It was rice/bran produced, so we take it back out
                    Item::where('id', $movement->item_id)->decrement('current_stock', $movement->quantity);
                }
            }
            // Delete the old movement records
            InventoryMovement::where('voucher_id', $id)->delete();

            // 2. UPDATE VOUCHER HEADER
            $voucher->update([
                'reference_number' => $request->batch_number,
                // Notes remain unchanged
            ]);

            // 3. RE-APPLY INPUT (Raw Material)
            $rawUnit = Unit::findOrFail($request->raw_unit_id);
            $rawKg = $request->raw_quantity * $rawUnit->conversion_rate;
            $rawBaseRate = $request->raw_rate / $rawUnit->conversion_rate;

            InventoryMovement::create([
                'voucher_id' => $voucher->id,
                'item_id' => $request->raw_item_id,
                'movement_type' => 'Out',
                'quantity' => $rawKg,
                'rate' => $rawBaseRate,
            ]);
            Item::where('id', $request->raw_item_id)->decrement('current_stock', $rawKg);

            // 4. RE-APPLY OUTPUT (Finished Goods / Rice)
            if ($request->has('rice_item_id')) {
                foreach ($request->rice_item_id as $index => $itemId) {
                    if ($itemId && !empty($request->rice_quantity[$index]) && isset($request->rice_rate[$index])) {
                        $riceUnit = Unit::findOrFail($request->rice_unit_id[$index]);
                        $riceKg = $request->rice_quantity[$index] * $riceUnit->conversion_rate;
                        $riceBaseRate = $request->rice_rate[$index] / $riceUnit->conversion_rate;

                        InventoryMovement::create([
                            'voucher_id' => $voucher->id,
                            'item_id' => $itemId,
                            'movement_type' => 'In',
                            'quantity' => $riceKg,
                            'rate' => $riceBaseRate,
                        ]);
                        
                        Item::where('id', $itemId)->update(['purchase_rate' => $riceBaseRate]);
                        Item::where('id', $itemId)->increment('current_stock', $riceKg);
                    }
                }
            }

            // 5. RE-APPLY OUTPUT (Byproducts)
            if ($request->has('byproduct_item_id')) {
                foreach ($request->byproduct_item_id as $index => $itemId) {
                    if ($itemId && !empty($request->byproduct_quantity[$index]) && isset($request->byproduct_rate[$index])) {
                        $byproductUnit = Unit::findOrFail($request->byproduct_unit_id[$index]);
                        $byproductKg = $request->byproduct_quantity[$index] * $byproductUnit->conversion_rate;
                        $byproductBaseRate = $request->byproduct_rate[$index] / $byproductUnit->conversion_rate;

                        InventoryMovement::create([
                            'voucher_id' => $voucher->id,
                            'item_id' => $itemId,
                            'movement_type' => 'In',
                            'quantity' => $byproductKg,
                            'rate' => $byproductBaseRate,
                        ]);
                        
                        Item::where('id', $itemId)->update(['purchase_rate' => $byproductBaseRate]);
                        Item::where('id', $itemId)->increment('current_stock', $byproductKg);
                    }
                }
            }
        });

        // Redirect back to the Daybook since we edited from there
        return redirect('/transactions');
    }
}
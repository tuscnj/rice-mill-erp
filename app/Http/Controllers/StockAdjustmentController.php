<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    public function update(Request $request, $id)
    {
        $request->validate([
            'item_id' => 'required',
            'adjustment_type' => 'required|in:In,Out',
            'quantity' => 'required|numeric|min:0.01'
        ]);

        DB::transaction(function () use ($request, $id) {
            $voucher = Voucher::with('inventoryMovements')->findOrFail($id);

            // 1. REVERSE OLD STOCK MOVEMENT
            foreach ($voucher->inventoryMovements as $movement) {
                $oldItem = Item::find($movement->item_id);
                if ($oldItem) {
                    if ($movement->movement_type == 'In') {
                        $oldItem->decrement('current_stock', $movement->quantity);
                    } else {
                        $oldItem->increment('current_stock', $movement->quantity);
                    }
                }
            }
            $voucher->inventoryMovements()->delete();

            // 2. FETCH NEW DATA
            $newItem = Item::findOrFail($request->item_id);
            $qty = $request->quantity;
            $type = $request->adjustment_type;
            
            // Extract the original prefix (e.g. "Stock Adjustment: Damaged goods" -> "Damaged goods")
            $cleanNotes = str_replace('Stock Adjustment: ', '', $request->notes);

            // 3. UPDATE VOUCHER DETAILS
            $voucher->update([
                'voucher_date' => $request->input('voucher_date', now()),
                'notes' => 'Stock Adjustment: ' . $cleanNotes
            ]);

            // 4. INSERT NEW INVENTORY MOVEMENT
            DB::table('inventory_movements')->insert([
                'voucher_id' => $voucher->id,
                'item_id' => $newItem->id,
                'quantity' => $qty,
                'rate' => $newItem->purchase_rate ?? 0,
                'movement_type' => $type,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // 5. UPDATE LIVE STOCK COUNT
            if ($type == 'In') {
                $newItem->increment('current_stock', $qty);
            } else {
                $newItem->decrement('current_stock', $qty);
            }
        });

        return redirect('/transactions')->with('success', 'Stock Adjustment updated successfully!');
    }
}
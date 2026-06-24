<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    public function index()
    {
        $items = Item::orderBy('name')->get();
        return view('stock-adjustment', ['items' => $items]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_id' => 'required',
            'adjustment_type' => 'required|in:In,Out',
            'quantity' => 'required|numeric|min:0.01'
        ]);

        DB::transaction(function () use ($request) {
            $item = Item::findOrFail($request->item_id);
            $qty = $request->quantity;
            $type = $request->adjustment_type;

            // Create a Journal/Adjustment Voucher
            $voucher = Voucher::create([
                'voucher_type' => 'Journal', // Keeping it as Journal so it shows up cleanly in Daybook
                'voucher_date' => now(),
                'reference_number' => 'ADJ',
                'notes' => 'Stock Adjustment: ' . $request->notes
            ]);

            // Insert directly into inventory_movements table
            DB::table('inventory_movements')->insert([
                'voucher_id' => $voucher->id,
                'item_id' => $item->id,
                'quantity' => $qty,
                'rate' => $item->purchase_rate ?? 0,
                'movement_type' => $type,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Update live stock count
            if ($type == 'In') {
                $item->increment('current_stock', $qty);
            } else {
                $item->decrement('current_stock', $qty);
            }
        });

        // Redirect back to the Live Stock Dashboard
        return redirect('/stock');
    }
}
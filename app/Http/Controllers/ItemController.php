<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Unit;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::orderBy('category')->orderBy('name')->get();
        $units = Unit::all(); 
        return view('stock', ['items' => $items, 'units' => $units]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'unit' => 'required|string',
            'opening_stock' => 'nullable|numeric|min:0',
            'purchase_rate' => 'nullable|numeric|min:0', 
        ]);

        // Find the unit conversion rate so we always save pure Base KG to the DB
        $unitStr = $request->unit;
        $unitObj = Unit::where('short_name', $unitStr)->orWhere('name', $unitStr)->first();
        $conversionRate = ($unitObj && $unitObj->conversion_rate > 0) ? $unitObj->conversion_rate : 1;

        Item::create([
            'name' => $request->name,
            'category' => $request->category,
            'unit' => $unitStr,
            'opening_stock' => ((float) ($request->opening_stock ?? 0)) * $conversionRate, 
            'current_stock' => ((float) ($request->opening_stock ?? 0)) * $conversionRate, 
            'purchase_rate' => ((float) ($request->purchase_rate ?? 0)) / $conversionRate 
        ]);

        return redirect('/items')->with('success', 'Item created successfully.');
    }

    public function edit($id)
    {
        $item = Item::findOrFail($id);
        $units = Unit::all();
        return view('edit-item', ['item' => $item, 'units' => $units]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'unit' => 'required|string',
            'opening_stock' => 'required|numeric|min:0',
            'current_stock' => 'required|numeric|min:0',
            'purchase_rate' => 'required|numeric|min:0',
        ]);

        // Find the unit conversion rate so we always save pure Base KG to the DB
        $unitStr = $request->unit;
        $unitObj = Unit::where('short_name', $unitStr)->orWhere('name', $unitStr)->first();
        $conversionRate = ($unitObj && $unitObj->conversion_rate > 0) ? $unitObj->conversion_rate : 1;

        Item::where('id', $id)->update([
            'name' => $request->name,
            'category' => $request->category,
            'unit' => $unitStr,
            'opening_stock' => ((float) $request->opening_stock) * $conversionRate, 
            'current_stock' => ((float) $request->current_stock) * $conversionRate,
            'purchase_rate' => ((float) $request->purchase_rate) / $conversionRate
        ]);
        
        return redirect('/items');
    }

    public function destroy($id)
    {
        $hasStockHistory = \App\Models\InventoryMovement::where('item_id', $id)->exists();
        if ($hasStockHistory) {
            return "<script>alert('🛑 CANNOT DELETE: This item has inventory history. You may only edit its name.'); window.location.href='/items';</script>";
        }

        Item::findOrFail($id)->delete();
        return redirect('/items');
    }
}
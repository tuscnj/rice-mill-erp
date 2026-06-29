<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Unit;

class ItemController extends Controller
{
    // 1. LOADS "INVENTORY" (The Live Stock Dashboard)
    public function stock()
    {
        $items = Item::orderBy('category')->orderBy('name')->get();
        $units = Unit::all();
        return view('stock', ['items' => $items, 'units' => $units]);
    }

    // 2. Save a new item (Used by the form on the Inventory page)
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'item_group' => 'nullable|string', // <-- Validates the new field
        ]);

        Item::create([
            'name' => $request->name,
            'category' => $request->category,
            'item_group' => $request->item_group, // <-- Saves the new field
            'opening_stock' => $request->opening_stock ?? 0,
            'current_stock' => $request->opening_stock ?? 0,
            'purchase_rate' => $request->opening_rate ?? $request->purchase_rate ?? 0,
            'unit' => $request->unit ?? 'KG'
        ]);

        return back(); 
    }

    // 3. Edit Item View
    public function edit($id)
    {
        $item = Item::findOrFail($id);
        $units = Unit::all();
        return view('edit-item', compact('item', 'units'));
    }

    // 4. Update Item
    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);
        $item->update($request->all());
        
        return redirect('/stock');
    }

    // 5. Delete Item
    public function destroy($id)
    {
        Item::findOrFail($id)->delete();
        return back();
    }
}
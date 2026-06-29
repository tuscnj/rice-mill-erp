<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Unit;
use App\Models\Brand; // <-- Imports your new Brand system

class ItemController extends Controller
{
    public function stock()
    {
        $items = Item::orderBy('category')->orderBy('name')->get();
        $units = Unit::all();
        $brands = Brand::orderBy('name')->get(); // <-- Loads the Brands
        return view('stock', compact('items', 'units', 'brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'item_group' => 'nullable|string',
        ]);

        Item::create([
            'name' => $request->name,
            'category' => $request->category,
            'item_group' => $request->item_group,
            'opening_stock' => $request->opening_stock ?? 0,
            'current_stock' => $request->opening_stock ?? 0,
            'purchase_rate' => $request->opening_rate ?? $request->purchase_rate ?? 0,
            'unit' => $request->unit ?? 'KG'
        ]);

        return back(); 
    }

    public function edit($id)
    {
        $item = Item::findOrFail($id);
        $units = Unit::all();
        $brands = Brand::orderBy('name')->get(); // <-- Loads the Brands
        return view('edit-item', compact('item', 'units', 'brands'));
    }

    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);
        $item->update($request->all());
        return redirect('/stock');
    }

    public function destroy($id)
    {
        Item::findOrFail($id)->delete();
        return back();
    }
}
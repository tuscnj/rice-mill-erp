<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Unit;

class ItemController extends Controller
{
    // -------------------------------------------------------------------------
    // 1. INVENTORY DASHBOARD — live stock list
    // -------------------------------------------------------------------------
    public function stock()
    {
        $items = Item::orderBy('category')->orderBy('name')->get();
        $units = Unit::orderBy('name')->get();

        return view('stock', compact('items', 'units'));
    }

    // -------------------------------------------------------------------------
    // 2. CREATE — show the "add item" form
    // -------------------------------------------------------------------------
    public function create()
    {
        $units = Unit::orderBy('name')->get();
        return view('create-item', compact('units'));
    }

    // -------------------------------------------------------------------------
    // 3. STORE — save a new item
    //
    // BUGS FIXED:
    //   - Added numeric validation for opening_stock and purchase_rate.
    //   - Added unit existence validation so a rogue unit string cannot be saved.
    //   - Removed the ambiguous "opening_rate vs purchase_rate" dual-name lookup.
    //     The form must send exactly one field: purchase_rate.
    //   - last_rate is now set on creation (same as purchase_rate).
    //   - Comma-formatted numbers (e.g. "61,330") are sanitised before casting.
    //   - Values are stored EXACTLY as entered, in the item's own unit.
    //     NO multiplier is applied here. The unit multiplier on the Unit model
    //     is available for cross-unit reporting only — it must NEVER be applied
    //     automatically during save.
    // -------------------------------------------------------------------------
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'category'      => 'required|string|max:255',
            'unit'          => 'required|string|exists:units,name',
            'opening_stock' => 'nullable|numeric|min:0',
            'purchase_rate' => 'nullable|numeric|min:0',
        ]);

        // Strip commas that users often type (e.g. "61,330" → 61330).
        // Without this, PHP's (float) cast silently truncates at the comma.
        $openingStock = (float) str_replace(',', '', $validated['opening_stock'] ?? 0);
        $purchaseRate = (float) str_replace(',', '', $validated['purchase_rate'] ?? 0);

        Item::create([
            'name'          => $validated['name'],
            'category'      => $validated['category'],
            'unit'          => $validated['unit'],
            'opening_stock' => $openingStock,
            'current_stock' => $openingStock,   // starts equal to opening stock
            'purchase_rate' => $purchaseRate,
            'last_rate'     => $purchaseRate,   // BUG FIXED: was never set, always NULL
        ]);

        return redirect()->route('stock.index')
            ->with('success', "Item '{$validated['name']}' created successfully.");
    }

    // -------------------------------------------------------------------------
    // 4. EDIT — show edit form for an existing item
    // -------------------------------------------------------------------------
    public function edit($id)
    {
        $item  = Item::findOrFail($id);
        $units = Unit::orderBy('name')->get();

        return view('edit-item', compact('item', 'units'));
    }

    // -------------------------------------------------------------------------
    // 5. UPDATE — save changes to an existing item
    //
    // BUGS FIXED:
    //   - Was using $request->all() with NO validation — critical security hole.
    //     Any field (including id, created_at) could be overwritten.
    //   - current_stock is NOT updatable here. Stock quantity must only change
    //     through inventory movements (purchases, sales, adjustments).
    //     Allowing direct edits to current_stock would corrupt movement history.
    //   - Added proper validation for every field.
    //   - Comma-formatted numbers sanitised.
    //   - last_rate updated when purchase_rate changes.
    // -------------------------------------------------------------------------
    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'category'      => 'required|string|max:255',
            'unit'          => 'required|string|exists:units,name',
            'purchase_rate' => 'nullable|numeric|min:0',
            // NOTE: opening_stock and current_stock are intentionally excluded.
            //   - opening_stock is a historical record and should not be edited.
            //   - current_stock must only change through inventory movements.
        ]);

        $purchaseRate = (float) str_replace(',', '', $validated['purchase_rate'] ?? $item->purchase_rate);

        $item->update([
            'name'          => $validated['name'],
            'category'      => $validated['category'],
            'unit'          => $validated['unit'],
            'purchase_rate' => $purchaseRate,
            'last_rate'     => $purchaseRate,   // always keep last_rate in sync
        ]);

        return redirect()->route('stock.index')
            ->with('success', "Item '{$item->name}' updated successfully.");
    }

    // -------------------------------------------------------------------------
    // 6. DESTROY — soft-delete an item
    //
    // BUG FIXED: Was using hard delete (forceDelete behaviour).
    //   Permanently deleting an item destroys its entire movement history.
    //   SoftDeletes (added to the Item model) marks the record as deleted_at
    //   without removing it, so stock movement records remain intact.
    // -------------------------------------------------------------------------
    public function destroy($id)
    {
        $item = Item::findOrFail($id);
        $name = $item->name;
        $item->delete(); // soft delete — row stays in DB, deleted_at is set

        return redirect()->route('stock.index')
            ->with('success', "Item '{$name}' removed from inventory.");
    }
}

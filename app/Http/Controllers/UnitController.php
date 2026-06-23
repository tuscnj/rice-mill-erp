<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Unit;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::all();
        return view('units', ['units' => $units]);
    }

    public function store(Request $request)
    {
        Unit::create([
            'name' => $request->name,
            'short_name' => $request->short_name,
            'conversion_rate' => $request->conversion_rate
        ]);

        return redirect('/units');
    }

    public function edit($id)
    {
        $unit = Unit::findOrFail($id);
        return view('edit-unit', ['unit' => $unit]);
    }

    public function update(Request $request, $id)
    {
        Unit::where('id', $id)->update([
            'name' => $request->name,
            'short_name' => $request->short_name,
            'conversion_rate' => $request->conversion_rate
        ]);
        return redirect('/units');
    }

    public function destroy($id)
    {
        // Lock the base unit from deletion
        if ($id == 1) {
            return "<script>alert('🛑 CANNOT DELETE: You cannot delete the default Base Unit (KG).'); window.location.href='/units';</script>";
        }

        Unit::findOrFail($id)->delete();
        return redirect('/units');
    }
}
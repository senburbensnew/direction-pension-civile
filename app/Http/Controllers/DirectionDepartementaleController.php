<?php

namespace App\Http\Controllers;

use App\Models\DirectionDepartementale;
use Illuminate\Http\Request;

class DirectionDepartementaleController extends Controller
{
    public function publicShow(DirectionDepartementale $direction)
    {
        $others = DirectionDepartementale::ordered()
            ->where('id', '!=', $direction->id)
            ->get();

        return view('directions.show', compact('direction', 'others'));
    }

    public function index()
    {
        $directions = DirectionDepartementale::ordered()->get();
        return view('admin.directions.index', compact('directions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'abbr'  => 'required|string|max:20|unique:direction_departementales,abbr',
            'nom'   => 'required|string|max:255',
            'ville' => 'required|string|max:100',
            'description' => 'nullable|string|max:3000',
            'color' => 'required|string|max:30',
            'order' => 'nullable|integer|min:0',
        ]);

        DirectionDepartementale::create($data);

        return back()->with('success', 'Direction départementale ajoutée.');
    }

    public function update(Request $request, DirectionDepartementale $direction)
    {
        $data = $request->validate([
            'abbr'  => 'required|string|max:20|unique:direction_departementales,abbr,' . $direction->id,
            'nom'   => 'required|string|max:255',
            'ville' => 'required|string|max:100',
            'description' => 'nullable|string|max:3000',
            'color' => 'required|string|max:30',
            'order' => 'nullable|integer|min:0',
        ]);

        $direction->update($data);

        return back()->with('success', 'Direction départementale mise à jour.');
    }

    public function destroy(DirectionDepartementale $direction)
    {
        $direction->delete();
        return back()->with('success', 'Direction supprimée.');
    }
}

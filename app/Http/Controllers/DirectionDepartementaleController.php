<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\StoreDirectionDepartementaleRequest;
use App\Http\Requests\Admin\UpdateDirectionDepartementaleRequest;
use App\Models\DirectionDepartementale;

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

    public function store(StoreDirectionDepartementaleRequest $request)
    {
        $data = $request->validated();

        DirectionDepartementale::create($data);

        return back()->with('success', 'Direction départementale ajoutée.');
    }

    public function update(UpdateDirectionDepartementaleRequest $request, DirectionDepartementale $direction)
    {
        $data = $request->validated();

        $direction->update($data);

        return back()->with('success', 'Direction départementale mise à jour.');
    }

    public function destroy(DirectionDepartementale $direction)
    {
        $direction->delete();
        return back()->with('success', 'Direction supprimée.');
    }
}

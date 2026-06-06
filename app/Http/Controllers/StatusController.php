<?php

namespace App\Http\Controllers;

use App\Models\Status;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    public function index()
    {
        $statuses = Status::withCount('demandes')->orderBy('id')->get();
        return view('admin.statuses.index', compact('statuses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'        => ['required', 'string', 'max:60', 'uppercase', 'unique:statuses,code', 'regex:/^[A-Z0-9_]+$/'],
            'label'       => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        Status::create($data);

        return back()->with('success', 'Statut créé avec succès.');
    }

    public function update(Request $request, Status $status)
    {
        $data = $request->validate([
            'label'       => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $status->update($data);

        return back()->with('success', 'Statut mis à jour.');
    }

    public function destroy(Status $status)
    {
        if ($status->demandes()->exists()) {
            return back()->with('error', 'Ce statut est utilisé par des demandes et ne peut pas être supprimé.');
        }

        $status->delete();

        return back()->with('success', 'Statut supprimé.');
    }
}

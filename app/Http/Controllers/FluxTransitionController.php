<?php

namespace App\Http\Controllers;

use App\Models\FluxTransition;
use App\Models\Service;
use Illuminate\Http\Request;

class FluxTransitionController extends Controller
{
    public function index()
    {
        $transitions = FluxTransition::with('sourceService', 'destinationService')
            ->orderBy('ordre')
            ->orderBy('id')
            ->get();

        $services = Service::orderBy('nom')->get();

        return view('admin.flux-transitions.index', compact('transitions', 'services'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'service_source_id'      => 'nullable|exists:services,id',
            'service_destination_id' => 'required|exists:services,id',
            'action'                 => 'required|string|max:100',
        ]);

        $exists = FluxTransition::where('service_source_id', $request->service_source_id ?: null)
            ->where('service_destination_id', $request->service_destination_id)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Cette transition existe déjà.');
        }

        FluxTransition::create([
            'service_source_id'      => $request->service_source_id ?: null,
            'service_destination_id' => $request->service_destination_id,
            'action'                 => $request->action,
        ]);

        return redirect()->back()->with('success', 'Transition ajoutée.');
    }

    public function update(Request $request, FluxTransition $fluxTransition)
    {
        $request->validate([
            'action'       => 'required|string|max:100',
            'type_demande' => 'nullable|string',
        ]);

        $fluxTransition->update([
            'action'       => $request->action,
            'type_demande' => $request->type_demande ?: null,
        ]);

        return redirect()->back()->with('success', 'Transition mise à jour.');
    }

    public function moveUp(FluxTransition $fluxTransition)
    {
        $previous = FluxTransition::where('ordre', '<', $fluxTransition->ordre)
            ->orderBy('ordre', 'desc')
            ->first();

        if ($previous) {
            [$fluxTransition->ordre, $previous->ordre] = [$previous->ordre, $fluxTransition->ordre];
            $fluxTransition->save();
            $previous->save();
        }

        return redirect()->back();
    }

    public function moveDown(FluxTransition $fluxTransition)
    {
        $next = FluxTransition::where('ordre', '>', $fluxTransition->ordre)
            ->orderBy('ordre')
            ->first();

        if ($next) {
            [$fluxTransition->ordre, $next->ordre] = [$next->ordre, $fluxTransition->ordre];
            $fluxTransition->save();
            $next->save();
        }

        return redirect()->back();
    }

    public function destroy(FluxTransition $fluxTransition)
    {
        $fluxTransition->delete();

        return redirect()->back()->with('success', 'Transition supprimée.');
    }
}

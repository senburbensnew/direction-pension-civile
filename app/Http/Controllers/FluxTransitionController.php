<?php

namespace App\Http\Controllers;

use App\Models\FluxTransition;
use App\Models\RequiredCircuitService;
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

        $requiredServices = RequiredCircuitService::with('service')
            ->orderBy('type_demande')
            ->orderBy('service_id')
            ->get();

        return view('admin.flux-transitions.index', compact('transitions', 'services', 'requiredServices'));
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
        $isInit = $fluxTransition->service_source_id === null;

        $rules = [
            'action'                 => 'required|string|max:100',
            'type_demande'           => 'nullable|string',
            'service_destination_id' => 'required|exists:services,id',
        ];

        if (! $isInit) {
            $rules['service_source_id'] = 'nullable|exists:services,id';
        }

        $request->validate($rules);

        $data = [
            'action'         => $request->action,
            'type_demande'   => $request->type_demande ?: null,
            'service_destination_id' => $request->service_destination_id,
        ];

        if (! $isInit) {
            $data['service_source_id'] = $request->service_source_id ?: null;
        }

        $fluxTransition->update($data);

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

    public function storeRequired(Request $request)
    {
        $request->validate([
            'service_id'   => 'required|exists:services,id',
            'type_demande' => 'nullable|string|max:100',
        ]);

        $exists = RequiredCircuitService::where('service_id', $request->service_id)
            ->where('type_demande', $request->type_demande ?: null)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Cette entrée existe déjà.');
        }

        RequiredCircuitService::create([
            'service_id'   => $request->service_id,
            'type_demande' => $request->type_demande ?: null,
        ]);

        return redirect()->back()->with('success', 'Étape obligatoire ajoutée.');
    }

    public function destroyRequired(RequiredCircuitService $requiredCircuitService)
    {
        $requiredCircuitService->delete();

        return redirect()->back()->with('success', 'Étape obligatoire supprimée.');
    }

    public function destroy(FluxTransition $fluxTransition)
    {
        if ($fluxTransition->service_source_id === null) {
            return redirect()->back()->with('error', 'La transition de soumission initiale ne peut pas être supprimée.');
        }

        $fluxTransition->delete();

        return redirect()->back()->with('success', 'Transition supprimée.');
    }
}

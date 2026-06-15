<?php

namespace App\Http\Controllers;

use App\Models\WorkflowStep;
use Illuminate\Http\Request;

class WorkflowStepController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'code'         => ['required', 'string', 'max:80', 'regex:/^[A-Z0-9_]+$/'],
            'service_id'   => ['nullable', 'exists:services,id'],
            'status_id'    => ['nullable', 'exists:etats,id'],
            'type_demande' => ['nullable', 'string', 'max:100'],
            'nom'          => ['required', 'string', 'max:150'],
            'description'  => ['nullable', 'string', 'max:500'],
            'ordre'        => ['required', 'integer', 'min:0'],
            'type_noeud'   => ['required', 'string', 'in:initial,intermediaire,terminal'],
        ]);

        $type = $request->type_demande ?: null;
        $code = strtoupper($request->code);

        if (WorkflowStep::where('code', $code)->where('type_demande', $type)->exists()) {
            return redirect()->back()->with('error', "Un nœud avec le code « {$code} » existe déjà pour ce type.");
        }

        WorkflowStep::create([
            'code'         => $code,
            'service_id'   => $request->service_id ?: null,
            'type_demande' => $type,
            'nom'          => $request->nom,
            'description'  => $request->description,
            'ordre'        => $request->ordre,
            'type_noeud'   => $request->type_noeud,
        ]);

        return redirect()->back()->with('success', 'Étape ajoutée.');
    }

    public function clone(Request $request, WorkflowStep $workflowStep)
    {
        $type = $request->input('type_demande') ?: null;

        if (WorkflowStep::where('code', $workflowStep->code)->where('type_demande', $type)->exists()) {
            return redirect()->back()->with('error', "Le nœud « {$workflowStep->code} » existe déjà dans ce circuit.");
        }

        WorkflowStep::create([
            'code'         => $workflowStep->code,
            'nom'          => $workflowStep->nom,
            'description'  => $workflowStep->description,
            'service_id'   => $workflowStep->service_id,
            'status_id'    => $workflowStep->status_id,
            'type_demande' => $type,
            'ordre'        => $workflowStep->ordre,
            'type_noeud'   => $workflowStep->type_noeud,
        ]);

        return redirect()->back()->with('success', "Nœud « {$workflowStep->nom} » ajouté au circuit.");
    }

    public function update(Request $request, WorkflowStep $workflowStep)
    {
        abort_if($workflowStep->isInitial(), 403, 'Cet état système ne peut pas être modifié.');
        $request->validate([
            'nom'         => ['required', 'string', 'max:150'],
            'service_id'  => ['nullable', 'exists:services,id'],
            'description' => ['nullable', 'string', 'max:500'],
            'ordre'       => ['required', 'integer', 'min:0'],
            'type_noeud'  => ['required', 'string', 'in:initial,intermediaire,terminal'],
        ]);

        $workflowStep->update([
            'nom'         => $request->nom,
            'service_id'  => $request->service_id ?: null,
            'description' => $request->description,
            'ordre'       => $request->ordre,
            'type_noeud'  => $request->type_noeud,
        ]);

        return redirect()->back()->with('success', 'Étape mise à jour.');
    }

    public function destroy(WorkflowStep $workflowStep)
    {
        abort_if($workflowStep->isInitial(), 403, 'Cet état système ne peut pas être supprimé.');
        $workflowStep->delete();
        return redirect()->back()->with('success', 'Étape supprimée.');
    }
}

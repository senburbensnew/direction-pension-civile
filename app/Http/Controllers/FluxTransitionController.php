<?php

namespace App\Http\Controllers;

use App\Enums\TypeDemandeEnum;
use App\Models\RequiredCircuitService;
use App\Models\Service;
use App\Models\ServiceSla;
use App\Models\WorkflowStep;
use App\Models\WorkflowStepTransition;
use Illuminate\Http\Request;

class FluxTransitionController extends Controller
{
    public function index(Request $request)
    {
        $selectedType = $request->query('type'); // null = common/global view

        // When a specific type is selected: show only type-specific steps (complete circuit).
        // When no type: show the global (type_demande = null) common circuit.
        $steps = WorkflowStep::with('service')
            ->when(
                $selectedType,
                fn($q) => $q->where('type_demande', $selectedType),
                fn($q) => $q->whereNull('type_demande')
            )
            ->orderBy('ordre')
            ->get();

        $stepTransitions = WorkflowStepTransition::with('fromStep.service', 'toStep.service')
            ->when($steps->isNotEmpty(), fn($q) => $q->where(
                fn($q2) => $q2->whereIn('from_step_id', $steps->pluck('id'))->orWhereNull('from_step_id')
            ))
            ->orderBy('ordre')
            ->get()
            ->filter(fn($t) => $steps->contains('id', $t->to_step_id))
            ->values();

        $services = Service::orderBy('nom')->get();

        $requiredServices = RequiredCircuitService::with('service')
            ->when($selectedType, fn($q) => $q->where(fn($q2) =>
                $q2->whereNull('type_demande')->orWhere('type_demande', $selectedType)
            ))
            ->orderBy('type_demande')
            ->orderBy('service_id')
            ->get();

        $slaRules = ServiceSla::with('service')
            ->orderBy('service_id')
            ->orderBy('type_demande')
            ->get();

        $typeDemandeOptions = TypeDemandeEnum::cases();

        // Steps from other circuits not already present in the current one (by code+type)
        $currentCodes = $steps->pluck('code')->unique();
        $reusableSteps = WorkflowStep::with('service')
            ->whereNotIn('code', $currentCodes)
            ->when($selectedType, fn($q) => $q->where('type_demande', '!=', $selectedType))
            ->orderBy('type_demande')
            ->orderBy('ordre')
            ->get();

        return view('admin.flux-transitions.index', compact(
            'steps', 'stepTransitions', 'services',
            'requiredServices', 'slaRules',
            'selectedType', 'typeDemandeOptions',
            'reusableSteps'
        ));
    }

    /* ── Step-transition CRUD ─────────────────────────────────────── */

    public function storeStepTransition(Request $request)
    {
        $request->validate([
            'from_step_id'  => 'nullable|exists:workflow_steps,id',
            'to_step_id'    => 'required|exists:workflow_steps,id|different:from_step_id',
            'action'        => 'required|string|max:100',
            'is_urgent_only'=> 'boolean',
        ]);

        $from = $request->from_step_id ?: null;

        $toStep = WorkflowStep::findOrFail($request->to_step_id);
        if ($toStep->isInitial()) {
            return redirect()->back()->with('error', 'Un nœud initial ne peut pas être une destination de transition.');
        }

        if ($from) {
            $fromStep = WorkflowStep::findOrFail($from);
            if ($fromStep->isTerminal()) {
                return redirect()->back()->with('error', 'Un nœud terminal ne peut pas être une source de transition.');
            }
        }

        if (WorkflowStepTransition::where('from_step_id', $from)->where('to_step_id', $request->to_step_id)->exists()) {
            return redirect()->back()->with('error', 'Cette transition existe déjà.');
        }

        $maxOrdre = WorkflowStepTransition::where('from_step_id', $from)->max('ordre') ?? 0;

        WorkflowStepTransition::create([
            'from_step_id'   => $from,
            'to_step_id'     => $request->to_step_id,
            'action'         => $request->action,
            'is_urgent_only' => $request->boolean('is_urgent_only'),
            'ordre'          => $maxOrdre + 10,
        ]);

        return redirect()->back()->with('success', 'Transition ajoutée.');
    }

    public function updateStepTransition(Request $request, WorkflowStepTransition $stepTransition)
    {
        $request->validate([
            'action'         => 'required|string|max:100',
            'is_urgent_only' => 'boolean',
        ]);

        $stepTransition->update([
            'action'         => $request->action,
            'is_urgent_only' => $request->boolean('is_urgent_only'),
        ]);

        return redirect()->back()->with('success', 'Transition mise à jour.');
    }

    public function destroyStepTransition(WorkflowStepTransition $stepTransition)
    {
        $stepTransition->delete();
        return redirect()->back()->with('success', 'Transition supprimée.');
    }

    public function moveUpStepTransition(WorkflowStepTransition $stepTransition)
    {
        $previous = WorkflowStepTransition::where('from_step_id', $stepTransition->from_step_id)
            ->where('ordre', '<', $stepTransition->ordre)
            ->orderBy('ordre', 'desc')
            ->first();

        if ($previous) {
            [$stepTransition->ordre, $previous->ordre] = [$previous->ordre, $stepTransition->ordre];
            $stepTransition->save();
            $previous->save();
        }

        return redirect()->back();
    }

    public function moveDownStepTransition(WorkflowStepTransition $stepTransition)
    {
        $next = WorkflowStepTransition::where('from_step_id', $stepTransition->from_step_id)
            ->where('ordre', '>', $stepTransition->ordre)
            ->orderBy('ordre')
            ->first();

        if ($next) {
            [$stepTransition->ordre, $next->ordre] = [$next->ordre, $stepTransition->ordre];
            $stepTransition->save();
            $next->save();
        }

        return redirect()->back();
    }

    /* ── Required circuit services ────────────────────────────────── */

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

    /* ── SLA ──────────────────────────────────────────────────────── */

    public function storeSla(Request $request)
    {
        $request->validate([
            'service_id'   => 'required|exists:services,id',
            'type_demande' => 'nullable|string|max:100',
            'delai_jours'  => 'required|integer|min:1|max:365',
        ]);

        ServiceSla::updateOrCreate(
            ['service_id' => $request->service_id, 'type_demande' => $request->type_demande ?: null],
            ['delai_jours' => $request->delai_jours]
        );

        return redirect()->back()->with('success', 'SLA enregistré.');
    }

    public function destroySla(ServiceSla $serviceSla)
    {
        $serviceSla->delete();
        return redirect()->back()->with('success', 'SLA supprimé.');
    }
}

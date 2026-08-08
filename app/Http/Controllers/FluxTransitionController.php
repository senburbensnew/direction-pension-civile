<?php

namespace App\Http\Controllers;

use App\Enums\TypeDemandeEnum;
use App\Models\RequiredCircuitService;
use App\Models\Service;
use App\Models\ServiceSla;
use App\Models\WorkflowStep;
use App\Models\WorkflowStepTransition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class FluxTransitionController extends Controller
{
    public function index(Request $request)
    {
        $rawType = $request->query('type');
        $selectedType = null;

        if ($rawType !== null && $rawType !== '') {
            $enum = TypeDemandeEnum::tryFrom($rawType);
            if (!$enum) {
                return redirect()
                    ->route('admin.flux-transitions.index')
                    ->with('error', 'Type de demande invalide.');
            }
            $selectedType = $enum->value;
        }

        $steps = WorkflowStep::with('service')
            ->when(
                $selectedType,
                fn ($q) => $q->where('type_demande', $selectedType),
                fn ($q) => $q->whereNull('type_demande')
            )
            ->orderBy('ordre')
            ->get();

        $stepTransitions = WorkflowStepTransition::with('fromStep.service', 'toStep.service')
            ->when($steps->isNotEmpty(), fn ($q) => $q->where(
                fn ($q2) => $q2->whereIn('from_step_id', $steps->pluck('id'))->orWhereNull('from_step_id')
            ))
            ->orderBy('ordre')
            ->get()
            ->filter(fn ($t) => $steps->contains('id', $t->to_step_id))
            ->values();

        $services = Service::orderBy('nom')->get();

        $requiredServices = RequiredCircuitService::with('service')
            ->when($selectedType, fn ($q) => $q->where(fn ($q2) =>
                $q2->whereNull('type_demande')->orWhere('type_demande', $selectedType)
            ))
            ->orderBy('type_demande')
            ->orderBy('service_id')
            ->get();

        $slaRules = ServiceSla::with('service')
            ->when($selectedType, fn ($q) => $q->where(fn ($q2) =>
                $q2->whereNull('type_demande')->orWhere('type_demande', $selectedType)
            ))
            ->orderBy('service_id')
            ->orderBy('type_demande')
            ->get();

        $typeDemandeOptions = TypeDemandeEnum::cases();

        $currentCodes = $steps->pluck('code')->unique();
        $reusableSteps = WorkflowStep::with('service')
            ->whereNotIn('code', $currentCodes)
            ->when($selectedType, fn ($q) => $q->where('type_demande', '!=', $selectedType))
            ->orderBy('type_demande')
            ->orderBy('ordre')
            ->get();

        $healthIssues = $this->buildHealthIssues($steps, $stepTransitions, $requiredServices);

        return view('admin.flux-transitions.index', compact(
            'steps', 'stepTransitions', 'services',
            'requiredServices', 'slaRules',
            'selectedType', 'typeDemandeOptions',
            'reusableSteps', 'healthIssues'
        ));
    }

    /* ── Step-transition CRUD ─────────────────────────────────────── */

    public function storeStepTransition(Request $request)
    {
        $request->validate([
            'from_step_id'   => 'nullable|exists:workflow_steps,id',
            'to_step_id'     => 'required|exists:workflow_steps,id|different:from_step_id',
            'action'         => 'required|string|max:100',
            'is_urgent_only' => 'boolean',
            'type_demande'   => 'nullable|string|max:100',
        ]);

        $from = $request->from_step_id ?: null;

        $toStep = WorkflowStep::findOrFail($request->to_step_id);
        if ($toStep->isDraftEntry()) {
            return $this->redirectToIndex($request)
                ->with('error', 'Le brouillon ne peut pas être une destination de transition.');
        }

        if ($from) {
            $fromStep = WorkflowStep::findOrFail($from);
            if ($fromStep->isTerminal()) {
                return $this->redirectToIndex($request)
                    ->with('error', 'Un nœud terminal ne peut pas être une source de transition.');
            }
        }

        if (WorkflowStepTransition::where('from_step_id', $from)->where('to_step_id', $request->to_step_id)->exists()) {
            return $this->redirectToIndex($request)->with('error', 'Cette transition existe déjà.');
        }

        $maxOrdre = WorkflowStepTransition::where('from_step_id', $from)->max('ordre') ?? 0;

        WorkflowStepTransition::create([
            'from_step_id'   => $from,
            'to_step_id'     => $request->to_step_id,
            'action'         => $request->action,
            'is_urgent_only' => $request->boolean('is_urgent_only'),
            'ordre'          => $maxOrdre + 10,
        ]);

        return $this->redirectToIndex($request)->with('success', 'Transition ajoutée.');
    }

    public function updateStepTransition(Request $request, WorkflowStepTransition $stepTransition)
    {
        $request->validate([
            'action'         => 'required|string|max:100',
            'is_urgent_only' => 'boolean',
            'type_demande'   => 'nullable|string|max:100',
        ]);

        $stepTransition->update([
            'action'         => $request->action,
            'is_urgent_only' => $request->boolean('is_urgent_only'),
        ]);

        return $this->redirectToIndex($request)->with('success', 'Transition mise à jour.');
    }

    public function destroyStepTransition(Request $request, WorkflowStepTransition $stepTransition)
    {
        $stepTransition->delete();
        return $this->redirectToIndex($request)->with('success', 'Transition supprimée.');
    }

    public function moveUpStepTransition(Request $request, WorkflowStepTransition $stepTransition)
    {
        $previous = WorkflowStepTransition::where('from_step_id', $stepTransition->from_step_id)
            ->where('ordre', '<', $stepTransition->ordre)
            ->orderBy('ordre', 'desc')
            ->first();

        if ($previous) {
            [$stepTransition->ordre, $previous->ordre] = [$previous->ordre, $stepTransition->ordre];
            $stepTransition->save();
            $previous->save();

            return $this->redirectToIndex($request)->with('success', 'Ordre mis à jour.');
        }

        return $this->redirectToIndex($request);
    }

    public function moveDownStepTransition(Request $request, WorkflowStepTransition $stepTransition)
    {
        $next = WorkflowStepTransition::where('from_step_id', $stepTransition->from_step_id)
            ->where('ordre', '>', $stepTransition->ordre)
            ->orderBy('ordre')
            ->first();

        if ($next) {
            [$stepTransition->ordre, $next->ordre] = [$next->ordre, $stepTransition->ordre];
            $stepTransition->save();
            $next->save();

            return $this->redirectToIndex($request)->with('success', 'Ordre mis à jour.');
        }

        return $this->redirectToIndex($request);
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
            return $this->redirectToIndex($request)->with('error', 'Cette entrée existe déjà.');
        }

        RequiredCircuitService::create([
            'service_id'   => $request->service_id,
            'type_demande' => $request->type_demande ?: null,
        ]);

        return $this->redirectToIndex($request)->with('success', 'Étape obligatoire ajoutée.');
    }

    public function destroyRequired(Request $request, RequiredCircuitService $requiredCircuitService)
    {
        $requiredCircuitService->delete();
        return $this->redirectToIndex($request)->with('success', 'Étape obligatoire supprimée.');
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

        return $this->redirectToIndex($request)->with('success', 'SLA enregistré.');
    }

    public function destroySla(Request $request, ServiceSla $serviceSla)
    {
        $serviceSla->delete();
        return $this->redirectToIndex($request)->with('success', 'SLA supprimé.');
    }

    private function redirectToIndex(Request $request): RedirectResponse
    {
        $type = $request->input('type_demande') ?: $request->query('type');
        $params = [];
        if ($type) {
            $params['type'] = $type;
        }

        return redirect()->route('admin.flux-transitions.index', $params);
    }

    /**
     * @return array<int, array{level: string, message: string}>
     */
    private function buildHealthIssues(Collection $steps, Collection $transitions, Collection $requiredServices): array
    {
        $issues = [];

        if ($steps->isEmpty()) {
            return [[
                'level'   => 'warning',
                'message' => 'Aucun état défini pour ce circuit. Ajoutez des nœuds pour commencer.',
            ]];
        }

        $connectedIds = collect();
        foreach ($transitions as $t) {
            if ($t->from_step_id) {
                $connectedIds->push($t->from_step_id);
            }
            $connectedIds->push($t->to_step_id);
        }
        $connectedIds = $connectedIds->unique();

        $orphans = $steps->filter(fn (WorkflowStep $s) =>
            !$s->isDraftEntry() && !$connectedIds->contains($s->id)
        );
        if ($orphans->isNotEmpty()) {
            $names = $orphans->pluck('nom')->take(5)->implode(', ');
            $extra = $orphans->count() > 5 ? '…' : '';
            $issues[] = [
                'level'   => 'warning',
                'message' => 'Nœud(s) orphelin(s) sans transition : ' . $names . $extra . '.',
            ];
        }

        $soumise = $steps->firstWhere('code', 'SOUMISE');
        if ($soumise) {
            $directionId = Service::where('code', Service::DIRECTION)->value('id');
            if ($directionId && (int) $soumise->service_id !== (int) $directionId) {
                $issues[] = [
                    'level'   => 'error',
                    'message' => 'L’étape SOUMISE doit être rattachée au service Direction — toute demande y est reçue au départ.',
                ];
            }
            if (!$transitions->contains(fn ($t) => (int) $t->from_step_id === (int) $soumise->id)) {
                $issues[] = [
                    'level'   => 'error',
                    'message' => 'Aucune transition sortante depuis « Soumission » (SOUMISE). Les dossiers ne pourront pas être transférés.',
                ];
            }

            $secretariatId = Service::where('code', Service::SECRETARIAT)->value('id');
            $hasSecretariatDispatch = $secretariatId && $transitions->contains(function ($t) use ($soumise, $secretariatId) {
                return (int) $t->from_step_id === (int) $soumise->id
                    && (int) ($t->toStep?->service_id) === (int) $secretariatId;
            });
            if (!$hasSecretariatDispatch) {
                $issues[] = [
                    'level'   => 'warning',
                    'message' => 'Aucune arête SOUMISE → Secrétariat. Après annotation, les dossiers ne seront pas dispatchés automatiquement au Secrétariat.',
                ];
            }
        }

        $hasSubmitEdge = $transitions->contains(function ($t) {
            $toCode = $t->toStep?->code;
            if ($toCode !== 'SOUMISE') {
                return false;
            }

            return $t->from_step_id === null || $t->fromStep?->code === 'BROUILLON';
        });
        if ($soumise && !$hasSubmitEdge) {
            $issues[] = [
                'level'   => 'warning',
                'message' => 'Aucune arête de soumission (Brouillon → Soumise ou Soumission initiale → Soumise).',
            ];
        }

        $terminals = $steps->filter(fn (WorkflowStep $s) => $s->isTerminal());
        if ($terminals->isNotEmpty() && $soumise) {
            $reachable = $this->reachableStepIds($soumise->id, $transitions);
            $unreachableTerminals = $terminals->filter(fn (WorkflowStep $s) => !$reachable->contains($s->id));
            if ($unreachableTerminals->isNotEmpty()) {
                $names = $unreachableTerminals->pluck('nom')->implode(', ');
                $issues[] = [
                    'level'   => 'warning',
                    'message' => 'Terminal(aux) inatteignable(s) depuis SOUMISE : ' . $names . '.',
                ];
            }
        } elseif ($terminals->isEmpty()) {
            $issues[] = [
                'level'   => 'info',
                'message' => 'Aucun nœud terminal (Approuvée, Rejetée, etc.) dans ce circuit.',
            ];
        }

        if ($requiredServices->isEmpty()) {
            $issues[] = [
                'level'   => 'info',
                'message' => 'Aucune étape obligatoire : l’approbation ne vérifiera pas le passage par un service donné.',
            ];
        }

        return $issues;
    }

    private function reachableStepIds(int $startId, Collection $transitions): Collection
    {
        $adj = [];
        foreach ($transitions as $t) {
            if ($t->from_step_id) {
                $adj[$t->from_step_id][] = $t->to_step_id;
            }
        }

        $seen = collect([$startId]);
        $queue = [$startId];
        while ($queue) {
            $current = array_shift($queue);
            foreach ($adj[$current] ?? [] as $next) {
                if (!$seen->contains($next)) {
                    $seen->push($next);
                    $queue[] = $next;
                }
            }
        }

        return $seen;
    }
}

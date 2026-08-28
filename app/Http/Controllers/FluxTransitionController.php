<?php

namespace App\Http\Controllers;

use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\RequiredCircuitService;
use App\Models\Service;
use App\Models\ServiceSla;
use App\Models\StepRequiredDocument;
use App\Models\TypeDemande;
use App\Models\WorkflowStep;
use App\Models\WorkflowStepTransition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FluxTransitionController extends Controller
{
    public function index(Request $request)
    {
        $typeDemandeOptions = $this->typeDemandeOptions();
        $rawType = $request->query('type');

        if ($rawType === null || $rawType === '') {
            $first = $typeDemandeOptions->first();
            if (! $first) {
                abort(500, 'Aucun type de demande n’est défini.');
            }

            return redirect()->route('admin.flux-transitions.index', ['type' => $first->value]);
        }

        if (! TypeDemande::isKnown($rawType)) {
            $first = $typeDemandeOptions->first();

            return redirect()
                ->route('admin.flux-transitions.index', $first ? ['type' => $first->value] : [])
                ->with('error', 'Type de demande invalide.');
        }

        $selectedType = $rawType;

        $steps = WorkflowStep::with('service')
            ->where('type_demande', $selectedType)
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
            ->when($selectedType, fn ($q) => $q->where(fn ($q2) => $q2->whereNull('type_demande')->orWhere('type_demande', $selectedType)
            ))
            ->orderBy('type_demande')
            ->orderBy('service_id')
            ->get();

        $slaRules = ServiceSla::with('service')
            ->when($selectedType, fn ($q) => $q->where(fn ($q2) => $q2->whereNull('type_demande')->orWhere('type_demande', $selectedType)
            ))
            ->orderBy('service_id')
            ->orderBy('type_demande')
            ->get();

        $currentCodes = $steps->pluck('code')->unique();
        $reusableSteps = WorkflowStep::with('service')
            ->whereNotIn('code', $currentCodes)
            ->when($selectedType, fn ($q) => $q->where('type_demande', '!=', $selectedType))
            ->orderBy('type_demande')
            ->orderBy('ordre')
            ->get();

        $healthIssues = $this->buildHealthIssues($steps, $stepTransitions, $requiredServices);
        $canDeleteSelectedType = TypeDemande::isCustom($selectedType);

        return view('admin.flux-transitions.index', compact(
            'steps', 'stepTransitions', 'services',
            'requiredServices', 'slaRules',
            'selectedType', 'typeDemandeOptions',
            'reusableSteps', 'healthIssues',
            'canDeleteSelectedType'
        ));
    }

    public function storeType(Request $request)
    {
        $knownCodes = $this->typeDemandeOptions()->pluck('value')->all();

        $validated = $request->validate([
            'type_code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Z0-9_]+$/',
                'unique:types_demandes,code',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (TypeDemandeEnum::tryFrom($value)) {
                        $fail('Ce code correspond déjà à un type de demande existant.');
                    }
                },
            ],
            'type_label' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'clone_from' => ['nullable', 'string', Rule::in(array_merge($knownCodes, ['__global__']))],
        ]);

        $code = strtoupper($validated['type_code']);

        DB::transaction(function () use ($validated, $code) {
            TypeDemande::create([
                'code' => $code,
                'label' => $validated['type_label'],
                'description' => $validated['description'] ?? null,
                'active' => true,
            ]);

            $cloneFrom = $validated['clone_from'] ?? null;
            if ($cloneFrom) {
                $this->cloneCircuit($cloneFrom === '__global__' ? null : $cloneFrom, $code);
            }
        });

        return redirect()
            ->route('admin.flux-transitions.index', ['type' => $code])
            ->with('success', 'Type de demande créé.');
    }

    public function destroyType(string $typeCode)
    {
        $code = strtoupper($typeCode);

        if (! TypeDemande::isCustom($code)) {
            return redirect()
                ->route('admin.flux-transitions.index', ['type' => $code])
                ->with('error', 'Ce type de demande ne peut pas être supprimé.');
        }

        if (Demande::where('type', $code)->exists()) {
            return redirect()
                ->route('admin.flux-transitions.index', ['type' => $code])
                ->with('error', 'Impossible de supprimer ce type : des dossiers l’utilisent encore.');
        }

        $label = TypeDemande::labelFor($code);

        DB::transaction(function () use ($code) {
            $stepIds = WorkflowStep::where('type_demande', $code)->pluck('id');
            if ($stepIds->isNotEmpty()) {
                StepRequiredDocument::whereIn('workflow_step_id', $stepIds)->delete();
                WorkflowStep::whereIn('id', $stepIds)->delete();
            }

            RequiredCircuitService::where('type_demande', $code)->delete();
            ServiceSla::where('type_demande', $code)->delete();
            StepRequiredDocument::where('type_demande', $code)->delete();
            TypeDemande::where('code', $code)->delete();
        });

        $fallback = $this->typeDemandeOptions()->first();

        return redirect()
            ->route('admin.flux-transitions.index', $fallback ? ['type' => $fallback->value] : [])
            ->with('success', 'Type « '.$label.' » supprimé.');
    }

    /* ── Step-transition CRUD ─────────────────────────────────────── */

    public function storeStepTransition(Request $request)
    {
        $request->validate([
            'from_step_id' => 'nullable|exists:workflow_steps,id',
            'to_step_id' => 'required|exists:workflow_steps,id|different:from_step_id',
            'action' => 'required|string|max:100',
            'is_urgent_only' => 'boolean',
            'type_demande' => 'nullable|string|max:100',
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
            'from_step_id' => $from,
            'to_step_id' => $request->to_step_id,
            'action' => $request->action,
            'is_urgent_only' => $request->boolean('is_urgent_only'),
            'ordre' => $maxOrdre + 10,
        ]);

        return $this->redirectToIndex($request)->with('success', 'Transition ajoutée.');
    }

    public function updateStepTransition(Request $request, WorkflowStepTransition $stepTransition)
    {
        $request->validate([
            'action' => 'required|string|max:100',
            'is_urgent_only' => 'boolean',
            'type_demande' => 'nullable|string|max:100',
        ]);

        $stepTransition->update([
            'action' => $request->action,
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
            'service_id' => 'required|exists:services,id',
            'type_demande' => 'nullable|string|max:100',
        ]);

        $exists = RequiredCircuitService::where('service_id', $request->service_id)
            ->where('type_demande', $request->type_demande ?: null)
            ->exists();

        if ($exists) {
            return $this->redirectToIndex($request)->with('error', 'Cette entrée existe déjà.');
        }

        RequiredCircuitService::create([
            'service_id' => $request->service_id,
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
            'service_id' => 'required|exists:services,id',
            'type_demande' => 'nullable|string|max:100',
            'delai_jours' => 'required|integer|min:1|max:365',
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
                'level' => 'warning',
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

        $orphans = $steps->filter(fn (WorkflowStep $s) => ! $s->isDraftEntry() && ! $connectedIds->contains($s->id)
        );
        if ($orphans->isNotEmpty()) {
            $names = $orphans->pluck('nom')->take(5)->implode(', ');
            $extra = $orphans->count() > 5 ? '…' : '';
            $issues[] = [
                'level' => 'warning',
                'message' => 'Nœud(s) orphelin(s) sans transition : '.$names.$extra.'.',
            ];
        }

        $soumise = $steps->firstWhere('code', 'SOUMISE');
        if ($soumise) {
            $directionId = Service::where('code', Service::DIRECTION)->value('id');
            if ($directionId && (int) $soumise->service_id !== (int) $directionId) {
                $issues[] = [
                    'level' => 'error',
                    'message' => 'L’étape SOUMISE doit être rattachée au service Direction — toute demande y est reçue au départ.',
                ];
            }
            if (! $transitions->contains(fn ($t) => (int) $t->from_step_id === (int) $soumise->id)) {
                $issues[] = [
                    'level' => 'error',
                    'message' => 'Aucune transition sortante depuis « Soumission » (SOUMISE). Les dossiers ne pourront pas être transférés.',
                ];
            }

            $secretariatId = Service::where('code', Service::SECRETARIAT)->value('id');
            $hasSecretariatDispatch = $secretariatId && $transitions->contains(function ($t) use ($soumise, $secretariatId) {
                return (int) $t->from_step_id === (int) $soumise->id
                    && (int) ($t->toStep?->service_id) === (int) $secretariatId;
            });
            if (! $hasSecretariatDispatch) {
                $issues[] = [
                    'level' => 'warning',
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
        if ($soumise && ! $hasSubmitEdge) {
            $issues[] = [
                'level' => 'warning',
                'message' => 'Aucune arête de soumission (Brouillon → Soumise ou Soumission initiale → Soumise).',
            ];
        }

        $terminals = $steps->filter(fn (WorkflowStep $s) => $s->isTerminal());
        if ($terminals->isNotEmpty() && $soumise) {
            $reachable = $this->reachableStepIds($soumise->id, $transitions);
            $unreachableTerminals = $terminals->filter(fn (WorkflowStep $s) => ! $reachable->contains($s->id));
            if ($unreachableTerminals->isNotEmpty()) {
                $names = $unreachableTerminals->pluck('nom')->implode(', ');
                $issues[] = [
                    'level' => 'warning',
                    'message' => 'Terminal(aux) inatteignable(s) depuis SOUMISE : '.$names.'.',
                ];
            }
        } elseif ($terminals->isEmpty()) {
            $issues[] = [
                'level' => 'info',
                'message' => 'Aucun nœud terminal (Approuvée, Rejetée, etc.) dans ce circuit.',
            ];
        }

        if ($requiredServices->isEmpty()) {
            $issues[] = [
                'level' => 'info',
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
                if (! $seen->contains($next)) {
                    $seen->push($next);
                    $queue[] = $next;
                }
            }
        }

        return $seen;
    }

    /**
     * @return Collection<int, object{value: string, label: string}>
     */
    private function typeDemandeOptions(): Collection
    {
        $dbByCode = TypeDemande::query()->orderBy('label')->get()->keyBy('code');

        $options = collect(TypeDemandeEnum::cases())->map(fn (TypeDemandeEnum $e) => (object) [
            'value' => $e->value,
            'label' => $dbByCode->get($e->value)?->label ?? $e->label(),
        ]);

        $extras = $dbByCode
            ->reject(fn (TypeDemande $t) => TypeDemandeEnum::tryFrom($t->code) !== null)
            ->map(fn (TypeDemande $t) => (object) [
                'value' => $t->code,
                'label' => $t->label,
            ]);

        return $options->concat($extras->values());
    }

    private function cloneCircuit(?string $sourceType, string $targetType): void
    {
        $sourceSteps = WorkflowStep::query()
            ->when(
                $sourceType,
                fn ($q) => $q->where('type_demande', $sourceType),
                fn ($q) => $q->whereNull('type_demande')
            )
            ->orderBy('ordre')
            ->get();

        if ($sourceSteps->isEmpty()) {
            return;
        }

        $idMap = [];
        $seenCodes = [];
        foreach ($sourceSteps as $step) {
            if (isset($seenCodes[$step->code])) {
                continue;
            }
            $seenCodes[$step->code] = true;

            if (WorkflowStep::where('code', $step->code)->where('type_demande', $targetType)->exists()) {
                continue;
            }

            $clone = $step->replicate();
            $clone->type_demande = $targetType;
            $clone->save();
            $idMap[$step->id] = $clone->id;
        }

        $sourceIds = $sourceSteps->pluck('id');
        $transitions = WorkflowStepTransition::query()
            ->whereIn('to_step_id', $sourceIds)
            ->where(function ($q) use ($sourceIds) {
                $q->whereNull('from_step_id')->orWhereIn('from_step_id', $sourceIds);
            })
            ->get();

        foreach ($transitions as $transition) {
            $toId = $idMap[$transition->to_step_id] ?? null;
            if (! $toId) {
                continue;
            }

            $fromId = $transition->from_step_id
                ? ($idMap[$transition->from_step_id] ?? null)
                : null;

            if ($transition->from_step_id && ! $fromId) {
                continue;
            }

            WorkflowStepTransition::create([
                'from_step_id' => $fromId,
                'to_step_id' => $toId,
                'action' => $transition->action,
                'is_urgent_only' => $transition->is_urgent_only,
                'ordre' => $transition->ordre,
                'required_permission' => $transition->required_permission,
                'guard_conditions' => $transition->guard_conditions,
            ]);
        }
    }
}

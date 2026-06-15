<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as BaseCollection;

class WorkflowStepTransition extends Model
{
    protected $fillable = [
        'from_step_id',
        'to_step_id',
        'action',
        'is_urgent_only',
        'ordre',
        'required_permission',
        'guard_conditions',
    ];

    protected $casts = [
        'is_urgent_only'   => 'boolean',
        'guard_conditions' => 'array',
    ];

    public function fromStep()
    {
        return $this->belongsTo(WorkflowStep::class, 'from_step_id');
    }

    public function toStep()
    {
        return $this->belongsTo(WorkflowStep::class, 'to_step_id');
    }

    /**
     * Whether this transition can be executed by the given user for the given demande.
     * Checks the required Spatie permission and each guard condition in guard_conditions.
     *
     * Supported guard keys:
     *   - docs_complets (bool)    — all step-required documents are attached
     *   - avis_complets (bool)    — no affectation is still EN_ATTENTE
     *   - avis_favorables (bool)  — all affectations are TERMINE (responded favorably)
     */
    public function canExecute(Demande $demande, User $user): bool
    {
        if ($this->required_permission && !$user->can($this->required_permission)) {
            return false;
        }

        foreach ($this->guard_conditions ?? [] as $guard => $expected) {
            $passes = match ($guard) {
                'docs_complets'   => $this->guardDocsComplets($demande),
                'avis_complets'   => $demande->affectations()->where('statut', 'EN_ATTENTE')->doesntExist(),
                'avis_favorables' => $demande->affectations()->where('statut', '!=', 'TERMINE')->doesntExist(),
                default           => true,
            };

            if ((bool) $passes !== (bool) $expected) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns WorkflowStep models reachable from a given step.
     * Filters by urgency. When demande + user are provided, also filters by canExecute().
     */
    public static function destinationsFor(
        ?int $fromStepId,
        bool $isUrgent = false,
        ?Demande $demande = null,
        ?User $user = null,
    ): BaseCollection {
        $transitions = static::with('toStep.service')
            ->where('from_step_id', $fromStepId)
            ->where(function ($q) use ($isUrgent) {
                $q->where('is_urgent_only', false);
                if ($isUrgent) {
                    $q->orWhere('is_urgent_only', true);
                }
            })
            ->orderBy('ordre')
            ->get();

        if ($demande && $user) {
            $transitions = $transitions->filter(fn($t) => $t->canExecute($demande, $user));
        }

        return $transitions->map(fn($t) => $t->toStep)->filter()->values();
    }

    /**
     * Check whether a transition between two steps is structurally allowed.
     * When demande + user are provided, also checks canExecute() on the matched transition.
     */
    public static function allowed(
        ?int $fromStepId,
        int $toStepId,
        bool $isUrgent = false,
        ?Demande $demande = null,
        ?User $user = null,
    ): bool {
        $transition = static::where('from_step_id', $fromStepId)
            ->where('to_step_id', $toStepId)
            ->where(function ($q) use ($isUrgent) {
                $q->where('is_urgent_only', false);
                if ($isUrgent) {
                    $q->orWhere('is_urgent_only', true);
                }
            })
            ->first();

        if (!$transition) {
            return false;
        }

        if ($demande && $user) {
            return $transition->canExecute($demande, $user);
        }

        return true;
    }

    private function guardDocsComplets(Demande $demande): bool
    {
        if (!$demande->current_service_id) {
            return true;
        }

        $required = StepRequiredDocument::forService($demande->current_service_id, $demande->type);
        $existing = $demande->getMedia()->pluck('collection_name')->unique();

        return $required->filter(fn($req) => !$existing->contains($req->document_type))->isEmpty();
    }
}

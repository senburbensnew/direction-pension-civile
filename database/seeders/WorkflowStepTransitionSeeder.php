<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\WorkflowStep;
use App\Models\WorkflowStepTransition;
use Illuminate\Database\Seeder;

/**
 * Seeds the common circuit (type_demande = null) by converting the legacy
 * FluxTransition service-to-service map into step-to-step transitions.
 *
 * Each transition maps [source_service_code|null => dest_service_code, action].
 * The corresponding global WorkflowStep (type_demande = null) is resolved for
 * each service. If no step exists for a given service, the transition is skipped.
 */
class WorkflowStepTransitionSeeder extends Seeder
{
    public function run(): void
    {
        /*
         * Circuit global (template — type_demande = null) :
         *   Brouillon → Soumise (Direction) → Instruction Secrétariat
         *   → En Décision (Direction) → Approuvée | Rejetée | Annulée
         *
         * Ce circuit reflète le circuit par défaut appliqué à tous les types de demande.
         * Il sert de référence visuelle dans l'interface d'administration.
         *
         * Format : ['step:CODE'|null, 'step:CODE', 'action', is_urgent_only]
         */
        $transitions = [
            // ── Flux principal ──────────────────────────────────────────
            ['step:BROUILLON',                  'step:SOUMISE',                    'Soumettre',                  false],
            [null,                              'step:SOUMISE',                    'Soumettre directement',      false],
            ['step:SOUMISE',                    'step:EN_INSTRUCTION_SECRETARIAT', 'Transmettre au Secrétariat', false],
            ['step:EN_INSTRUCTION_SECRETARIAT', 'step:EN_DECISION',                'Soumettre pour décision',    false],
            ['step:EN_DECISION',                'step:APPROUVEE',                  'Approuver',                  false],
            ['step:EN_DECISION',                'step:REJETEE',                    'Rejeter',                    false],
            ['step:EN_DECISION',                'step:ANNULEE',                    'Annuler',                    false],
            // ── Retour ──────────────────────────────────────────────────
            ['step:EN_INSTRUCTION_SECRETARIAT', 'step:SOUMISE',                    'Retourner à la Direction',   false],
        ];

        $ordre = 10;

        foreach ($transitions as [$srcCode, $dstCode, $action, $urgentOnly]) {
            $fromStep = $srcCode !== null ? $this->resolveStep($srcCode) : null;
            $toStep   = $this->resolveStep($dstCode);

            if (!$toStep) continue; // destination inconnue → ignorer

            $fromStepId = $fromStep?->id ?? null;

            WorkflowStepTransition::updateOrCreate(
                ['from_step_id' => $fromStepId, 'to_step_id' => $toStep->id],
                ['action' => $action, 'is_urgent_only' => $urgentOnly, 'ordre' => $ordre]
            );

            $ordre += 10;
        }
    }

    /**
     * Resolve a step reference: 'step:CODE' → WorkflowStep by code, otherwise by service code.
     * Returns null if not found (transition will be skipped).
     */
    private function resolveStep(string $ref): ?WorkflowStep
    {
        if (str_starts_with($ref, 'step:')) {
            $code = substr($ref, 5);
            return WorkflowStep::where('code', $code)->whereNull('type_demande')->first();
        }

        $serviceId = Service::where('code', $ref)->value('id');
        if (!$serviceId) return null;

        return WorkflowStep::where('service_id', $serviceId)->whereNull('type_demande')->orderBy('ordre')->first();
    }
}

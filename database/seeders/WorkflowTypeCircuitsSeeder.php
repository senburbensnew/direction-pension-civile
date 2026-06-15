<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\WorkflowStep;
use App\Models\WorkflowStepTransition;
use Illuminate\Database\Seeder;

/**
 * Seeds the default workflow circuit for every TypeDemandeEnum value.
 *
 * All standard types start with the canonical default circuit:
 *   Brouillon → Soumission initiale (Direction) → Instruction Secrétariat
 *   → En Décision (Direction) → Approuvée | Rejetée | Annulée
 *
 * DEMANDE_RENCONTRE uses a minimal public-form variant (no draft step).
 * Any type's circuit can be customised further via the admin UI.
 */
class WorkflowTypeCircuitsSeeder extends Seeder
{
    private array $svc = [];   // code → id

    public function run(): void
    {
        Service::all()->each(fn($s) => $this->svc[$s->code] = $s->id);

        // All types start from the default circuit and can be customised via the admin UI.
        // DEMANDE_RENCONTRE is a public form with no draft phase → minimal flow.
        $circuits = [
            'DEMANDE_ATTESTATION'       => 'simple',
            'DEMANDE_PREUVE_EXISTENCE'  => 'simple',
            'DEMANDE_ETAT_CARRIERE'     => 'simple',
            'DEMANDE_VIREMENT_BANCAIRE' => 'simple',
            'DEMANDE_TRANSFERT_CHEQUE'  => 'simple',
            'DEMANDE_ARRET_PAIEMENT'    => 'simple',
            'DEMANDE_ARRET_VIREMENT'    => 'simple',
            'DEMANDE_REINSERTION'       => 'simple',
            'DEMANDE_PENSION'           => 'simple',
            'DEMANDE_PENSION_REVERSION' => 'simple',
            'DEMANDE_ADHESION'          => 'simple',
            'DEMANDE_RENCONTRE'         => 'rencontre',
        ];

        foreach ($circuits as $type => $group) {
            $this->buildCircuit($type, $group);
        }
    }

    // ── Circuit builder ──────────────────────────────────────────────────

    private function buildCircuit(string $type, string $group): void
    {
        $stepDefs       = $this->stepDefs($group);
        $transitionDefs = $this->transitionDefs($group);

        $initiateurs  = ['BROUILLON', 'SOUMISE'];
        $terminaisons = ['APPROUVEE', 'FINALISEE', 'REJETEE', 'ANNULEE'];

        // 1. Upsert steps
        foreach ($stepDefs as [$etatCode, $svcCode, $ordre]) {
            $typeNoeud = in_array($etatCode, $initiateurs)  ? 'initial'
                       : (in_array($etatCode, $terminaisons) ? 'terminal'
                       : 'intermediaire');

            WorkflowStep::updateOrCreate(
                ['code' => $etatCode, 'type_demande' => $type],
                [
                    'nom'        => $this->label($etatCode),
                    'service_id' => $svcCode ? ($this->svc[$svcCode] ?? null) : null,
                    'ordre'      => $ordre,
                    'type_noeud' => $typeNoeud,
                ]
            );
        }

        // 2. Upsert transitions
        $ordre = 10;
        foreach ($transitionDefs as [$fromCode, $toCode, $action, $urgentOnly]) {
            $fromStep = $fromCode !== null
                ? WorkflowStep::where('code', $fromCode)->where('type_demande', $type)->first()
                : null;

            $toStep = WorkflowStep::where('code', $toCode)->where('type_demande', $type)->first();

            if (!$toStep) continue;

            WorkflowStepTransition::updateOrCreate(
                ['from_step_id' => $fromStep?->id, 'to_step_id' => $toStep->id],
                ['action' => $action, 'is_urgent_only' => $urgentOnly, 'ordre' => $ordre]
            );

            $ordre += 10;
        }
    }

    // ── Step definitions by group ────────────────────────────────────────
    // Format : [etat_code, service_code|null, ordre]

    private function stepDefs(string $group): array
    {
        return match ($group) {

            // Brouillon → Soumission initiale (Direction) → Instruction Secrétariat → Décision (Direction) → terminaux
            'simple' => [
                ['BROUILLON',                  null,          1],
                ['SOUMISE',                    'direction',   5],
                ['EN_INSTRUCTION_SECRETARIAT', 'secretariat', 10],
                ['EN_DECISION',                'direction',   20],
                ['APPROUVEE',                  'direction',   30],
                ['REJETEE',                    'direction',   40],
                ['ANNULEE',                    'direction',   50],
            ],

            // Soumission directe (formulaire public, sans brouillon) → Décision → terminaux
            'rencontre' => [
                ['SOUMISE',     'direction', 1],
                ['EN_DECISION', 'direction', 10],
                ['APPROUVEE',   'direction', 20],
                ['REJETEE',     'direction', 30],
                ['ANNULEE',     'direction', 40],
            ],
        };
    }

    // ── Transition definitions by group ──────────────────────────────────
    // Format : [from_code|null, to_code, action, is_urgent_only]

    private function transitionDefs(string $group): array
    {
        return match ($group) {

            'simple' => [
                // Flux principal
                ['BROUILLON',                  'SOUMISE',                    'Soumettre',                 false],
                [null,                         'SOUMISE',                    'Soumettre directement',     false],
                ['SOUMISE',                    'EN_INSTRUCTION_SECRETARIAT', 'Transmettre au Secrétariat', false],
                ['EN_INSTRUCTION_SECRETARIAT', 'EN_DECISION',                'Soumettre pour décision',   false],
                ['EN_DECISION',                'APPROUVEE',                  'Approuver',                 false],
                ['EN_DECISION',                'REJETEE',                    'Rejeter',                   false],
                ['EN_DECISION',                'ANNULEE',                    'Annuler',                   false],
                // Retour
                ['EN_INSTRUCTION_SECRETARIAT', 'SOUMISE',                    'Retourner à la Direction',  false],
            ],

            'rencontre' => [
                [null,          'SOUMISE',     'Soumettre', false],
                ['SOUMISE',     'EN_DECISION', 'Examiner',  false],
                ['EN_DECISION', 'APPROUVEE',   'Accepter',  false],
                ['EN_DECISION', 'REJETEE',     'Refuser',   false],
                ['EN_DECISION', 'ANNULEE',     'Annuler',   false],
            ],
        };
    }

    private function label(string $code): string
    {
        return WorkflowStep::where('code', $code)->value('nom')
            ?? ucwords(strtolower(str_replace('_', ' ', $code)));
    }
}

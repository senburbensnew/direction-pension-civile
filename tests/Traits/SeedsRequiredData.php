<?php

namespace Tests\Traits;

use App\Models\WorkflowStep;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

trait SeedsRequiredData
{
    protected function seedStatuses(): void
    {
        $steps = [
            ['code' => 'BROUILLON',            'nom' => 'Brouillon',            'type_noeud' => 'initial',    'ordre' => 1],
            ['code' => 'SOUMISE',              'nom' => 'Soumise',              'type_noeud' => 'initial',    'ordre' => 2],
            ['code' => 'EN_ATTENTE',           'nom' => 'En attente',           'type_noeud' => 'intermediaire', 'ordre' => 5],
            ['code' => 'EN_COURS',             'nom' => 'En cours',             'type_noeud' => 'intermediaire', 'ordre' => 6],
            ['code' => 'APPROUVEE',            'nom' => 'Approuvée',            'type_noeud' => 'terminal',   'ordre' => 90],
            ['code' => 'REJETEE',              'nom' => 'Rejetée',              'type_noeud' => 'terminal',   'ordre' => 91],
            ['code' => 'FINALISEE',            'nom' => 'Finalisée',            'type_noeud' => 'terminal',   'ordre' => 92],
            ['code' => 'ANNULEE',              'nom' => 'Annulée',              'type_noeud' => 'terminal',   'ordre' => 93],
            ['code' => 'COMPLEMENT_REQUIS',    'nom' => 'Complément requis',    'type_noeud' => 'intermediaire', 'ordre' => 0],
            ['code' => 'TRANSFERT_EN_ATTENTE', 'nom' => 'Transfert en attente', 'type_noeud' => 'intermediaire', 'ordre' => 0],
            ['code' => 'TRANSFERT_REFUSE',     'nom' => 'Transfert refusé',     'type_noeud' => 'intermediaire', 'ordre' => 0],
        ];

        foreach ($steps as $step) {
            DB::table('workflow_steps')->insertOrIgnore(array_merge($step, [
                'service_id'   => null,
                'type_demande' => null,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]));
        }
    }

    protected function seedServices(): void
    {
        $services = [
            Service::DIRECTION          => 'Direction',
            Service::SECRETARIAT        => 'Secrétariat',
            Service::LIQUIDATION        => 'Service Liquidation',
            Service::CONTROLE_PLACEMENT => 'Service Contrôle et Placement',
            Service::COMPTABILITE       => 'Service Comptabilité',
            Service::FORMALITE          => 'Service Formalité',
            Service::ASSURANCE          => 'Service Assurance',
        ];

        foreach ($services as $code => $nom) {
            Service::create(['code' => $code, 'nom' => $nom]);
        }
    }

    protected function seedRoles(): void
    {
        $roles = [
            'admin', 'direction', 'secretariat', 'service_liquidation',
            'service_formalite', 'service_controle_placement',
            'service_comptabilite', 'service_assurance',
            'pensionnaire', 'fonctionnaire', 'institution',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }

    protected function createDemande(array $attributes = []): \App\Models\Demande
    {
        $user = isset($attributes['created_by'])
            ? \App\Models\User::find($attributes['created_by'])
            : \App\Models\User::factory()->create();

        $brouillonId = WorkflowStep::idForCode('BROUILLON');

        return \App\Models\Demande::create(array_merge([
            'type'       => \App\Enums\TypeDemandeEnum::DEMANDE_ATTESTATION->value,
            'created_by' => $user->id,
            'current_step_id'  => $brouillonId,
        ], $attributes));
    }
}

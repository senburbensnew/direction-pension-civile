<?php

namespace Tests\Traits;

use App\Models\Service;
use App\Models\Status;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

trait SeedsRequiredData
{
    protected function seedStatuses(): void
    {
        $statuses = [
            ['code' => 'BROUILLON',            'label' => 'Brouillon'],
            ['code' => 'SOUMISE',              'label' => 'Soumise'],
            ['code' => 'EN_ATTENTE',           'label' => 'En attente'],
            ['code' => 'APPROUVEE',            'label' => 'Approuvée'],
            ['code' => 'EN_COURS',             'label' => 'En cours'],
            ['code' => 'REJETEE',              'label' => 'Rejetée'],
            ['code' => 'FINALISEE',            'label' => 'Finalisée'],
            ['code' => 'ANNULEE',              'label' => 'Annulée'],
            ['code' => 'COMPLEMENT_REQUIS',    'label' => 'Complément requis'],
            ['code' => 'TRANSFERT_EN_ATTENTE', 'label' => 'Transfert en attente'],
            ['code' => 'TRANSFERT_REFUSE',     'label' => 'Transfert refusé'],
        ];

        // insertOrIgnore handles the case where the migration already seeded some statuses
        DB::table('statuses')->insertOrIgnore($statuses);
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

        $brouillonId = Status::where('code', 'BROUILLON')->value('id');

        return \App\Models\Demande::create(array_merge([
            'type'       => \App\Enums\TypeDemandeEnum::DEMANDE_ATTESTATION->value,
            'created_by' => $user->id,
            'status_id'  => $brouillonId,
        ], $attributes));
    }
}

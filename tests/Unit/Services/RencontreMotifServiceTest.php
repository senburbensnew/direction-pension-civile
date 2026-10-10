<?php

namespace Tests\Unit\Services;

use App\Enums\TypeDemandeEnum;
use App\Enums\AccountStatusEnum;
use App\Models\Demande;
use App\Models\DemandeCreationCompte;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkflowStep;
use App\Services\Account\UserCategoryRoleSyncService;
use App\Services\RencontreMotifService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class RencontreMotifServiceTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedStatuses();
        $this->seedServices();
    }

    public function test_it_exposes_configured_motifs(): void
    {
        $service = app(RencontreMotifService::class);

        $this->assertArrayHasKey(
            'demande_formalites_mandats',
            $service->motifs()
        );

        $this->assertSame(
            Service::FORMALITE,
            $service->resolve('demande_formalites_mandats')?->code
        );

        $this->assertSame(
            'Service Formalités',
            $service->motifServiceLabels()['demande_formalites_mandats'] ?? null
        );

        Service::query()->where('code', Service::FORMALITE)->delete();

        $this->assertSame(
            'Service Formalités',
            $service->serviceLabelForMotif('demande_formalites_mandats')
        );

        $this->assertSame(
            Service::FORMALITE,
            $service->resolve('demande_formalites_mandats')?->code
        );

        $this->assertSame(
            'demande_formalites_mandats',
            $service->normalizeMotifKey('Demande de formalités de pension et mandats')
        );
    }

    public function test_provisional_user_sees_formalites_mandats_motif(): void
    {
        $this->seedRoles();
        $user = User::factory()->create([
            'account_status' => AccountStatusEnum::PROVISOIRE,
        ]);
        $user->assignRole('pensionne');

        DemandeCreationCompte::create([
            'code' => 'DCC-TEST-001',
            'user_type' => 'pensionne',
            'telephone' => '+50938123456',
            'adresse' => 'Port-au-Prince',
            'status' => DemandeCreationCompte::STATUS_EN_ATTENTE,
            'user_id' => $user->id,
            'soumise_at' => now(),
        ]);

        $service = app(RencontreMotifService::class);
        $motifs = $service->motifsForUser($user);

        $this->assertArrayHasKey('demande_formalites_mandats', $motifs);
        $this->assertArrayNotHasKey('finalisation_creation_compte', $motifs);
        $this->assertSame(
            'Demande de formalités de pension et mandats',
            $motifs['demande_formalites_mandats'] ?? null
        );
        $this->assertSame(
            Service::FORMALITE,
            $service->resolve('demande_formalites_mandats')?->code
        );
    }

    public function test_active_pensionne_sees_formalites_mandats_motif(): void
    {
        $this->seedRoles();
        $user = User::factory()->create([
            'account_status' => AccountStatusEnum::ACTIF,
        ]);
        $user->assignRole('pensionne');
        app(UserCategoryRoleSyncService::class)->assignCategory($user, 'pensionne', primary: true, syncRoles: false);

        $service = app(RencontreMotifService::class);
        $motifs = $service->motifsForUser($user);

        $this->assertArrayHasKey('demande_formalites_mandats', $motifs);
        $this->assertSame(
            Service::FORMALITE,
            $service->resolve('demande_formalites_mandats')?->code
        );
    }

    public function test_suivi_uses_the_service_of_the_latest_user_dossier(): void
    {
        $this->seedRoles();
        $user = User::factory()->create();
        $liquidationId = Service::where('code', Service::LIQUIDATION)->value('id');

        Demande::create([
            'type' => TypeDemandeEnum::DEMANDE_PENSION->value,
            'created_by' => $user->id,
            'current_service_id' => $liquidationId,
            'current_step_id' => WorkflowStep::idForCode('SOUMISE'),
        ]);

        $resolved = app(RencontreMotifService::class)->resolve('suivi_dossier', $user);

        $this->assertSame(Service::LIQUIDATION, $resolved?->code);
    }

    public function test_document_validation_rules_include_required_motif_documents(): void
    {
        $service = app(RencontreMotifService::class);

        $rules = $service->documentValidationRules('demande_formalites_mandats');

        $this->assertArrayHasKey('doc_carte_pension', $rules);
        $this->assertArrayHasKey('doc_piece_identite', $rules);
        $this->assertContains('required', $rules['doc_carte_pension']);
    }
}

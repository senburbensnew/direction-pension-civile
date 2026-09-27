<?php

namespace Tests\Unit\Services;

use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkflowStep;
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

    public function test_it_maps_motifs_to_the_responsible_service(): void
    {
        $service = app(RencontreMotifService::class);

        $this->assertSame(Service::LIQUIDATION, $service->resolve('demande_pension')?->code);
        $this->assertSame(Service::FORMALITE, $service->resolve('formalites_pension')?->code);
        $this->assertSame(Service::FORMALITE, $service->resolve('designation_mandataire')?->code);
        $this->assertSame(Service::FORMALITE, $service->resolve('autre')?->code);
        $this->assertSame(Service::COMPTABILITE, $service->resolve('paiement')?->code);
        $this->assertSame(Service::FORMALITE, $service->resolve('attestation')?->code);
        $this->assertSame(Service::ADMINISTRATIF, $service->resolve('mise_a_jour')?->code);
        $this->assertSame(Service::CONTROLE_PLACEMENT, $service->resolve('controle')?->code);
        $this->assertSame(Service::FORMALITE, $service->resolve('information_generale')?->code);
        $this->assertSame(Service::DIRECTION, $service->resolve('direction')?->code);
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
}

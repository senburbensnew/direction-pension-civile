<?php

namespace Tests\Unit\Services;

use App\Enums\RencontreStatutEnum;
use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\User;
use App\Services\RencontreStatutAlignmentService;
use App\Services\RencontreWorkflowService;
use Mockery;
use Tests\TestCase;

class RencontreStatutAlignmentServiceTest extends TestCase
{
    /** @test */
    public function it_skips_non_rencontre_demandes(): void
    {
        $demande = new Demande(['type' => TypeDemandeEnum::DEMANDE_ATTESTATION->value]);
        $workflow = Mockery::mock(RencontreWorkflowService::class);
        $workflow->shouldNotReceive('statut');

        $service = new RencontreStatutAlignmentService($workflow);
        $user = User::factory()->make();

        $this->assertSame($demande, $service->alignAfterFormalite($demande, $user));
    }

    /** @test */
    public function it_closes_active_rdv_when_formalite_recorded_and_slot_passed(): void
    {
        $demande = new class extends Demande {
            public function isRencontre(): bool
            {
                return true;
            }

            public function formaliteRdvEnregistree(): bool
            {
                return true;
            }

            public function rencontreHeureEstPassee(): bool
            {
                return true;
            }
        };

        $user = User::factory()->make();
        $closed = new Demande;

        $workflow = Mockery::mock(RencontreWorkflowService::class);
        $workflow->shouldReceive('statut')->andReturn(RencontreStatutEnum::ACTIF);
        $workflow->shouldReceive('clore')
            ->once()
            ->with($demande, $user, RencontreStatutEnum::REALISE, Mockery::type('string'))
            ->andReturn($closed);

        $service = new RencontreStatutAlignmentService($workflow);

        $this->assertSame($closed, $service->alignAfterFormalite($demande, $user));
    }
}

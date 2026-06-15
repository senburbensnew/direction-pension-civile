<?php

namespace Tests\Unit\Models;

use App\Models\Demande;
use App\Models\DemandeInteraction;
use App\Models\Service;
use App\Models\User;
use App\Enums\TypeDemandeEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class DemandeInteractionTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedStatuses();
        $this->seedServices();
    }

    private function makeInteraction(
        string $statut = DemandeInteraction::STATUT_EN_ATTENTE,
        string $type   = DemandeInteraction::TYPE_TRANSFERT
    ): DemandeInteraction {
        $user    = User::factory()->create();
        $service = Service::where('code', Service::DIRECTION)->first();

        $demande = Demande::create([
            'type'       => TypeDemandeEnum::DEMANDE_ATTESTATION->value,
            'created_by' => $user->id,
        ]);

        return DemandeInteraction::create([
            'demande_id'    => $demande->id,
            'type'          => $type,
            'to_service_id' => $service->id,
            'initiated_by'  => $user->id,
            'statut'        => $statut,
        ]);
    }

    /** @test */
    public function isPending_returns_true_only_when_statut_is_EN_ATTENTE(): void
    {
        $interaction = $this->makeInteraction(DemandeInteraction::STATUT_EN_ATTENTE);

        $this->assertTrue($interaction->isPending());
        $this->assertFalse($interaction->isAccepted());
        $this->assertFalse($interaction->isRefused());
    }

    /** @test */
    public function isAccepted_returns_true_only_when_statut_is_ACCEPTE(): void
    {
        $interaction = $this->makeInteraction(DemandeInteraction::STATUT_ACCEPTE);

        $this->assertTrue($interaction->isAccepted());
        $this->assertFalse($interaction->isPending());
        $this->assertFalse($interaction->isRefused());
    }

    /** @test */
    public function isRefused_returns_true_only_when_statut_is_REJETE(): void
    {
        $interaction = $this->makeInteraction(DemandeInteraction::STATUT_REJETE);

        $this->assertTrue($interaction->isRefused());
        $this->assertFalse($interaction->isPending());
        $this->assertFalse($interaction->isAccepted());
    }

    /** @test */
    public function isTransfert_and_isAvis_are_mutually_exclusive(): void
    {
        $transfert = $this->makeInteraction(DemandeInteraction::STATUT_EN_ATTENTE, DemandeInteraction::TYPE_TRANSFERT);
        $avis      = $this->makeInteraction(DemandeInteraction::STATUT_EN_ATTENTE, DemandeInteraction::TYPE_AVIS);

        $this->assertTrue($transfert->isTransfert());
        $this->assertFalse($transfert->isAvis());

        $this->assertTrue($avis->isAvis());
        $this->assertFalse($avis->isTransfert());
    }

    /** @test */
    public function interaction_belongs_to_demande(): void
    {
        $interaction = $this->makeInteraction();

        $this->assertInstanceOf(Demande::class, $interaction->demande);
    }

    /** @test */
    public function interaction_belongs_to_to_service(): void
    {
        $interaction = $this->makeInteraction();

        $this->assertInstanceOf(Service::class, $interaction->toService);
    }

    /** @test */
    public function interaction_belongs_to_initiated_by_user(): void
    {
        $interaction = $this->makeInteraction();

        $this->assertInstanceOf(User::class, $interaction->initiatedBy);
    }
}

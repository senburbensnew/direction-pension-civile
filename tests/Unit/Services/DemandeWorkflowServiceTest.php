<?php

namespace Tests\Unit\Services;

use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\DemandeHistory;
use App\Models\DemandeInteraction;
use App\Models\WorkflowStep;
use App\Models\WorkflowStepTransition;
use App\Models\Service;
use App\Models\User;
use App\Services\DemandeWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class DemandeWorkflowServiceTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    private DemandeWorkflowService $workflowService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedStatuses();
        $this->seedServices();
        $this->seedRoles();
        $this->workflowService = new DemandeWorkflowService();
    }

    private function makeUser(?string $role = null): User
    {
        $user = User::factory()->create();
        if ($role) {
            $user->assignRole($role);
        }
        return $user;
    }

    private function makeDemande(string $type = TypeDemandeEnum::DEMANDE_ATTESTATION->value): Demande
    {
        $user = $this->makeUser();
        return Demande::create(['type' => $type, 'created_by' => $user->id]);
    }

    private function direction(): Service
    {
        return Service::where('code', Service::DIRECTION)->first();
    }

    private function liquidation(): Service
    {
        return Service::where('code', Service::LIQUIDATION)->first();
    }

    // ─── submit() ────────────────────────────────────────────────────────────

    /** @test */
    public function submit_sets_status_to_soumise_and_assigns_direction_service(): void
    {
        $demande = $this->makeDemande();
        $user    = $this->makeUser();

        $this->workflowService->submit($demande, $user);

        $demande->refresh();

        $this->assertEquals('SOUMISE', $demande->currentStep?->code);
        $this->assertEquals($this->direction()->id, $demande->current_service_id);
    }

    /** @test */
    public function submit_creates_interaction_record_with_accepte_statut(): void
    {
        $demande = $this->makeDemande();
        $user    = $this->makeUser();

        $this->workflowService->submit($demande, $user);

        $this->assertDatabaseHas('demande_interactions', [
            'demande_id'    => $demande->id,
            'type'          => DemandeInteraction::TYPE_TRANSFERT,
            'to_service_id' => $this->direction()->id,
            'statut'        => DemandeInteraction::STATUT_ACCEPTE,
        ]);
    }

    // ─── validateTransition() ────────────────────────────────────────────────

    /** @test */
    public function validateTransition_returns_true_when_step_transition_exists(): void
    {
        $fromStep = WorkflowStep::create(['code' => 'DIR', 'nom' => 'Direction',   'service_id' => $this->direction()->id,   'ordre' => 10]);
        $toStep   = WorkflowStep::create(['code' => 'LIQ', 'nom' => 'Liquidation', 'service_id' => $this->liquidation()->id, 'ordre' => 20]);

        WorkflowStepTransition::create([
            'from_step_id' => $fromStep->id,
            'to_step_id'   => $toStep->id,
            'action'       => 'transfer',
            'ordre'        => 10,
        ]);

        $this->assertTrue($this->workflowService->validateTransition(
            $this->direction()->id,
            $this->liquidation()->id
        ));
    }

    /** @test */
    public function validateTransition_returns_false_when_no_step_transition_configured(): void
    {
        WorkflowStep::create(['code' => 'DIR', 'nom' => 'Direction',   'service_id' => $this->direction()->id,   'ordre' => 10]);
        WorkflowStep::create(['code' => 'LIQ', 'nom' => 'Liquidation', 'service_id' => $this->liquidation()->id, 'ordre' => 20]);

        // Steps exist but no transition between them → must return false
        $this->assertFalse($this->workflowService->validateTransition(
            $this->direction()->id,
            $this->liquidation()->id
        ));
    }

    // ─── transfer() ──────────────────────────────────────────────────────────

    private function makeStepTransition(): void
    {
        $fromStep = WorkflowStep::create(['code' => 'DIR', 'nom' => 'Direction',   'service_id' => $this->direction()->id,   'ordre' => 10]);
        $toStep   = WorkflowStep::create(['code' => 'LIQ', 'nom' => 'Liquidation', 'service_id' => $this->liquidation()->id, 'ordre' => 20]);

        WorkflowStepTransition::create([
            'from_step_id' => $fromStep->id,
            'to_step_id'   => $toStep->id,
            'action'       => 'transfer',
            'ordre'        => 10,
        ]);
    }

    /** @test */
    public function transfer_moves_demande_to_destination_with_pending_statut(): void
    {
        $this->makeStepTransition();

        $demande = $this->makeDemande();
        $demande->update([
            'current_service_id' => $this->direction()->id,
            'annotation'         => 'Test',
            'annotated_by'       => 1,
            'annotated_at'       => now(),
        ]);

        $user = $this->makeUser();

        $interaction = $this->workflowService->transfer($demande, $this->liquidation(), $user, 'Motif test');

        $demande->refresh();

        $this->assertEquals($this->liquidation()->id, $demande->current_service_id);
        $this->assertEquals('TRANSFERT_EN_ATTENTE', $demande->currentStep?->code);
        $this->assertInstanceOf(DemandeInteraction::class, $interaction);
        $this->assertEquals(DemandeInteraction::STATUT_EN_ATTENTE, $interaction->statut);
    }

    /** @test */
    public function transfer_creates_history_record(): void
    {
        $this->makeStepTransition();

        $demande = $this->makeDemande();
        $demande->update([
            'current_service_id' => $this->direction()->id,
            'annotation'         => 'Note',
            'annotated_by'       => 1,
            'annotated_at'       => now(),
        ]);

        $user = $this->makeUser();

        $this->workflowService->transfer($demande, $this->liquidation(), $user);

        $this->assertDatabaseHas('demande_histories', [
            'demande_id' => $demande->id,
            'statut'     => 'TRANSFERT_EN_ATTENTE',
        ]);
    }

    /** @test */
    public function transfer_aborts_403_when_transition_not_allowed(): void
    {
        // Steps exist but no transition between them → 403
        WorkflowStep::create(['code' => 'DIR', 'nom' => 'Direction',   'service_id' => $this->direction()->id,   'ordre' => 10]);
        WorkflowStep::create(['code' => 'LIQ', 'nom' => 'Liquidation', 'service_id' => $this->liquidation()->id, 'ordre' => 20]);

        $demande = $this->makeDemande();
        $demande->update(['current_service_id' => $this->direction()->id]);

        $user = $this->makeUser();

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $this->workflowService->transfer($demande, $this->liquidation(), $user);
    }

    // ─── accepterReception() ─────────────────────────────────────────────────

    /** @test */
    public function accepterReception_sets_interaction_to_accepte_and_status_to_en_cours(): void
    {
        $user    = $this->makeUser();
        $demande = $this->makeDemande();

        $interaction = DemandeInteraction::create([
            'demande_id'    => $demande->id,
            'type'          => DemandeInteraction::TYPE_TRANSFERT,
            'to_service_id' => $this->liquidation()->id,
            'initiated_by'  => $user->id,
            'statut'        => DemandeInteraction::STATUT_EN_ATTENTE,
        ]);

        $this->workflowService->accepterReception($interaction, $user);

        $interaction->refresh();
        $demande->refresh();

        $this->assertEquals(DemandeInteraction::STATUT_ACCEPTE, $interaction->statut);
        $this->assertEquals('EN_COURS', $demande->currentStep?->code);
    }

    /** @test */
    public function accepterReception_aborts_422_when_already_processed(): void
    {
        $user    = $this->makeUser();
        $demande = $this->makeDemande();

        $interaction = DemandeInteraction::create([
            'demande_id'    => $demande->id,
            'type'          => DemandeInteraction::TYPE_TRANSFERT,
            'to_service_id' => $this->liquidation()->id,
            'initiated_by'  => $user->id,
            'statut'        => DemandeInteraction::STATUT_ACCEPTE,
        ]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $this->workflowService->accepterReception($interaction, $user);
    }

    // ─── refuserReception() ──────────────────────────────────────────────────

    /** @test */
    public function refuserReception_returns_demande_to_originating_service(): void
    {
        $user    = $this->makeUser();
        $demande = $this->makeDemande();

        $demande->update(['current_service_id' => $this->liquidation()->id]);

        $interaction = DemandeInteraction::create([
            'demande_id'      => $demande->id,
            'type'            => DemandeInteraction::TYPE_TRANSFERT,
            'from_service_id' => $this->direction()->id,
            'to_service_id'   => $this->liquidation()->id,
            'initiated_by'    => $user->id,
            'statut'          => DemandeInteraction::STATUT_EN_ATTENTE,
        ]);

        $this->workflowService->refuserReception($interaction, $user, 'Dossier incomplet');

        $interaction->refresh();
        $demande->refresh();

        $this->assertEquals(DemandeInteraction::STATUT_REJETE, $interaction->statut);
        $this->assertEquals('Dossier incomplet', $interaction->reponse);
        $this->assertEquals($this->direction()->id, $demande->current_service_id);
        $this->assertEquals('TRANSFERT_REFUSE', $demande->currentStep?->code);
    }

    // ─── affecterServices() ──────────────────────────────────────────────────

    /** @test */
    public function affecterServices_creates_avis_interactions_for_each_service(): void
    {
        $user    = $this->makeUser('direction');
        $demande = $this->makeDemande();
        $demande->update(['current_step_id' => WorkflowStep::idForCode('EN_COURS')]);

        $serviceIds = [$this->liquidation()->id, $this->direction()->id];

        $this->workflowService->affecterServices($demande, $serviceIds, $user);

        $this->assertDatabaseCount('demande_interactions', 2);
        $this->assertDatabaseHas('demande_interactions', [
            'demande_id'    => $demande->id,
            'type'          => DemandeInteraction::TYPE_AVIS,
            'to_service_id' => $this->liquidation()->id,
            'statut'        => DemandeInteraction::STATUT_EN_ATTENTE,
        ]);
    }

    /** @test */
    public function affecterServices_updates_existing_interaction_rather_than_duplicating(): void
    {
        $user    = $this->makeUser('direction');
        $demande = $this->makeDemande();
        $demande->update(['current_step_id' => WorkflowStep::idForCode('EN_COURS')]);

        $serviceIds = [$this->liquidation()->id];

        $this->workflowService->affecterServices($demande, $serviceIds, $user);
        $this->workflowService->affecterServices($demande, $serviceIds, $user);

        // Should still be 1, not 2
        $this->assertDatabaseCount('demande_interactions', 1);
    }

    // ─── repondreAffectation() ───────────────────────────────────────────────

    /** @test */
    public function repondreAffectation_updates_statut_and_creates_history(): void
    {
        $user    = $this->makeUser();
        $demande = $this->makeDemande();
        $demande->update(['current_step_id' => WorkflowStep::idForCode('EN_COURS')]);

        $affectation = DemandeInteraction::create([
            'demande_id'    => $demande->id,
            'type'          => DemandeInteraction::TYPE_AVIS,
            'to_service_id' => $this->liquidation()->id,
            'statut'        => DemandeInteraction::STATUT_EN_ATTENTE,
            'initiated_by'  => $user->id,
        ]);

        $this->workflowService->repondreAffectation($affectation, $user, 'Avis favorable', 'TERMINE');

        $affectation->refresh();

        $this->assertEquals('TERMINE', $affectation->statut);
        $this->assertEquals('Avis favorable', $affectation->reponse);
        $this->assertNotNull($affectation->repondu_at);

        $this->assertDatabaseHas('demande_histories', [
            'demande_id' => $demande->id,
        ]);
    }
}

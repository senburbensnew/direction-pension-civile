<?php

namespace Tests\Unit\Services;

use App\Enums\TypeDemandeEnum;
use App\Models\Affectation;
use App\Models\Demande;
use App\Models\DemandeHistory;
use App\Models\DemandeWorkflow;
use App\Models\FluxTransition;
use App\Models\Service;
use App\Models\Status;
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

        $this->assertEquals('SOUMISE', $demande->status->code);
        $this->assertEquals($this->direction()->id, $demande->current_service_id);
    }

    /** @test */
    public function submit_creates_workflow_record_with_accepted_reception(): void
    {
        $demande = $this->makeDemande();
        $user    = $this->makeUser();

        $this->workflowService->submit($demande, $user);

        $this->assertDatabaseHas('demande_workflows', [
            'demande_id'       => $demande->id,
            'to_service_id'    => $this->direction()->id,
            'reception_status' => 'accepted',
        ]);
    }

    // ─── validateTransition() ────────────────────────────────────────────────

    /** @test */
    public function validateTransition_returns_true_when_flux_transition_exists(): void
    {
        FluxTransition::create([
            'service_source_id'      => $this->direction()->id,
            'service_destination_id' => $this->liquidation()->id,
            'action'                 => 'transfer',
            'ordre'                  => 1,
        ]);

        $this->assertTrue($this->workflowService->validateTransition(
            $this->direction()->id,
            $this->liquidation()->id
        ));
    }

    /** @test */
    public function validateTransition_returns_false_when_no_flux_transition(): void
    {
        $this->assertFalse($this->workflowService->validateTransition(
            $this->direction()->id,
            $this->liquidation()->id
        ));
    }

    // ─── transfer() ──────────────────────────────────────────────────────────

    /** @test */
    public function transfer_moves_demande_to_destination_with_pending_status(): void
    {
        FluxTransition::create([
            'service_source_id'      => $this->direction()->id,
            'service_destination_id' => $this->liquidation()->id,
            'action'                 => 'transfer',
            'ordre'                  => 1,
        ]);

        $demande = $this->makeDemande();
        $demande->update([
            'current_service_id' => $this->direction()->id,
            'annotation'         => 'Test',
            'annotated_by'       => 1,
            'annotated_at'       => now(),
        ]);

        $user = $this->makeUser();

        $workflow = $this->workflowService->transfer($demande, $this->liquidation(), $user, 'Motif test');

        $demande->refresh();

        $this->assertEquals($this->liquidation()->id, $demande->current_service_id);
        $this->assertEquals('TRANSFERT_EN_ATTENTE', $demande->status->code);
        $this->assertInstanceOf(DemandeWorkflow::class, $workflow);
        $this->assertEquals('pending', $workflow->reception_status);
    }

    /** @test */
    public function transfer_creates_history_record(): void
    {
        FluxTransition::create([
            'service_source_id'      => $this->direction()->id,
            'service_destination_id' => $this->liquidation()->id,
            'action'                 => 'transfer',
            'ordre'                  => 1,
        ]);

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
        $demande = $this->makeDemande();
        $demande->update(['current_service_id' => $this->direction()->id]);

        $user = $this->makeUser();

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $this->workflowService->transfer($demande, $this->liquidation(), $user);
    }

    // ─── accepterReception() ─────────────────────────────────────────────────

    /** @test */
    public function accepterReception_sets_workflow_to_accepted_and_status_to_en_cours(): void
    {
        $user     = $this->makeUser();
        $demande  = $this->makeDemande();
        $statusId = Status::where('code', 'TRANSFERT_EN_ATTENTE')->value('id');

        $workflow = DemandeWorkflow::create([
            'demande_id'        => $demande->id,
            'to_service_id'     => $this->liquidation()->id,
            'status_id'         => $statusId,
            'action_by_user_id' => $user->id,
            'reception_status'  => 'pending',
        ]);

        $this->workflowService->accepterReception($workflow, $user);

        $workflow->refresh();
        $demande->refresh();

        $this->assertEquals('accepted', $workflow->reception_status);
        $this->assertEquals('EN_COURS', $demande->status->code);
    }

    /** @test */
    public function accepterReception_aborts_422_when_already_processed(): void
    {
        $user     = $this->makeUser();
        $demande  = $this->makeDemande();
        $statusId = Status::where('code', 'EN_COURS')->value('id');

        $workflow = DemandeWorkflow::create([
            'demande_id'        => $demande->id,
            'to_service_id'     => $this->liquidation()->id,
            'status_id'         => $statusId,
            'action_by_user_id' => $user->id,
            'reception_status'  => 'accepted',
        ]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $this->workflowService->accepterReception($workflow, $user);
    }

    // ─── refuserReception() ──────────────────────────────────────────────────

    /** @test */
    public function refuserReception_returns_demande_to_originating_service(): void
    {
        $user     = $this->makeUser();
        $demande  = $this->makeDemande();
        $statusId = Status::where('code', 'TRANSFERT_EN_ATTENTE')->value('id');

        // Direction originally held the demande
        $demande->update(['current_service_id' => $this->liquidation()->id]);

        $workflow = DemandeWorkflow::create([
            'demande_id'        => $demande->id,
            'from_service_id'   => $this->direction()->id,
            'to_service_id'     => $this->liquidation()->id,
            'status_id'         => $statusId,
            'action_by_user_id' => $user->id,
            'reception_status'  => 'pending',
        ]);

        $this->workflowService->refuserReception($workflow, $user, 'Dossier incomplet');

        $workflow->refresh();
        $demande->refresh();

        $this->assertEquals('refused', $workflow->reception_status);
        $this->assertEquals('Dossier incomplet', $workflow->reception_motif);
        $this->assertEquals($this->direction()->id, $demande->current_service_id);
        $this->assertEquals('TRANSFERT_REFUSE', $demande->status->code);
    }

    // ─── affecterServices() ──────────────────────────────────────────────────

    /** @test */
    public function affecterServices_creates_affectations_for_each_service(): void
    {
        $user    = $this->makeUser('direction');
        $demande = $this->makeDemande();
        $demande->update(['status_id' => Status::where('code', 'EN_COURS')->value('id')]);

        $serviceIds = [$this->liquidation()->id, $this->direction()->id];

        $this->workflowService->affecterServices($demande, $serviceIds, $user);

        $this->assertDatabaseCount('affectations', 2);
        $this->assertDatabaseHas('affectations', [
            'demande_id' => $demande->id,
            'service_id' => $this->liquidation()->id,
            'statut'     => 'EN_ATTENTE',
        ]);
    }

    /** @test */
    public function affecterServices_updates_existing_affectation_rather_than_duplicating(): void
    {
        $user    = $this->makeUser('direction');
        $demande = $this->makeDemande();
        $demande->update(['status_id' => Status::where('code', 'EN_COURS')->value('id')]);

        $serviceIds = [$this->liquidation()->id];

        $this->workflowService->affecterServices($demande, $serviceIds, $user);
        $this->workflowService->affecterServices($demande, $serviceIds, $user);

        // Should still be 1, not 2
        $this->assertDatabaseCount('affectations', 1);
    }

    // ─── repondreAffectation() ───────────────────────────────────────────────

    /** @test */
    public function repondreAffectation_updates_statut_and_creates_history(): void
    {
        $user    = $this->makeUser();
        $demande = $this->makeDemande();
        $demande->update(['status_id' => Status::where('code', 'EN_COURS')->value('id')]);

        $affectation = Affectation::create([
            'demande_id'          => $demande->id,
            'service_id'          => $this->liquidation()->id,
            'statut'              => 'EN_ATTENTE',
            'affecte_par_user_id' => $user->id,
            'date_affectation'    => now(),
        ]);

        $this->workflowService->repondreAffectation($affectation, $user, 'Avis favorable', 'TERMINE');

        $affectation->refresh();

        $this->assertEquals('TERMINE', $affectation->statut);
        $this->assertEquals('Avis favorable', $affectation->avis);
        $this->assertNotNull($affectation->date_reponse);

        $this->assertDatabaseHas('demande_histories', [
            'demande_id' => $demande->id,
        ]);
    }
}

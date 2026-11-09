<?php

namespace Tests\Unit\Services;

use App\Enums\RencontreStatutEnum;
use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\User;
use App\Models\WorkflowStep;
use App\Services\RencontreWorkflowService;
use Database\Seeders\WorkflowSteps\RencontreWorkflowStepSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class RencontreWorkflowServiceTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRequiredData;

    private RencontreWorkflowService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflowSteps();
        $this->seedServices();
        $this->seed(RencontreWorkflowStepSeeder::class);
        $this->service = app(RencontreWorkflowService::class);
    }

    /** @test */
    public function statut_prefers_stored_rdv_statut_in_data(): void
    {
        $demande = $this->makeRencontreDemande([
            'rdv_statut' => RencontreStatutEnum::REALISE->value,
        ], 'ACTIF');

        $this->assertSame(RencontreStatutEnum::REALISE, $this->service->statut($demande));
        $this->assertFalse($this->service->estActif($demande));
    }

    /** @test */
    public function statut_falls_back_to_workflow_step_when_rdv_statut_missing(): void
    {
        $demande = $this->makeRencontreDemande([], 'ATTRIBUE');

        $this->assertSame(RencontreStatutEnum::ATTRIBUE, $this->service->statut($demande));
    }

    /** @test */
    public function est_actif_is_true_only_for_actif_status(): void
    {
        $actif = $this->makeRencontreDemande(['rdv_statut' => RencontreStatutEnum::ACTIF->value]);
        $refuse = $this->makeRencontreDemande(['rdv_statut' => RencontreStatutEnum::REFUSE->value]);

        $this->assertTrue($this->service->estActif($actif));
        $this->assertFalse($this->service->estActif($refuse));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function makeRencontreDemande(array $data = [], string $stepCode = 'SOUMISE'): Demande
    {
        return Demande::create([
            'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => User::factory()->create()->id,
            'current_step_id' => WorkflowStep::idForCode($stepCode, TypeDemandeEnum::DEMANDE_RENCONTRE->value),
            'data' => array_merge(['motif_key' => 'demande_formalites_mandats'], $data),
        ]);
    }
}

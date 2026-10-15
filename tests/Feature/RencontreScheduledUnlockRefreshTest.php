<?php

namespace Tests\Feature;

use App\Enums\RencontreStatutEnum;
use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkflowStep;
use Database\Seeders\WorkflowSteps\RencontreWorkflowStepSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class RencontreScheduledUnlockRefreshTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflowSteps();
        $this->seedServices();
        $this->seedRoles();
        $this->seed(RencontreWorkflowStepSeeder::class);
    }

    private function agentRdv(): User
    {
        $agent = User::factory()->create([
            'service_id' => Service::where('code', Service::FORMALITE)->value('id'),
        ]);
        $agent->assignRole(User::ROLE_AGENT_RDV);

        return $agent;
    }

    private function activeDemande(string $date, string $heure): Demande
    {
        return Demande::create([
            'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => User::factory()->create()->id,
            'current_service_id' => Service::where('code', Service::FORMALITE)->value('id'),
            'current_step_id' => WorkflowStep::idForCode('ACTIF', TypeDemandeEnum::DEMANDE_RENCONTRE->value),
            'data' => [
                'motif_key' => 'demande_formalites_mandats',
                'objet' => 'Formalités',
                'modalite' => 'physique',
                'date_souhaitee' => $date,
                'heure_souhaitee' => $heure,
                'rdv_statut' => RencontreStatutEnum::ACTIF->value,
            ],
        ]);
    }

    public function test_personal_request_includes_unlock_script_when_closure_waits_for_slot(): void
    {
        $agent = $this->agentRdv();
        $demande = $this->activeDemande(
            now()->addDay()->toDateString(),
            '14:00'
        );
        $opensAt = $demande->rencontreScheduledAt();
        $this->assertNotNull($opensAt);
        $this->assertTrue($opensAt->isFuture());

        $this->actingAsConfirmed($agent)
            ->get(route('personal.request.show', $demande->id))
            ->assertOk()
            ->assertSee('Clôture (réalisé / non honoré) possible à partir du', false)
            ->assertSee('__dpcScheduleUnlockReload', false)
            ->assertSee($opensAt->toIso8601String(), false)
            ->assertDontSee('Clôturer le rendez-vous', false);
    }

    public function test_personal_request_shows_closure_actions_without_unlock_when_slot_passed(): void
    {
        $agent = $this->agentRdv();
        $demande = $this->activeDemande(
            now()->subDay()->toDateString(),
            '09:00'
        );

        $this->actingAsConfirmed($agent)
            ->get(route('personal.request.show', $demande->id))
            ->assertOk()
            ->assertSee('Clôturer le rendez-vous', false)
            ->assertSee('Marquer réalisé', false)
            ->assertDontSee('Clôture (réalisé / non honoré) possible à partir du', false)
            ->assertDontSee('__dpcScheduleUnlockReload', false);
    }
}

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

class RencontrePilotageStatutTest extends TestCase
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

    /** @test */
    public function pilotage_programmes_includes_rencontre_without_stored_statut_but_workflow_actif(): void
    {
        $formaliteId = Service::where('code', Service::FORMALITE)->value('id');
        $manager = User::factory()->create(['service_id' => $formaliteId]);
        $manager->assignRole(User::ROLE_VALIDATEUR_RDV);

        $actifOwner = User::factory()->create([
            'name' => 'Liste Programmee',
            'firstname' => 'Liste',
            'lastname' => 'Programmee',
        ]);

        Demande::create([
            'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => $actifOwner->id,
            'current_service_id' => $formaliteId,
            'current_step_id' => WorkflowStep::idForCode('ACTIF', TypeDemandeEnum::DEMANDE_RENCONTRE->value),
            'data' => [
                'motif_key' => 'demande_formalites_mandats',
                'modalite' => 'visio',
                'date_souhaitee' => now()->addDay()->toDateString(),
                'heure_souhaitee' => '10:00',
            ],
        ]);

        $closOwner = User::factory()->create([
            'name' => 'Liste Historique',
            'firstname' => 'Liste',
            'lastname' => 'Historique',
        ]);

        Demande::create([
            'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => $closOwner->id,
            'current_service_id' => $formaliteId,
            'current_step_id' => WorkflowStep::idForCode('REALISE', TypeDemandeEnum::DEMANDE_RENCONTRE->value),
            'data' => [
                'motif_key' => 'demande_formalites_mandats',
                'modalite' => 'visio',
                'date_souhaitee' => now()->subDay()->toDateString(),
                'heure_souhaitee' => '10:00',
            ],
        ]);

        $this->actingAsConfirmed($manager)
            ->get(route('rencontres.pilotage.index', ['filtre' => 'programmes']))
            ->assertOk()
            ->assertSee('Liste Programmee')
            ->assertDontSee('Liste Historique');
    }

    /** @test */
    public function pilotage_historique_includes_terminal_from_workflow_step_without_rdv_statut(): void
    {
        $formaliteId = Service::where('code', Service::FORMALITE)->value('id');
        $manager = User::factory()->create(['service_id' => $formaliteId]);
        $manager->assignRole(User::ROLE_VALIDATEUR_RDV);

        $realise = Demande::create([
            'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => User::factory()->create()->id,
            'current_service_id' => $formaliteId,
            'current_step_id' => WorkflowStep::idForCode('REALISE', TypeDemandeEnum::DEMANDE_RENCONTRE->value),
            'data' => [
                'motif_key' => 'demande_formalites_mandats',
                'prenom' => 'Marie',
                'nom' => 'Clos',
                'modalite' => 'physique',
                'date_souhaitee' => now()->subDays(2)->toDateString(),
                'heure_souhaitee' => '11:00',
            ],
        ]);

        $this->actingAsConfirmed($manager)
            ->get(route('rencontres.pilotage.index', ['filtre' => 'historique']))
            ->assertOk()
            ->assertSee('Marie Clos');
    }

    /** @test */
    public function pilotage_show_prompts_closure_when_actif_and_slot_passed(): void
    {
        $formaliteId = Service::where('code', Service::FORMALITE)->value('id');
        $manager = User::factory()->create(['service_id' => $formaliteId]);
        $manager->assignRole(User::ROLE_VALIDATEUR_RDV);

        $demande = Demande::create([
            'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => User::factory()->create()->id,
            'current_service_id' => $formaliteId,
            'current_step_id' => WorkflowStep::idForCode('ACTIF', TypeDemandeEnum::DEMANDE_RENCONTRE->value),
            'data' => [
                'motif_key' => 'demande_formalites_mandats',
                'rdv_statut' => RencontreStatutEnum::ACTIF->value,
                'modalite' => 'physique',
                'date_souhaitee' => now()->subDay()->toDateString(),
                'heure_souhaitee' => '09:00',
            ],
        ]);

        $this->actingAsConfirmed($manager)
            ->get(route('rencontres.pilotage.show', $demande))
            ->assertOk()
            ->assertSee('Clôture en attente')
            ->assertSee('Parcours du rendez-vous');
    }
}

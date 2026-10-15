<?php

namespace Tests\Feature;

use App\Enums\RencontreStatutEnum;
use App\Enums\TypeDemandeEnum;
use App\Models\AnneeFiscale;
use App\Models\Demande;
use App\Models\Formalite;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkflowStep;
use Database\Seeders\AnneeFiscaleSeeder;
use Database\Seeders\WorkflowSteps\RencontreWorkflowStepSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class FormaliteSuiviTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        $this->seedServices();
        $this->seedWorkflowSteps();
        $this->seed(RencontreWorkflowStepSeeder::class);
        $this->seed(AnneeFiscaleSeeder::class);
    }

    private function responsable(): User
    {
        $user = User::factory()->create([
            'service_id' => Service::where('code', Service::FORMALITE)->value('id'),
        ]);
        $user->assignRole('responsable_service_accueil_formalites');

        return $user;
    }

    public function test_responsable_can_open_formalite_suivi_dashboard(): void
    {
        $this->actingAsConfirmed($this->responsable())
            ->get(route('formalites.suivi.index'))
            ->assertOk()
            ->assertSee('Suivi des formalités fiscales', false)
            ->assertSee('En attente d’enregistrement', false);
    }

    public function test_suivi_lists_completed_and_pending_formalites(): void
    {
        $annee = AnneeFiscale::active();
        $this->assertNotNull($annee);

        $pensionne = User::factory()->create();
        $agent = User::factory()->create();
        $agent->assignRole(User::ROLE_AGENT_FORMALITES);

        Formalite::create([
            'user_id' => $pensionne->id,
            'annee_fiscale_id' => $annee->id,
            'realisee_at' => now(),
            'realisee_par' => $agent->id,
        ]);

        $pendingOwner = User::factory()->create();
        Demande::create([
            'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => $pendingOwner->id,
            'current_service_id' => Service::where('code', Service::FORMALITE)->value('id'),
            'current_step_id' => WorkflowStep::idForCode('ACTIF', TypeDemandeEnum::DEMANDE_RENCONTRE->value),
            'data' => [
                'motif_key' => 'demande_formalites_mandats',
                'rdv_statut' => RencontreStatutEnum::REALISE->value,
                'date_souhaitee' => now()->subDay()->toDateString(),
                'heure_souhaitee' => '10:00',
            ],
        ]);

        $this->actingAsConfirmed($this->responsable())
            ->get(route('formalites.suivi.index', ['filtre' => 'remplies']))
            ->assertOk()
            ->assertSee($pensionne->displayName(), false);

        $this->actingAsConfirmed($this->responsable())
            ->get(route('formalites.suivi.index', ['filtre' => 'en_attente']))
            ->assertOk()
            ->assertSee($pendingOwner->displayName(), false)
            ->assertSee('Enregistrer', false);
    }

    public function test_rdv_with_formalite_enregistree_not_listed_as_a_cloturer(): void
    {
        $annee = AnneeFiscale::active();
        $this->assertNotNull($annee);

        $pensionne = User::factory()->create();
        $agent = User::factory()->create();
        $agent->assignRole(User::ROLE_AGENT_FORMALITES);

        $demande = Demande::create([
            'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => $pensionne->id,
            'current_service_id' => Service::where('code', Service::FORMALITE)->value('id'),
            'current_step_id' => WorkflowStep::idForCode('ACTIF', TypeDemandeEnum::DEMANDE_RENCONTRE->value),
            'data' => [
                'motif_key' => 'demande_formalites_mandats',
                'rdv_statut' => RencontreStatutEnum::ACTIF->value,
                'date_souhaitee' => now()->subDay()->toDateString(),
                'heure_souhaitee' => '10:00',
            ],
        ]);

        Formalite::create([
            'user_id' => $pensionne->id,
            'annee_fiscale_id' => $annee->id,
            'realisee_at' => now(),
            'realisee_par' => $agent->id,
            'demande_id' => $demande->id,
        ]);

        $this->actingAsConfirmed($this->responsable())
            ->get(route('formalites.suivi.index', ['filtre' => 'a_cloturer']))
            ->assertOk()
            ->assertDontSee($pensionne->displayName(), false);
    }
}

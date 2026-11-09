<?php

namespace Tests\Unit\Services\Formalites;

use App\Enums\RencontreStatutEnum;
use App\Enums\TypeDemandeEnum;
use App\Models\AnneeFiscale;
use App\Models\Demande;
use App\Models\Formalite;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkflowStep;
use App\Services\Formalites\FormaliteSuiviService;
use Database\Seeders\AnneeFiscaleSeeder;
use Database\Seeders\WorkflowSteps\RencontreWorkflowStepSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class FormaliteSuiviServiceTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRequiredData;

    private FormaliteSuiviService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        $this->seedServices();
        $this->seedWorkflowSteps();
        $this->seed(RencontreWorkflowStepSeeder::class);
        $this->seed(AnneeFiscaleSeeder::class);
        $this->service = app(FormaliteSuiviService::class);
    }

    /** @test */
    public function stats_count_filled_formalites_and_pending_after_realised_rdv(): void
    {
        $annee = AnneeFiscale::active();
        $this->assertNotNull($annee);

        $pensionne = User::factory()->create();
        $agent = User::factory()->create();

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
            'current_step_id' => WorkflowStep::idForCode('REALISE', TypeDemandeEnum::DEMANDE_RENCONTRE->value),
            'data' => [
                'motif_key' => 'demande_formalites_mandats',
                'rdv_statut' => RencontreStatutEnum::REALISE->value,
                'date_souhaitee' => now()->subDay()->toDateString(),
                'heure_souhaitee' => '10:00',
            ],
        ]);

        $stats = $this->service->stats($annee->id);

        $this->assertSame(1, $stats['remplies']);
        $this->assertSame(1, $stats['en_attente']);
        $this->assertSame(1, $stats['rdv_realises']);
    }
}

<?php

namespace Tests\Feature;

use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\User;
use App\Models\WorkflowStep;
use App\Notifications\RappelRencontreAppelNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class RappelRencontreTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        $this->seedStatuses();
    }

    public function test_command_notifies_formalites_agents_the_day_before(): void
    {
        Notification::fake();

        $agent = User::factory()->create(['name' => 'Agent RDV']);
        $agent->assignRole(User::ROLE_AGENT_RDV);

        $demande = Demande::create([
            'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => User::factory()->create()->id,
            'current_step_id' => WorkflowStep::idForCode('SOUMISE'),
            'data' => [
                'prenom' => 'Jean',
                'nom' => 'Pierre',
                'telephone' => '+50938123456',
                'date_souhaitee' => now()->addDay()->toDateString(),
                'heure_souhaitee' => '14:15',
                'modalite' => 'physique',
            ],
        ]);

        $this->artisan('rdv:rappels-veille')->assertSuccessful();

        Notification::assertSentTo($agent, RappelRencontreAppelNotification::class);
        $this->assertNotEmpty($demande->fresh()->data['rappel_veille_envoye_at'] ?? null);

        Notification::fake();
        $this->artisan('rdv:rappels-veille')->assertSuccessful();
        Notification::assertNothingSent();
    }

    public function test_cancelled_appointments_are_not_reminded(): void
    {
        Notification::fake();

        $agent = User::factory()->create();
        $agent->assignRole(User::ROLE_AGENT_RDV);

        Demande::create([
            'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => User::factory()->create()->id,
            'current_step_id' => WorkflowStep::idForCode('ANNULEE'),
            'data' => [
                'telephone' => '+50938123456',
                'date_souhaitee' => now()->addDay()->toDateString(),
                'heure_souhaitee' => '14:00',
            ],
        ]);

        $this->artisan('rdv:rappels-veille')->assertSuccessful();

        Notification::assertNothingSent();
    }
}

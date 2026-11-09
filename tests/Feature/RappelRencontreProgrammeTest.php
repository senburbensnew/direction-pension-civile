<?php

namespace Tests\Feature;

use App\Enums\RencontreStatutEnum;
use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\User;
use App\Models\WorkflowStep;
use App\Notifications\RappelRencontreProgrammeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class RappelRencontreProgrammeTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        $this->seedWorkflowSteps();
    }

    public function test_command_notifies_agent_and_booker_24h_before(): void
    {
        Notification::fake();

        $now = Carbon::parse('2026-06-10 14:00:00');
        Carbon::setTestNow($now);

        $agent = User::factory()->create(['name' => 'Agent RDV', 'email' => 'agent@example.com']);
        $agent->assignRole(User::ROLE_AGENT_RDV);

        $booker = User::factory()->create(['email' => 'booker@example.com']);

        $demande = Demande::create([
            'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => $booker->id,
            'current_step_id' => WorkflowStep::idForCode('ACTIF', TypeDemandeEnum::DEMANDE_RENCONTRE->value),
            'data' => [
                'prenom' => 'Jean',
                'nom' => 'Pierre',
                'telephone' => '+50938123456',
                'date_souhaitee' => '2026-06-11',
                'heure_souhaitee' => '14:00',
                'modalite' => 'physique',
                'agent_id' => $agent->id,
                'rdv_statut' => RencontreStatutEnum::ACTIF->value,
            ],
        ]);

        $this->artisan('rdv:rappels-programmes')->assertSuccessful();

        Notification::assertSentTo($agent, RappelRencontreProgrammeNotification::class);
        Notification::assertSentTo($booker, RappelRencontreProgrammeNotification::class);
        $this->assertNotEmpty($demande->fresh()->data['rappel_24h_envoye_at'] ?? null);

        Notification::fake();
        $this->artisan('rdv:rappels-programmes')->assertSuccessful();
        Notification::assertNothingSent();
    }

    public function test_command_notifies_30_minutes_before(): void
    {
        Notification::fake();

        $now = Carbon::parse('2026-06-11 13:30:00');
        Carbon::setTestNow($now);

        $agent = User::factory()->create();
        $agent->assignRole(User::ROLE_AGENT_RDV);
        $booker = User::factory()->create();

        Demande::create([
            'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => $booker->id,
            'current_step_id' => WorkflowStep::idForCode('ACTIF', TypeDemandeEnum::DEMANDE_RENCONTRE->value),
            'data' => [
                'date_souhaitee' => '2026-06-11',
                'heure_souhaitee' => '14:00',
                'modalite' => 'visio',
                'agent_id' => $agent->id,
                'rdv_statut' => RencontreStatutEnum::ACTIF->value,
            ],
        ]);

        $this->artisan('rdv:rappels-programmes')->assertSuccessful();

        Notification::assertSentToTimes($agent, RappelRencontreProgrammeNotification::class, 1);
        Notification::assertSentToTimes($booker, RappelRencontreProgrammeNotification::class, 1);
    }

    public function test_terminal_appointments_are_skipped(): void
    {
        Notification::fake();

        Carbon::setTestNow(Carbon::parse('2026-06-10 14:00:00'));

        $agent = User::factory()->create();
        $agent->assignRole(User::ROLE_AGENT_RDV);

        Demande::create([
            'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => User::factory()->create()->id,
            'current_step_id' => WorkflowStep::idForCode('ANNULE', TypeDemandeEnum::DEMANDE_RENCONTRE->value),
            'data' => [
                'date_souhaitee' => now()->addDay()->toDateString(),
                'heure_souhaitee' => '14:00',
                'agent_id' => $agent->id,
                'rdv_statut' => RencontreStatutEnum::ANNULE->value,
            ],
        ]);

        $this->artisan('rdv:rappels-programmes')->assertSuccessful();

        Notification::assertNothingSent();
    }
}

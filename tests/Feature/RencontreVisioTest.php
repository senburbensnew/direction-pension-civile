<?php

namespace Tests\Feature;

use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkflowStep;
use App\Services\RencontreVisioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class RencontreVisioTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        $this->seedWorkflowSteps();
        $this->seedServices();
    }

    private function makeVisioDemande(User $owner, User $agent, array $data = []): Demande
    {
        $demande = Demande::create([
            'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => $owner->id,
            'current_step_id' => WorkflowStep::idForCode('SOUMISE'),
            'data' => array_merge([
                'modalite' => 'visio',
                'agent_id' => $agent->id,
                'date_souhaitee' => now()->toDateString(),
                'heure_souhaitee' => now()->format('H:i'),
            ], $data),
        ]);

        return app(RencontreVisioService::class)->attachTo($demande);
    }

    public function test_visio_link_is_unique_and_only_owner_or_agent_can_open_it(): void
    {
        $owner = User::factory()->create();
        $agent = User::factory()->create();
        $other = User::factory()->create();
        $demande = $this->makeVisioDemande($owner, $agent);

        $this->assertNotEmpty($demande->visio_token);
        $this->assertSame(64, strlen($demande->visio_token));

        $this->actingAs($other)
            ->get(route('demandes.rencontre.visio', $demande->visio_token))
            ->assertForbidden();

        $this->actingAs($owner)
            ->get(route('demandes.rencontre.visio', $demande->visio_token))
            ->assertOk();

        $this->actingAs($agent)
            ->get(route('demandes.rencontre.visio', $demande->visio_token))
            ->assertOk();
    }

    public function test_visio_room_stays_closed_until_the_opening_window(): void
    {
        $owner = User::factory()->create();
        $agent = User::factory()->create();
        $demande = $this->makeVisioDemande($owner, $agent, [
            'date_souhaitee' => now()->addDay()->toDateString(),
            'heure_souhaitee' => '14:00',
        ]);

        $this->actingAs($owner)
            ->get(route('demandes.rencontre.visio', $demande->visio_token))
            ->assertOk()
            ->assertSee('Lien pas encore actif')
            ->assertDontSee('Salle réservée');
    }

    public function test_visio_room_is_active_near_the_appointment(): void
    {
        $owner = User::factory()->create();
        $agent = User::factory()->create();
        $start = now()->addMinutes(5);
        $demande = $this->makeVisioDemande($owner, $agent, [
            'date_souhaitee' => $start->toDateString(),
            'heure_souhaitee' => $start->format('H:i'),
        ]);

        $this->actingAs($owner)
            ->get(route('demandes.rencontre.visio', $demande->visio_token))
            ->assertOk()
            ->assertSee('Nouvel onglet')
            ->assertSee('Salle ouverte');
    }

    public function test_visio_page_shows_unavailable_when_schedule_is_missing(): void
    {
        $owner = User::factory()->create();
        $agent = User::factory()->create();
        $demande = $this->makeVisioDemande($owner, $agent, [
            'date_souhaitee' => null,
            'heure_souhaitee' => null,
        ]);

        $this->actingAs($owner)
            ->get(route('demandes.rencontre.visio', $demande->visio_token))
            ->assertOk()
            ->assertSee('Lien indisponible')
            ->assertDontSee('Salle ouverte');
    }

    public function test_realised_appointment_stays_accessible_during_visio_window(): void
    {
        $this->travelTo(now()->startOfDay()->setTime(14, 0));

        $owner = User::factory()->create();
        $agent = User::factory()->create();
        $demande = $this->makeVisioDemande($owner, $agent, [
            'date_souhaitee' => now()->toDateString(),
            'heure_souhaitee' => '14:00',
            'rdv_statut' => 'realise',
        ]);

        $this->actingAs($owner)
            ->get(route('demandes.rencontre.visio', $demande->visio_token))
            ->assertOk()
            ->assertSee('Salle ouverte');
    }

    public function test_unknown_token_is_not_found(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('demandes.rencontre.visio', str_repeat('ab', 32)))
            ->assertNotFound();
    }

    public function test_visio_status_json_reflects_opening_window(): void
    {
        $owner = User::factory()->create();
        $agent = User::factory()->create();
        $demande = $this->makeVisioDemande($owner, $agent, [
            'date_souhaitee' => now()->addDay()->toDateString(),
            'heure_souhaitee' => '14:00',
        ]);

        $this->actingAs($agent)
            ->getJson(route('demandes.rencontre.visio.status', $demande->visio_token))
            ->assertOk()
            ->assertJson([
                'status' => 'en_attente',
            ])
            ->assertJsonStructure(['status', 'opens_at', 'closes_at']);
    }

    public function test_visio_link_expires_at_end_of_slot_not_at_start(): void
    {
        $this->travelTo(now()->startOfDay()->setTime(14, 10));

        $owner = User::factory()->create();
        $agent = User::factory()->create();
        $demande = $this->makeVisioDemande($owner, $agent, [
            'date_souhaitee' => now()->toDateString(),
            'heure_souhaitee' => '14:00',
            'rdv_statut' => 'actif',
        ]);

        $visio = app(RencontreVisioService::class);

        $this->assertFalse($visio->isVisioLinkExpired($demande));
        $this->assertSame('actif', $visio->status($demande));
        $this->assertTrue(
            $visio->slotEndsAt($demande)?->equalTo(
                now()->startOfDay()->setTime(14, 15)
            ) ?? false
        );

        $this->travelTo(now()->startOfDay()->setTime(14, 15));

        $this->assertTrue($visio->isVisioLinkExpired($demande->fresh()));
        $this->assertSame('termine', $visio->status($demande->fresh()));

        $this->travelTo(now()->startOfDay()->setTime(14, 14));

        $this->assertFalse($visio->isVisioLinkExpired($demande->fresh()));
        $this->assertSame('actif', $visio->status($demande->fresh()));
    }

    public function test_confirmation_recap_hides_join_link_after_visio_window(): void
    {
        $this->travelTo(now()->startOfDay()->setTime(15, 0));

        $owner = User::factory()->create();
        $agent = User::factory()->create([
            'service_id' => Service::where('code', Service::FORMALITE)->value('id'),
        ]);
        $agent->assignRole(User::ROLE_AGENT_RDV);

        $demande = $this->makeVisioDemande($owner, $agent, [
            'date_souhaitee' => now()->toDateString(),
            'heure_souhaitee' => '14:00',
            'rdv_statut' => 'actif',
            'confirmation' => [
                'numero' => 'RDV-TEST',
                'mode' => 'Visioconférence',
                'date' => now()->toDateString(),
                'heure' => '14:00',
            ],
        ]);

        $demande->update([
            'current_service_id' => $agent->service_id,
        ]);

        $this->actingAsConfirmed($agent)
            ->get(route('personal.request.show', $demande->id))
            ->assertOk()
            ->assertSee('Rendez-vous terminé — lien expiré', false)
            ->assertDontSee('Rejoindre la visioconférence', false);

        $this->actingAsConfirmed($owner)
            ->get(route('personal.request.authenticated-user-request.show', $demande->id))
            ->assertOk()
            ->assertSee('Lien de visioconférence expiré', false)
            ->assertDontSee('Rejoindre la visioconférence', false);
    }
}

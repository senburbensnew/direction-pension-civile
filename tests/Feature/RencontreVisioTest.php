<?php

namespace Tests\Feature;

use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
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
        $this->seedStatuses();
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
            ->assertSee('Salle réservée');
    }

    public function test_unknown_token_is_not_found(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('demandes.rencontre.visio', str_repeat('ab', 32)))
            ->assertNotFound();
    }
}

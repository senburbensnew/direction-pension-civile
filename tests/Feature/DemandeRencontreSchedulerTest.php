<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use App\Notifications\DemandeStatusChangedNotification;
use App\Notifications\RencontreAgentAssigneNotification;
use App\Services\RencontreAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class DemandeRencontreSchedulerTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        $this->seedServices();
        Storage::fake('public');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validStorePayload(User $agent, array $overrides = []): array
    {
        return array_merge([
            'motif' => 'suivi_dossier',
            'date_souhaitee' => now()->next('Wednesday')->toDateString(),
            'heure_souhaitee' => '14:30',
            'modalite' => 'physique',
            'lieu_rdv' => 'Siège de la DPC — Port-au-Prince',
            'agent_id' => $agent->id,
            'carte_pension' => UploadedFile::fake()->image('carte.jpg'),
            'prenom' => 'Jean',
            'nom' => 'Pierre',
            'numero_pension' => 'P-100',
            'telephone' => '+50938123456',
            'email' => 'jean.pierre@example.com',
            'confirmation_lu_accepte' => '1',
        ], $overrides);
    }

    private function makePensionne(array $overrides = []): User
    {
        $user = User::factory()->create($overrides);
        $user->assignRole('pensionne');

        return $user;
    }

    private function makeFormalitesAgent(array $overrides = []): User
    {
        $agent = User::factory()->create(array_merge([
            'service_id' => Service::where('code', Service::FORMALITE)->value('id'),
        ], $overrides));
        $agent->assignRole(User::ROLE_AGENT_RDV);

        return $agent;
    }

    public function test_guest_is_asked_to_log_in_to_open_the_appointment_page(): void
    {
        $this->get(route('demandes.rencontre.create'))
            ->assertRedirect(route('login'));
    }

    public function test_non_pensionne_cannot_open_the_appointment_page(): void
    {
        $user = User::factory()->create();
        $user->assignRole('fonctionnaire');

        $this->actingAs($user)
            ->get(route('demandes.rencontre.create'))
            ->assertForbidden();
    }

    public function test_appointment_page_lets_the_user_choose_the_mode(): void
    {
        $user = $this->makePensionne();
        $agent = $this->makeFormalitesAgent();

        $this->actingAs($user)
            ->get(route('demandes.rencontre.create'))
            ->assertOk()
            ->assertSee('Demande de rendez-vous')
            ->assertSee('Présentiel')
            ->assertSee('Visioconférence')
            ->assertSee('Préciser le motif')
            ->assertSee('Demande de pension / liquidation')
            ->assertSee('Suivi d’un dossier de pension')
            ->assertSee('Désignation de mandataire')
            ->assertSee('Autre demande')
            ->assertSee('Service responsable')
            ->assertSee('Rendez-vous auprès de la Direction')
            ->assertSee('Formalités')
            ->assertSee($agent->displayName())
            ->assertSee('Carte de pension')
            ->assertSee('14h00')
            ->assertSee('15 minutes')
            ->assertSee('id="dpc-rdv-scheduler"', false)
            ->assertSee('name="date_souhaitee"', false)
            ->assertSee('name="heure_souhaitee"', false)
            ->assertSee('name="agent_id"', false)
            ->assertSee('name="carte_pension"', false);
    }

    public function test_identity_fields_are_prefilled_from_the_logged_in_user(): void
    {
        $user = $this->makePensionne([
            'firstname' => 'Marie',
            'lastname' => 'Joseph',
            'pension_code' => 'PEN-123456',
            'phone' => '+50938123456',
            'email' => 'marie.joseph@example.com',
        ]);

        $this->actingAs($user)
            ->get(route('demandes.rencontre.create'))
            ->assertOk()
            ->assertSee('value="Marie"', false)
            ->assertSee('value="Joseph"', false)
            ->assertSee('value="PEN-123456"', false)
            ->assertSee('value="+50938123456"', false)
            ->assertSee('value="marie.joseph@example.com"', false);
    }

    public function test_legacy_physical_url_redirects_to_the_unified_page(): void
    {
        $user = $this->makePensionne();

        $this->actingAs($user)
            ->get(route('demandes.rencontre.physique'))
            ->assertRedirect(route('demandes.rencontre.create', ['modalite' => 'physique']));
    }

    public function test_scheduler_exposes_reserved_slots_but_not_rejected_ones(): void
    {
        $this->seedStatuses();
        $user = $this->makePensionne();
        $other = $this->makePensionne();
        $agent = $this->makeFormalitesAgent();
        $takenDate = now()->next('Wednesday')->toDateString();
        $freeDate = now()->next('Thursday')->toDateString();

        \App\Models\Demande::create([
            'type' => \App\Enums\TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => $other->id,
            'current_step_id' => \App\Models\WorkflowStep::idForCode('SOUMISE'),
            'data' => [
                'date_souhaitee' => $takenDate,
                'heure_souhaitee' => '14:00',
                'modalite' => 'visio',
                'agent_id' => $agent->id,
            ],
        ]);

        \App\Models\Demande::create([
            'type' => \App\Enums\TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => $user->id,
            'current_step_id' => \App\Models\WorkflowStep::idForCode('REJETEE'),
            'data' => [
                'date_souhaitee' => $freeDate,
                'heure_souhaitee' => '14:15',
                'modalite' => 'physique',
                'agent_id' => $agent->id,
            ],
        ]);

        $this->actingAs($user)
            ->get(route('demandes.rencontre.create'))
            ->assertOk()
            ->assertSee('data-reserved', false)
            ->assertSee($takenDate, false)
            ->assertSee('14:00', false)
            ->assertDontSee($freeDate);
    }

    public function test_store_rejects_an_already_reserved_slot_for_the_same_agent(): void
    {
        $this->seedStatuses();
        $user = $this->makePensionne();
        $other = $this->makePensionne();
        $agent = $this->makeFormalitesAgent();
        $date = now()->next('Wednesday')->toDateString();

        \App\Models\Demande::create([
            'type' => \App\Enums\TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => $other->id,
            'current_step_id' => \App\Models\WorkflowStep::idForCode('SOUMISE'),
            'data' => [
                'date_souhaitee' => $date,
                'heure_souhaitee' => '14:30',
                'modalite' => 'physique',
                'agent_id' => $agent->id,
            ],
        ]);

        $this->actingAs($user)
            ->from(route('demandes.rencontre.create'))
            ->post(route('demandes.rencontre.store'), $this->validStorePayload($agent, [
                'date_souhaitee' => $date,
                'heure_souhaitee' => '14:30',
            ]))
            ->assertRedirect(route('demandes.rencontre.create'))
            ->assertSessionHasErrors('heure_souhaitee');
    }

    public function test_same_slot_can_be_booked_by_another_formalites_agent(): void
    {
        $this->seedStatuses();
        $userA = $this->makePensionne();
        $userB = $this->makePensionne();
        $agentA = $this->makeFormalitesAgent();
        $agentB = $this->makeFormalitesAgent();
        $date = now()->next('Wednesday')->toDateString();

        \App\Models\Demande::create([
            'type' => \App\Enums\TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => $userA->id,
            'current_step_id' => \App\Models\WorkflowStep::idForCode('SOUMISE'),
            'data' => [
                'date_souhaitee' => $date,
                'heure_souhaitee' => '14:30',
                'modalite' => 'physique',
                'agent_id' => $agentA->id,
            ],
        ]);

        $this->actingAs($userB)
            ->from(route('demandes.rencontre.create'))
            ->post(route('demandes.rencontre.store'), $this->validStorePayload($agentB, [
                'date_souhaitee' => $date,
                'heure_souhaitee' => '14:30',
            ]))
            ->assertRedirect(route('demandes.rencontre.create'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('demandes', [
            'type' => \App\Enums\TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => $userB->id,
        ]);
        $this->assertTrue(\App\Models\Demande::query()
            ->where('type', \App\Enums\TypeDemandeEnum::DEMANDE_RENCONTRE->value)
            ->where('created_by', $userB->id)
            ->get()
            ->contains(fn ($demande) => (int) ($demande->data['agent_id'] ?? 0) === $agentB->id));
    }

    public function test_store_sends_a_single_submission_notification_to_the_owner(): void
    {
        $this->seedStatuses();
        Notification::fake();

        $user = $this->makePensionne();
        $agent = $this->makeFormalitesAgent();

        $this->actingAs($user)
            ->post(route('demandes.rencontre.store'), $this->validStorePayload($agent))
            ->assertRedirect(route('demandes.rencontre.create'));

        Notification::assertSentToTimes($user, DemandeStatusChangedNotification::class, 1);
        Notification::assertSentToTimes($agent, RencontreAgentAssigneNotification::class, 1);
        Notification::assertNotSentTo($user, RencontreAgentAssigneNotification::class);
    }

    public function test_appointment_does_not_enter_the_direction_circuit(): void
    {
        $this->seedStatuses();
        Notification::fake();

        $user = $this->makePensionne();
        $agent = $this->makeFormalitesAgent();
        $directionUser = User::factory()->create([
            'service_id' => Service::where('code', Service::DIRECTION)->value('id'),
        ]);
        $directionUser->assignRole('direction');

        $this->actingAs($user)
            ->post(route('demandes.rencontre.store'), $this->validStorePayload($agent))
            ->assertRedirect(route('demandes.rencontre.create'));

        $demande = \App\Models\Demande::query()
            ->where('type', \App\Enums\TypeDemandeEnum::DEMANDE_RENCONTRE->value)
            ->where('created_by', $user->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($demande);
        $this->assertSame(Service::where('code', Service::FORMALITE)->value('id'), $demande->current_service_id);
        $this->assertNull($demande->circuitSnapshot);
        $this->assertDatabaseMissing('demande_interactions', [
            'demande_id' => $demande->id,
            'commentaire' => 'Soumission initiale — réception Direction',
        ]);
        Notification::assertNotSentTo($directionUser, \App\Notifications\DemandeSubmittedNotification::class);
        $this->assertTrue(app(\App\Services\DemandeWorkflowService::class)->availableTransferOptions($demande)->isEmpty());
    }

    public function test_store_rejects_a_slot_outside_the_afternoon_window(): void
    {
        $this->seedStatuses();
        $user = $this->makePensionne();
        $agent = $this->makeFormalitesAgent();

        $this->actingAs($user)
            ->from(route('demandes.rencontre.create'))
            ->post(route('demandes.rencontre.store'), $this->validStorePayload($agent, [
                'heure_souhaitee' => '10:00',
            ]))
            ->assertRedirect(route('demandes.rencontre.create'))
            ->assertSessionHasErrors('heure_souhaitee');
    }

    public function test_only_users_with_the_agent_rdv_role_are_bookable(): void
    {
        $user = $this->makePensionne();
        $listed = $this->makeFormalitesAgent(['name' => 'Agent RDV visible']);
        User::factory()->create([
            'service_id' => Service::where('code', Service::FORMALITE)->value('id'),
            'name' => 'Agent sans rôle RDV',
        ]);

        $this->actingAs($user)
            ->get(route('demandes.rencontre.create'))
            ->assertOk()
            ->assertSee($listed->displayName())
            ->assertDontSee('Agent sans rôle RDV');
    }

    public function test_formalites_offers_eight_fifteen_minute_slots(): void
    {
        $times = app(RencontreAvailabilityService::class)->allowedTimes();

        $this->assertSame([
            '14:00', '14:15', '14:30', '14:45',
            '15:00', '15:15', '15:30', '15:45',
        ], $times);
    }

    public function test_user_can_cancel_a_visio_appointment_and_free_the_slot(): void
    {
        $this->seedStatuses();
        $user = $this->makePensionne();
        $agent = $this->makeFormalitesAgent();
        $date = now()->next('Wednesday')->toDateString();

        $demande = \App\Models\Demande::create([
            'type' => \App\Enums\TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => $user->id,
            'current_step_id' => \App\Models\WorkflowStep::idForCode('SOUMISE'),
            'data' => [
                'objet' => 'Suivi de dossier',
                'date_souhaitee' => $date,
                'heure_souhaitee' => '14:00',
                'modalite' => 'visio',
                'agent_id' => $agent->id,
            ],
        ]);

        $this->actingAs($user)
            ->from(route('demandes.rencontre.create'))
            ->post(route('demandes.rencontre.annuler', $demande))
            ->assertRedirect(route('demandes.rencontre.create'))
            ->assertSessionHas('success');

        $demande->refresh();
        $this->assertSame('ANNULEE', $demande->currentStep?->code);
        $this->assertFalse(\App\Models\Demande::isRencontreSlotTaken($date, '14:00', $agent->id));
    }

    public function test_user_can_cancel_a_physical_appointment(): void
    {
        $this->seedStatuses();
        $user = $this->makePensionne();
        $date = now()->next('Thursday')->toDateString();

        $demande = \App\Models\Demande::create([
            'type' => \App\Enums\TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => $user->id,
            'current_step_id' => \App\Models\WorkflowStep::idForCode('APPROUVEE'),
            'data' => [
                'objet' => 'Dépôt de pièces',
                'date_souhaitee' => $date,
                'heure_souhaitee' => '14:15',
                'modalite' => 'physique',
            ],
        ]);

        $this->actingAs($user)
            ->from(route('demandes.rencontre.create'))
            ->post(route('demandes.rencontre.annuler', $demande))
            ->assertRedirect(route('demandes.rencontre.create'))
            ->assertSessionHas('success');

        $this->assertSame('ANNULEE', $demande->fresh()->currentStep?->code);
    }

    public function test_pensionne_cannot_book_a_second_active_appointment(): void
    {
        $this->seedStatuses();
        $user = $this->makePensionne();
        $agent = $this->makeFormalitesAgent();
        $date = now()->next('Wednesday')->toDateString();

        \App\Models\Demande::create([
            'type' => \App\Enums\TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => $user->id,
            'current_step_id' => \App\Models\WorkflowStep::idForCode('SOUMISE'),
            'data' => [
                'date_souhaitee' => $date,
                'heure_souhaitee' => '14:00',
                'modalite' => 'physique',
                'agent_id' => $agent->id,
                'rdv_statut' => \App\Enums\RencontreStatutEnum::DEMANDE->value,
            ],
        ]);

        $this->actingAs($user)
            ->get(route('demandes.rencontre.create'))
            ->assertOk()
            ->assertSee('Un rendez-vous est déjà en cours')
            ->assertDontSee('id="main-form"', false);

        $this->actingAs($user)
            ->from(route('demandes.rencontre.create'))
            ->post(route('demandes.rencontre.store'), $this->validStorePayload($agent, [
                'date_souhaitee' => now()->next('Thursday')->toDateString(),
                'heure_souhaitee' => '14:30',
            ]))
            ->assertRedirect(route('demandes.rencontre.create'))
            ->assertSessionHasErrors('date_souhaitee');
    }

    public function test_pensionne_can_book_again_after_cancelling_the_active_appointment(): void
    {
        $this->seedStatuses();
        $user = $this->makePensionne();
        $agent = $this->makeFormalitesAgent();
        $date = now()->next('Wednesday')->toDateString();

        $demande = \App\Models\Demande::create([
            'type' => \App\Enums\TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => $user->id,
            'current_step_id' => \App\Models\WorkflowStep::idForCode('SOUMISE'),
            'data' => [
                'date_souhaitee' => $date,
                'heure_souhaitee' => '14:00',
                'modalite' => 'visio',
                'agent_id' => $agent->id,
                'rdv_statut' => \App\Enums\RencontreStatutEnum::DEMANDE->value,
            ],
        ]);

        $this->actingAs($user)
            ->post(route('demandes.rencontre.annuler', $demande))
            ->assertRedirect(route('demandes.rencontre.create'));

        $this->actingAs($user)
            ->from(route('demandes.rencontre.create'))
            ->post(route('demandes.rencontre.store'), $this->validStorePayload($agent, [
                'date_souhaitee' => now()->next('Thursday')->toDateString(),
                'heure_souhaitee' => '14:30',
            ]))
            ->assertRedirect(route('demandes.rencontre.create'))
            ->assertSessionHas('success');
    }

    public function test_user_cannot_cancel_someone_elses_appointment(): void
    {
        $this->seedStatuses();
        $owner = $this->makePensionne();
        $other = $this->makePensionne();

        $demande = \App\Models\Demande::create([
            'type' => \App\Enums\TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => $owner->id,
            'current_step_id' => \App\Models\WorkflowStep::idForCode('SOUMISE'),
            'data' => [
                'date_souhaitee' => now()->next('Wednesday')->toDateString(),
                'heure_souhaitee' => '15:00',
                'modalite' => 'visio',
            ],
        ]);

        $this->actingAs($other)
            ->post(route('demandes.rencontre.annuler', $demande))
            ->assertForbidden();
    }
}

<?php

namespace Tests\Feature;

use App\Enums\RencontreStatutEnum;
use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkflowStep;
use App\Notifications\DemandeStatusChangedNotification;
use App\Notifications\RencontreAgentAssigneNotification;
use App\Services\Account\UserCategoryRoleSyncService;
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
            'motif' => 'demande_formalites_mandats',
            'date_souhaitee' => now()->next('Wednesday')->toDateString(),
            'heure_souhaitee' => '14:30',
            'modalite' => 'visio',
            'lieu_rdv' => 'Siège de la DPC — Port-au-Prince',
            'doc_carte_pension' => UploadedFile::fake()->image('carte.jpg'),
            'doc_piece_identite' => UploadedFile::fake()->image('id.jpg'),
            'telephone' => '+50938123456',
            'confirmation_lu_accepte' => '1',
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $dataOverrides
     */
    private function createRencontreDemande(
        User $owner,
        array $dataOverrides = [],
        ?string $stepCode = 'SOUMISE'
    ): Demande {
        return Demande::create([
            'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => $owner->id,
            'current_step_id' => $stepCode
                ? WorkflowStep::idForCode($stepCode)
                : null,
            'data' => array_merge([
                'motif_key' => 'demande_formalites_mandats',
                'rdv_statut' => RencontreStatutEnum::ACTIF->value,
                'date_souhaitee' => now()->next('Wednesday')->toDateString(),
                'heure_souhaitee' => '14:00',
                'modalite' => 'visio',
            ], $dataOverrides),
        ]);
    }

    private function makePensionne(array $overrides = []): User
    {
        $user = User::factory()->create($overrides);
        $user->assignRole('pensionne');
        app(UserCategoryRoleSyncService::class)
            ->assignCategory($user, 'pensionne', primary: true, syncRoles: false);

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

    private function formalitesService(): Service
    {
        return Service::where('code', Service::FORMALITE)->firstOrFail();
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
        $this->makeFormalitesAgent();

        $this->actingAs($user)
            ->get(route('demandes.rencontre.create'))
            ->assertOk()
            ->assertSee('Demande de rendez-vous')
            ->assertSee('Présentiel')
            ->assertSee('Visioconférence')
            ->assertSee('Préciser le motif')
            ->assertSee('Demande de formalités de pension et mandats')
            ->assertDontSee('Finalisation de la création de compte')
            ->assertSee('Service responsable')
            ->assertSee('Formalités')
            ->assertSee('Recommandé')
            ->assertSee('jours fériés')
            ->assertSee('id="dpc-rdv-scheduler"', false)
            ->assertSee('name="date_souhaitee"', false)
            ->assertSee('name="heure_souhaitee"', false)
            ->assertSee('name="doc_carte_pension"', false);
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

        $this->createRencontreDemande($other, [
            'date_souhaitee' => $takenDate,
            'heure_souhaitee' => '14:00',
            'agent_id' => $agent->id,
        ]);

        $this->createRencontreDemande($user, [
            'date_souhaitee' => $freeDate,
            'heure_souhaitee' => '14:15',
            'modalite' => 'physique',
            'agent_id' => $agent->id,
            'rdv_statut' => RencontreStatutEnum::REFUSE->value,
        ], 'REJETEE');

        $availability = app(RencontreAvailabilityService::class);
        $unavailable = $availability->unavailableSlotsForService(
            $this->formalitesService()
        );

        $this->assertContains("{$takenDate} 14:00", $unavailable);
        $this->assertNotContains("{$freeDate} 14:15", $unavailable);

        $this->actingAs($user)
            ->get(route('demandes.rencontre.create'))
            ->assertOk()
            ->assertSee('data-reserved-by-service', false)
            ->assertSee($takenDate, false);
    }

    public function test_store_rejects_an_already_reserved_slot_for_the_same_agent(): void
    {
        $this->seedStatuses();
        $user = $this->makePensionne();
        $other = $this->makePensionne();
        $agent = $this->makeFormalitesAgent();
        $date = now()->next('Wednesday')->toDateString();

        $this->createRencontreDemande($other, [
            'date_souhaitee' => $date,
            'heure_souhaitee' => '14:30',
            'modalite' => 'physique',
            'agent_id' => $agent->id,
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

        $this->createRencontreDemande($userA, [
            'date_souhaitee' => $date,
            'heure_souhaitee' => '14:30',
            'modalite' => 'physique',
            'agent_id' => $agentA->id,
        ]);

        $this->actingAs($userB)
            ->from(route('demandes.rencontre.create'))
            ->post(route('demandes.rencontre.store'), $this->validStorePayload($agentB, [
                'date_souhaitee' => $date,
                'heure_souhaitee' => '14:30',
            ]))
            ->assertRedirect(route('demandes.rencontre.create'))
            ->assertSessionHas('success');

        $this->assertTrue(Demande::query()
            ->where('type', TypeDemandeEnum::DEMANDE_RENCONTRE->value)
            ->where('created_by', $userB->id)
            ->get()
            ->contains(
                fn ($demande) => (int) ($demande->data['agent_id'] ?? 0) === $agentB->id
            ));
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

        Notification::assertSentToTimes($agent, RencontreAgentAssigneNotification::class, 1);
        Notification::assertNotSentTo($user, RencontreAgentAssigneNotification::class);
        Notification::assertNotSentTo($user, DemandeStatusChangedNotification::class);
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

        $demande = Demande::query()
            ->where('type', TypeDemandeEnum::DEMANDE_RENCONTRE->value)
            ->where('created_by', $user->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($demande);
        $this->assertSame(
            Service::where('code', Service::FORMALITE)->value('id'),
            $demande->current_service_id
        );
        $this->assertNull($demande->circuitSnapshot);
        Notification::assertNotSentTo(
            $directionUser,
            \App\Notifications\DemandeSubmittedNotification::class
        );
        $this->assertTrue(
            app(\App\Services\DemandeWorkflowService::class)
                ->availableTransferOptions($demande)
                ->isEmpty()
        );
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
        $listed = $this->makeFormalitesAgent(['name' => 'Agent RDV visible']);
        User::factory()->create([
            'service_id' => Service::where('code', Service::FORMALITE)->value('id'),
            'name' => 'Agent sans rôle RDV',
        ]);

        $agents = app(RencontreAvailabilityService::class)
            ->bookingAgents($this->formalitesService());

        $this->assertTrue(
            $agents->contains(fn (User $user) => $user->id === $listed->id)
        );
        $this->assertFalse(
            $agents->contains(fn (User $user) => $user->name === 'Agent sans rôle RDV')
        );
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
        $availability = app(RencontreAvailabilityService::class);

        $demande = $this->createRencontreDemande($user, [
            'objet' => 'Suivi de dossier',
            'date_souhaitee' => $date,
            'heure_souhaitee' => '14:00',
            'modalite' => 'visio',
            'agent_id' => $agent->id,
        ]);

        $this->assertTrue(
            $availability->agentHasAppointmentAt($agent, $date, '14:00')
        );

        $this->actingAs($user)
            ->from(route('demandes.rencontre.create'))
            ->post(route('demandes.rencontre.annuler', $demande))
            ->assertRedirect(route('demandes.rencontre.create'))
            ->assertSessionHas('success');

        $demande->refresh();
        $this->assertSame(
            RencontreStatutEnum::ANNULE,
            $demande->rencontreStatut()
        );
        $this->assertFalse(
            $availability->agentHasAppointmentAt($agent, $date, '14:00')
        );
    }

    public function test_user_can_cancel_a_physical_appointment(): void
    {
        $this->seedStatuses();
        $user = $this->makePensionne();
        $date = now()->next('Thursday')->toDateString();

        $demande = $this->createRencontreDemande($user, [
            'objet' => 'Dépôt de pièces',
            'date_souhaitee' => $date,
            'heure_souhaitee' => '14:15',
            'modalite' => 'physique',
        ], 'ACTIF');

        $this->actingAs($user)
            ->from(route('demandes.rencontre.create'))
            ->post(route('demandes.rencontre.annuler', $demande))
            ->assertRedirect(route('demandes.rencontre.create'))
            ->assertSessionHas('success');

        $this->assertSame(
            RencontreStatutEnum::ANNULE,
            $demande->fresh()->rencontreStatut()
        );
    }

    public function test_pensionne_cannot_book_a_second_active_appointment(): void
    {
        $this->seedStatuses();
        $user = $this->makePensionne();
        $agent = $this->makeFormalitesAgent();

        $this->createRencontreDemande($user, [
            'date_souhaitee' => now()->next('Wednesday')->toDateString(),
            'heure_souhaitee' => '14:00',
            'modalite' => 'physique',
            'agent_id' => $agent->id,
            'rdv_statut' => RencontreStatutEnum::DEMANDE->value,
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

        $demande = $this->createRencontreDemande($user, [
            'date_souhaitee' => $date,
            'heure_souhaitee' => '14:00',
            'modalite' => 'visio',
            'agent_id' => $agent->id,
            'rdv_statut' => RencontreStatutEnum::DEMANDE->value,
        ]);

        $this->actingAs($user)
            ->from(route('demandes.rencontre.create'))
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

        $demande = $this->createRencontreDemande($owner, [
            'date_souhaitee' => now()->next('Wednesday')->toDateString(),
            'heure_souhaitee' => '15:00',
            'modalite' => 'visio',
        ]);

        $this->actingAs($other)
            ->post(route('demandes.rencontre.annuler', $demande))
            ->assertForbidden();
    }

    public function test_provisionnel_pensionne_books_formalites_motif_only(): void
    {
        $this->seedStatuses();
        $agent = $this->makeFormalitesAgent();
        $user = $this->makePensionne([
            'account_status' => \App\Enums\AccountStatusEnum::PROVISOIRE,
        ]);
        app(\App\Services\Pension\BeneficiaireProvisioningService::class)
            ->provisionAdultSelf($user, null, '8-34321');

        $this->actingAs($user)
            ->get(route('demandes.rencontre.create'))
            ->assertOk()
            ->assertSee('Demande de formalités de pension et mandats')
            ->assertDontSee('Finalisation de la création de compte')
            ->assertDontSee('Documents à préparer pour le rendez-vous');

        $this->actingAs($user)
            ->from(route('demandes.rencontre.create'))
            ->post(route('demandes.rencontre.store'), $this->validStorePayload($agent, [
                'motif' => 'demande_formalites_mandats',
            ]))
            ->assertRedirect(route('demandes.rencontre.create'))
            ->assertSessionHas('success');
    }
}

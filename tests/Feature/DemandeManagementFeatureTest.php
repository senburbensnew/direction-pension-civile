<?php

namespace Tests\Feature;

use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\DemandeInteraction;
use App\Models\Service;
use App\Models\WorkflowStep;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class DemandeManagementFeatureTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedStatuses();
        $this->seedServices();
        $this->seedRoles();
    }

    /**
     * Direction user: has 'direction' role (not 'admin') + admin role to access admin panel.
     * In production, direction employees are also given the 'admin' role to access the admin panel.
     */
    private function makeDirectionUser(): User
    {
        $user = User::factory()->create([
            'service_id' => Service::where('code', Service::DIRECTION)->value('id'),
        ]);
        $user->assignRole(['admin', 'direction']);
        return $user;
    }

    /**
     * A regular pensionnaire/fonctionnaire user — no admin role, no service.
     */
    private function makeRegularUser(): User
    {
        return User::factory()->create();
    }

    /**
     * An agent user belonging to a service, with admin role to access admin panel.
     */
    private function makeServiceUser(string $serviceCode): User
    {
        $user = User::factory()->create([
            'service_id' => Service::where('code', $serviceCode)->value('id'),
        ]);
        $user->assignRole('admin');
        return $user;
    }

    private function makeDemande(User $owner, string $statusCode = 'SOUMISE'): Demande
    {
        $stepId = WorkflowStep::idForCode($statusCode);
        return Demande::create([
            'type'           => TypeDemandeEnum::DEMANDE_ATTESTATION->value,
            'created_by'     => $owner->id,
            'current_step_id' => $stepId,
        ]);
    }

    private function makeDemandeThatHasBeenProcessed(User $owner): Demande
    {
        // DEMANDE_ATTESTATION requires: secretariat → service_formalite
        $demande     = $this->makeDemande($owner, 'EN_COURS');
        $direction   = Service::where('code', Service::DIRECTION)->first();
        $secretariat = Service::where('code', Service::SECRETARIAT)->first();
        $formalite   = Service::where('code', Service::FORMALITE)->first();

        foreach ([$secretariat, $formalite] as $service) {
            DemandeInteraction::create([
                'demande_id'      => $demande->id,
                'type'            => DemandeInteraction::TYPE_TRANSFERT,
                'from_service_id' => $direction->id,
                'to_service_id'   => $service->id,
                'initiated_by'    => $owner->id,
                'statut'          => DemandeInteraction::STATUT_ACCEPTE,
                'repondu_by'      => $owner->id,
                'repondu_at'      => now(),
            ]);
        }

        // Final return to Direction
        DemandeInteraction::create([
            'demande_id'      => $demande->id,
            'type'            => DemandeInteraction::TYPE_TRANSFERT,
            'from_service_id' => $formalite->id,
            'to_service_id'   => $direction->id,
            'initiated_by'    => $owner->id,
            'statut'          => DemandeInteraction::STATUT_ACCEPTE,
            'repondu_by'      => $owner->id,
            'repondu_at'      => now(),
        ]);

        return $demande;
    }

    // ─── Admin index ─────────────────────────────────────────────────────────

    /** @test */
    public function admin_can_view_demande_management_index(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('admin.demandes.index'));

        $response->assertOk();
    }

    /** @test */
    public function regular_user_cannot_view_admin_demande_index(): void
    {
        $user = $this->makeRegularUser();

        $response = $this->actingAs($user)->get(route('admin.demandes.index'));

        $response->assertForbidden();
    }

    // ─── annotate() ──────────────────────────────────────────────────────────
    // demande.annotate is under middleware('not.admin'), so direction user
    // must NOT have role:admin to access it.

    /** @test */
    public function direction_user_without_admin_role_can_annotate_demande(): void
    {
        // Direction user with ONLY direction role (no admin) — passes not.admin middleware
        $dirUser = User::factory()->create([
            'service_id' => Service::where('code', Service::DIRECTION)->value('id'),
        ]);
        $dirUser->assignRole('direction');

        $owner   = $this->makeRegularUser();
        $demande = $this->makeDemande($owner);

        $response = $this->actingAs($dirUser)
            ->post(route('demande.annotate', $demande), [
                'annotation' => 'Dossier examiné et validé pour transfert.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $demande->refresh();
        $this->assertTrue($demande->isAnnotated());
        $this->assertEquals('Dossier examiné et validé pour transfert.', $demande->annotation);
    }

    /** @test */
    public function pensionnaire_user_cannot_annotate_demande(): void
    {
        // Regular user (pensionnaire) — no direction role → controller aborts 403
        $user    = $this->makeRegularUser();
        $owner   = $this->makeRegularUser();
        $demande = $this->makeDemande($owner);

        $response = $this->actingAs($user)
            ->post(route('demande.annotate', $demande), [
                'annotation' => 'Tentative.',
            ]);

        $response->assertForbidden();
    }

    /** @test */
    public function annotation_requires_non_empty_text(): void
    {
        $dirUser = User::factory()->create([
            'service_id' => Service::where('code', Service::DIRECTION)->value('id'),
        ]);
        $dirUser->assignRole('direction');
        $owner   = $this->makeRegularUser();
        $demande = $this->makeDemande($owner);

        $response = $this->actingAs($dirUser)
            ->post(route('demande.annotate', $demande), [
                'annotation' => '',
            ]);

        $response->assertSessionHasErrors('annotation');
    }

    // ─── requestComplement() ─────────────────────────────────────────────────
    // demande.complement is also under middleware('not.admin').

    /** @test */
    public function direction_agent_can_request_complement_from_user(): void
    {
        // Agent with direction role but NO admin role — passes not.admin
        $agent   = User::factory()->create([
            'service_id' => Service::where('code', Service::DIRECTION)->value('id'),
        ]);
        $agent->assignRole('direction');
        $owner   = $this->makeRegularUser();
        $demande = $this->makeDemande($owner);

        $response = $this->actingAs($agent)
            ->post(route('demande.complement', $demande), [
                'message' => 'Veuillez fournir une copie de votre pièce d\'identité.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $demande->refresh();
        $this->assertEquals('COMPLEMENT_REQUIS', $demande->currentStep?->code);

        $this->assertDatabaseHas('demande_messages', [
            'demande_id' => $demande->id,
            'body'       => 'Veuillez fournir une copie de votre pièce d\'identité.',
        ]);
    }

    /** @test */
    public function complement_request_requires_message(): void
    {
        $agent = User::factory()->create([
            'service_id' => Service::where('code', Service::DIRECTION)->value('id'),
        ]);
        $agent->assignRole('direction');
        $owner   = $this->makeRegularUser();
        $demande = $this->makeDemande($owner);

        $response = $this->actingAs($agent)
            ->post(route('demande.complement', $demande), [
                'message' => '',
            ]);

        $response->assertSessionHasErrors('message');
    }

    // ─── approuver() / cloturer() — require admin role ───────────────────────

    /** @test */
    public function direction_admin_can_approve_processed_demande(): void
    {
        $dirUser = $this->makeDirectionUser(); // admin + direction roles
        $owner   = $this->makeRegularUser();
        $demande = $this->makeDemandeThatHasBeenProcessed($owner);

        $response = $this->actingAs($dirUser)
            ->post(route('admin.demandes.approuver', $demande));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $demande->refresh();
        $this->assertEquals('APPROUVEE', $demande->currentStep?->code);
    }

    /** @test */
    public function direction_admin_cannot_approve_unprocessed_demande(): void
    {
        $dirUser = $this->makeDirectionUser();
        $owner   = $this->makeRegularUser();
        $demande = $this->makeDemande($owner); // no workflow processed

        $response = $this->actingAs($dirUser)
            ->post(route('admin.demandes.approuver', $demande));

        $response->assertForbidden();
    }

    /** @test */
    public function admin_without_direction_role_cannot_approve_demande(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin'); // no direction role
        $owner   = $this->makeRegularUser();
        $demande = $this->makeDemandeThatHasBeenProcessed($owner);

        $response = $this->actingAs($admin)
            ->post(route('admin.demandes.approuver', $demande));

        $response->assertForbidden();
    }

    // ─── cloturer() ──────────────────────────────────────────────────────────

    /** @test */
    public function direction_admin_can_close_processed_demande(): void
    {
        $dirUser = $this->makeDirectionUser();
        $owner   = $this->makeRegularUser();
        $demande = $this->makeDemandeThatHasBeenProcessed($owner);

        $response = $this->actingAs($dirUser)
            ->post(route('admin.demandes.cloturer', $demande));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $demande->refresh();
        $this->assertEquals('FINALISEE', $demande->currentStep?->code);
    }

    // ─── rejeter() ───────────────────────────────────────────────────────────

    /** @test */
    public function direction_admin_can_reject_demande(): void
    {
        $dirUser = $this->makeDirectionUser();
        $owner   = $this->makeRegularUser();
        $demande = $this->makeDemande($owner);

        $response = $this->actingAs($dirUser)
            ->post(route('admin.demandes.rejeter', $demande), [
                'motif' => 'Documents insuffisants.',
            ]);

        $response->assertRedirect();
        $demande->refresh();
        $this->assertEquals('REJETEE', $demande->currentStep?->code);
    }

    /** @test */
    public function admin_without_direction_cannot_reject_demande(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $owner   = $this->makeRegularUser();
        $demande = $this->makeDemande($owner);

        $response = $this->actingAs($admin)
            ->post(route('admin.demandes.rejeter', $demande));

        $response->assertForbidden();
    }

    // ─── annuler() ───────────────────────────────────────────────────────────

    /** @test */
    public function direction_admin_can_cancel_demande(): void
    {
        $dirUser = $this->makeDirectionUser();
        $owner   = $this->makeRegularUser();
        $demande = $this->makeDemande($owner);

        $response = $this->actingAs($dirUser)
            ->post(route('admin.demandes.annuler', $demande), [
                'motif' => 'Retrait volontaire.',
            ]);

        $response->assertRedirect();
        $demande->refresh();
        $this->assertEquals('ANNULEE', $demande->currentStep?->code);
    }

    // ─── updateStatus() ──────────────────────────────────────────────────────

    /** @test */
    public function admin_can_update_demande_status(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $owner   = $this->makeRegularUser();
        $demande = $this->makeDemande($owner, 'SOUMISE');

        $response = $this->actingAs($admin)
            ->post(route('admin.demandes.updateStatus', $demande), [
                'etat'        => 'EN_ATTENTE',
                'commentaire' => 'Mise en attente de vérification.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $demande->refresh();
        $this->assertEquals('EN_ATTENTE', $demande->currentStep?->code);
    }

    /** @test */
    public function update_status_requires_etat_field(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $owner   = $this->makeRegularUser();
        $demande = $this->makeDemande($owner);

        $response = $this->actingAs($admin)
            ->post(route('admin.demandes.updateStatus', $demande), []);

        $response->assertSessionHasErrors('etat');
    }

    // ─── affecterServices() ──────────────────────────────────────────────────

    /** @test */
    public function admin_can_assign_demande_to_multiple_services(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $owner   = $this->makeRegularUser();
        $demande = $this->makeDemande($owner);
        $liq     = Service::where('code', Service::LIQUIDATION)->first();
        $sec     = Service::where('code', Service::SECRETARIAT)->first();

        $response = $this->actingAs($admin)
            ->post(route('admin.demandes.affecter', $demande), [
                'service_ids' => [$liq->id, $sec->id],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('demande_interactions', [
            'demande_id'    => $demande->id,
            'type'          => DemandeInteraction::TYPE_AVIS,
            'to_service_id' => $liq->id,
        ]);
        $this->assertDatabaseHas('demande_interactions', [
            'demande_id'    => $demande->id,
            'type'          => DemandeInteraction::TYPE_AVIS,
            'to_service_id' => $sec->id,
        ]);
    }

    /** @test */
    public function affecter_requires_at_least_one_service(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $owner   = $this->makeRegularUser();
        $demande = $this->makeDemande($owner);

        $response = $this->actingAs($admin)
            ->post(route('admin.demandes.affecter', $demande), [
                'service_ids' => [],
            ]);

        $response->assertSessionHasErrors('service_ids');
    }

    /** @test */
    public function assigner_agent_creates_assignment_record(): void
    {
        $serviceId = Service::where('code', Service::DIRECTION)->value('id');
        $admin     = $this->makeDirectionUser();
        $agent     = User::factory()->create(['service_id' => $serviceId, 'is_active' => true]);
        $owner     = $this->makeRegularUser();
        $demande   = $this->makeDemande($owner);
        $demande->update(['current_service_id' => $serviceId]);

        $response = $this->actingAs($admin)
            ->post(route('admin.demandes.assigner-agent', $demande), [
                'user_id' => $agent->id,
                'note'    => 'Test affectation',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('assignments', [
            'demande_id'  => $demande->id,
            'user_id'     => $agent->id,
            'assigned_by' => $admin->id,
            'ended_at'    => null,
        ]);
    }

    /** @test */
    public function assigner_agent_closes_previous_assignment(): void
    {
        $serviceId = Service::where('code', Service::DIRECTION)->value('id');
        $admin     = $this->makeDirectionUser();
        $agent1    = User::factory()->create(['service_id' => $serviceId, 'is_active' => true]);
        $agent2    = User::factory()->create(['service_id' => $serviceId, 'is_active' => true]);
        $owner     = $this->makeRegularUser();
        $demande   = $this->makeDemande($owner);
        $demande->update(['current_service_id' => $serviceId]);

        $this->actingAs($admin)->post(route('admin.demandes.assigner-agent', $demande), ['user_id' => $agent1->id]);
        $this->actingAs($admin)->post(route('admin.demandes.assigner-agent', $demande), ['user_id' => $agent2->id]);

        // First assignment should now have ended_at set
        $this->assertDatabaseMissing('assignments', ['user_id' => $agent1->id, 'ended_at' => null]);
        // Second assignment should be active
        $this->assertDatabaseHas('assignments', ['user_id' => $agent2->id, 'ended_at' => null]);
    }

    /** @test */
    public function assigner_agent_rejects_agent_from_wrong_service(): void
    {
        $dirServiceId = Service::where('code', Service::DIRECTION)->value('id');
        $liqServiceId = Service::where('code', Service::LIQUIDATION)->value('id');
        $admin  = $this->makeDirectionUser();
        $agent  = User::factory()->create(['service_id' => $liqServiceId, 'is_active' => true]);
        $owner  = $this->makeRegularUser();
        $demande = $this->makeDemande($owner);
        $demande->update(['current_service_id' => $dirServiceId]);

        $response = $this->actingAs($admin)
            ->post(route('admin.demandes.assigner-agent', $demande), ['user_id' => $agent->id]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('assignments', 0);
    }

    /** @test */
    public function admin_can_accept_rencontre(): void
    {
        $admin   = User::factory()->create();
        $admin->assignRole('admin');
        $statusId = WorkflowStep::idForCode('SOUMISE');
        $demande  = Demande::create([
            'type'           => \App\Enums\TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by'     => null,
            'current_step_id' => $statusId,
            'data'           => ['prenom' => 'Jean', 'nom' => 'Dupont', 'email' => 'j@d.com',
                                'objet' => 'Test', 'date_souhaitee' => now()->addDays(5)->toDateString(),
                                'heure_souhaitee' => '10:00', 'plateforme' => 'zoom'],
        ]);

        $this->actingAs($admin)
            ->post(route('admin.rencontres.accepter', $demande))
            ->assertRedirect(route('admin.rencontres.index'));

        $this->assertDatabaseHas('demandes', [
            'id'              => $demande->id,
            'current_step_id' => WorkflowStep::idForCode('APPROUVEE'),
        ]);
    }

    /** @test */
    public function admin_can_refuse_rencontre_with_motif(): void
    {
        $admin    = User::factory()->create();
        $admin->assignRole('admin');
        $statusId = WorkflowStep::idForCode('SOUMISE');
        $demande  = Demande::create([
            'type'           => \App\Enums\TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by'     => null,
            'current_step_id' => $statusId,
            'data'           => ['prenom' => 'Jean', 'nom' => 'Dupont', 'email' => 'j@d.com',
                                'objet' => 'Test', 'date_souhaitee' => now()->addDays(5)->toDateString(),
                                'heure_souhaitee' => '10:00', 'plateforme' => 'zoom'],
        ]);

        $this->actingAs($admin)
            ->post(route('admin.rencontres.refuser', $demande), ['motif' => 'Agenda complet.'])
            ->assertRedirect(route('admin.rencontres.index'));

        $this->assertDatabaseHas('demandes', [
            'id'         => $demande->id,
            'current_step_id' => WorkflowStep::idForCode('REJETEE'),
            'annotation' => 'Agenda complet.',
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\DemandeWorkflow;
use App\Models\Service;
use App\Models\Status;
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
        $statusId = Status::where('code', $statusCode)->value('id');
        return Demande::create([
            'type'       => TypeDemandeEnum::DEMANDE_ATTESTATION->value,
            'created_by' => $owner->id,
            'status_id'  => $statusId,
        ]);
    }

    private function makeDemandeThatHasBeenProcessed(User $owner): Demande
    {
        // DEMANDE_ATTESTATION requires: secretariat → service_formalite
        $demande    = $this->makeDemande($owner, 'EN_COURS');
        $direction  = Service::where('code', Service::DIRECTION)->first();
        $secretariat = Service::where('code', Service::SECRETARIAT)->first();
        $formalite  = Service::where('code', Service::FORMALITE)->first();
        $statusId   = Status::where('code', 'EN_COURS')->value('id');

        foreach ([$secretariat, $formalite] as $service) {
            DemandeWorkflow::create([
                'demande_id'           => $demande->id,
                'from_service_id'      => $direction->id,
                'to_service_id'        => $service->id,
                'status_id'            => $statusId,
                'action_by_user_id'    => $owner->id,
                'reception_status'     => 'accepted',
                'reception_by_user_id' => $owner->id,
                'reception_at'         => now(),
            ]);
        }

        // Final return to Direction
        DemandeWorkflow::create([
            'demande_id'           => $demande->id,
            'from_service_id'      => $formalite->id,
            'to_service_id'        => $direction->id,
            'status_id'            => $statusId,
            'action_by_user_id'    => $owner->id,
            'reception_status'     => 'accepted',
            'reception_by_user_id' => $owner->id,
            'reception_at'         => now(),
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
        $this->assertEquals('COMPLEMENT_REQUIS', $demande->status->code);

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
        $this->assertEquals('APPROUVEE', $demande->status->code);
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
        $this->assertEquals('FINALISEE', $demande->status->code);
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
        $this->assertEquals('REJETEE', $demande->status->code);
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
        $this->assertEquals('ANNULEE', $demande->status->code);
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
        $this->assertEquals('EN_ATTENTE', $demande->status->code);
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

        $this->assertDatabaseHas('affectations', ['demande_id' => $demande->id, 'service_id' => $liq->id]);
        $this->assertDatabaseHas('affectations', ['demande_id' => $demande->id, 'service_id' => $sec->id]);
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
}

<?php

namespace Tests\Feature;

use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\DemandeWorkflow;
use App\Models\FluxTransition;
use App\Models\Service;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class DemandeWorkflowFeatureTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedStatuses();
        $this->seedServices();
        $this->seedRoles();
    }

    private function makeUser(?string $role = null, ?int $serviceId = null): User
    {
        $user = User::factory()->create(['service_id' => $serviceId]);
        if ($role) {
            $user->assignRole($role);
        }
        return $user;
    }

    private function direction(): Service
    {
        return Service::where('code', Service::DIRECTION)->first();
    }

    private function liquidation(): Service
    {
        return Service::where('code', Service::LIQUIDATION)->first();
    }

    private function makeDemande(User $user, string $type = TypeDemandeEnum::DEMANDE_ATTESTATION->value): Demande
    {
        return Demande::create(['type' => $type, 'created_by' => $user->id]);
    }

    // ─── Personal dashboard ──────────────────────────────────────────────────

    /** @test */
    public function authenticated_user_can_view_their_personal_dashboard(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user)->get(route('personal.dashboard'));

        $response->assertOk();
    }

    /** @test */
    public function unauthenticated_user_is_redirected_from_personal_dashboard(): void
    {
        $response = $this->get(route('personal.dashboard'));

        $response->assertRedirect(route('login'));
    }

    // ─── Show request (authenticated user) ───────────────────────────────────

    /** @test */
    public function user_can_view_their_own_demande(): void
    {
        $user    = $this->makeUser();
        $demande = $this->makeDemande($user);

        $response = $this->actingAs($user)
            ->get(route('personal.request.authenticated-user-request.show', $demande->id));

        $response->assertOk();
    }

    /** @test */
    public function user_cannot_view_another_users_demande_via_auth_route(): void
    {
        $owner   = $this->makeUser();
        $visitor = $this->makeUser();
        $demande = $this->makeDemande($owner);

        $response = $this->actingAs($visitor)
            ->get(route('personal.request.authenticated-user-request.show', $demande->id));

        $response->assertNotFound();
    }

    // ─── Répondre au complément ───────────────────────────────────────────────

    /** @test */
    public function owner_can_submit_complement_response(): void
    {
        $user      = $this->makeUser();
        $statusId  = Status::where('code', 'COMPLEMENT_REQUIS')->value('id');
        $demande   = $this->makeDemande($user);
        $demande->update(['status_id' => $statusId]);

        $response = $this->actingAs($user)
            ->post(route('demande.repondre-complement', $demande), [
                'message' => 'Voici les documents complémentaires demandés.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $demande->refresh();
        $this->assertEquals('SOUMISE', $demande->status->code);
    }

    /** @test */
    public function non_owner_cannot_submit_complement_response(): void
    {
        $owner     = $this->makeUser();
        $intruder  = $this->makeUser();
        $statusId  = Status::where('code', 'COMPLEMENT_REQUIS')->value('id');
        $demande   = $this->makeDemande($owner);
        $demande->update(['status_id' => $statusId]);

        $response = $this->actingAs($intruder)
            ->post(route('demande.repondre-complement', $demande), [
                'message' => 'Tentative d\'intrusion.',
            ]);

        $response->assertForbidden();
    }

    /** @test */
    public function complement_response_requires_message_field(): void
    {
        $user     = $this->makeUser();
        $statusId = Status::where('code', 'COMPLEMENT_REQUIS')->value('id');
        $demande  = $this->makeDemande($user);
        $demande->update(['status_id' => $statusId]);

        $response = $this->actingAs($user)
            ->post(route('demande.repondre-complement', $demande), []);

        $response->assertSessionHasErrors('message');
    }

    /** @test */
    public function complement_response_rejected_when_demande_not_in_complement_requis(): void
    {
        $user    = $this->makeUser();
        $demande = $this->makeDemande($user); // BROUILLON by default

        $response = $this->actingAs($user)
            ->post(route('demande.repondre-complement', $demande), [
                'message' => 'Quelque chose.',
            ]);

        $response->assertForbidden();
    }

    // ─── Accept / Refuse reception ───────────────────────────────────────────

    /** @test */
    public function service_user_can_accept_transfer_reception(): void
    {
        // admin.workflows.accepter is behind role:admin middleware;
        // liqUser must have admin role to access the route.
        $dirUser  = $this->makeUser('direction', $this->direction()->id);
        $liqUser  = User::factory()->create(['service_id' => $this->liquidation()->id]);
        $liqUser->assignRole('admin');
        $demande  = $this->makeDemande($dirUser);
        $statusId = Status::where('code', 'TRANSFERT_EN_ATTENTE')->value('id');

        $demande->update([
            'current_service_id' => $this->liquidation()->id,
            'status_id'          => $statusId,
        ]);

        $workflow = DemandeWorkflow::create([
            'demande_id'        => $demande->id,
            'from_service_id'   => $this->direction()->id,
            'to_service_id'     => $this->liquidation()->id,
            'status_id'         => $statusId,
            'action_by_user_id' => $dirUser->id,
            'reception_status'  => 'pending',
        ]);

        $response = $this->actingAs($liqUser)
            ->post(route('admin.workflows.accepter', $workflow));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $workflow->refresh();
        $this->assertEquals('accepted', $workflow->reception_status);
    }

    /** @test */
    public function service_user_can_refuse_transfer_reception(): void
    {
        $dirUser  = $this->makeUser('direction', $this->direction()->id);
        $liqUser  = User::factory()->create(['service_id' => $this->liquidation()->id]);
        $liqUser->assignRole('admin');
        $demande  = $this->makeDemande($dirUser);
        $statusId = Status::where('code', 'TRANSFERT_EN_ATTENTE')->value('id');

        $demande->update([
            'current_service_id' => $this->liquidation()->id,
            'status_id'          => $statusId,
        ]);

        $workflow = DemandeWorkflow::create([
            'demande_id'        => $demande->id,
            'from_service_id'   => $this->direction()->id,
            'to_service_id'     => $this->liquidation()->id,
            'status_id'         => $statusId,
            'action_by_user_id' => $dirUser->id,
            'reception_status'  => 'pending',
        ]);

        $response = $this->actingAs($liqUser)
            ->post(route('admin.workflows.refuser', $workflow), [
                'motif' => 'Dossier incomplet',
            ]);

        $response->assertRedirect();

        $workflow->refresh();
        $demande->refresh();

        $this->assertEquals('refused', $workflow->reception_status);
        $this->assertEquals($this->direction()->id, $demande->current_service_id);
    }

    /** @test */
    public function wrong_service_user_cannot_accept_reception(): void
    {
        // A non-admin user from a different service cannot access admin routes at all.
        $dirUser  = $this->makeUser('direction', $this->direction()->id);
        $wrongUser = $this->makeUser(null, $this->direction()->id); // no admin role → blocked by middleware
        $demande  = $this->makeDemande($dirUser);
        $statusId = Status::where('code', 'TRANSFERT_EN_ATTENTE')->value('id');

        $workflow = DemandeWorkflow::create([
            'demande_id'        => $demande->id,
            'from_service_id'   => $this->direction()->id,
            'to_service_id'     => $this->liquidation()->id,
            'status_id'         => $statusId,
            'action_by_user_id' => $dirUser->id,
            'reception_status'  => 'pending',
        ]);

        $response = $this->actingAs($wrongUser)
            ->post(route('admin.workflows.accepter', $workflow));

        $response->assertForbidden();
    }

    // ─── Transfer demande ────────────────────────────────────────────────────

    /** @test */
    public function direction_user_can_transfer_annotated_demande(): void
    {
        FluxTransition::create([
            'service_source_id'      => $this->direction()->id,
            'service_destination_id' => $this->liquidation()->id,
            'action'                 => 'transfer',
            'ordre'                  => 1,
        ]);

        $dirUser = $this->makeUser('direction', $this->direction()->id);
        $demande = $this->makeDemande($dirUser);
        $demande->update([
            'current_service_id' => $this->direction()->id,
            'annotation'         => 'Dossier examiné',
            'annotated_by'       => $dirUser->id,
            'annotated_at'       => now(),
            'status_id'          => Status::where('code', 'SOUMISE')->value('id'),
        ]);

        $response = $this->actingAs($dirUser)
            ->post(route('demande.transfert'), [
                'demande_id'  => $demande->id,
                'service_id'  => $this->liquidation()->id,
                'commentaire' => 'Pour traitement',
            ]);

        $response->assertRedirect(route('personal.cart'));

        $demande->refresh();
        $this->assertEquals('TRANSFERT_EN_ATTENTE', $demande->status->code);
    }

    /** @test */
    public function transfer_is_blocked_when_demande_not_annotated(): void
    {
        FluxTransition::create([
            'service_source_id'      => $this->direction()->id,
            'service_destination_id' => $this->liquidation()->id,
            'action'                 => 'transfer',
            'ordre'                  => 1,
        ]);

        $dirUser = $this->makeUser('direction', $this->direction()->id);
        $demande = $this->makeDemande($dirUser);
        $demande->update(['current_service_id' => $this->direction()->id]);

        $response = $this->actingAs($dirUser)
            ->post(route('demande.transfert'), [
                'demande_id' => $demande->id,
                'service_id' => $this->liquidation()->id,
            ]);

        $response->assertForbidden();
    }
}

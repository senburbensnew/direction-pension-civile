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
        $statusId  = WorkflowStep::idForCode('COMPLEMENT_REQUIS');
        $demande   = $this->makeDemande($user);
        $demande->update(['current_step_id' => $statusId]);

        $response = $this->actingAs($user)
            ->post(route('demande.repondre-complement', $demande), [
                'message' => 'Voici les documents complémentaires demandés.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $demande->refresh();
        $this->assertEquals('SOUMISE', $demande->currentStep?->code);
    }

    /** @test */
    public function non_owner_cannot_submit_complement_response(): void
    {
        $owner     = $this->makeUser();
        $intruder  = $this->makeUser();
        $statusId  = WorkflowStep::idForCode('COMPLEMENT_REQUIS');
        $demande   = $this->makeDemande($owner);
        $demande->update(['current_step_id' => $statusId]);

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
        $statusId = WorkflowStep::idForCode('COMPLEMENT_REQUIS');
        $demande  = $this->makeDemande($user);
        $demande->update(['current_step_id' => $statusId]);

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
        $dirUser  = $this->makeUser('direction', $this->direction()->id);
        $liqUser  = User::factory()->create(['service_id' => $this->liquidation()->id]);
        $liqUser->assignRole('admin');
        $demande  = $this->makeDemande($dirUser);
        $statusId = WorkflowStep::idForCode('TRANSFERT_EN_ATTENTE');

        $demande->update([
            'current_service_id' => $this->liquidation()->id,
            'current_step_id'    => $statusId,
        ]);

        $interaction = DemandeInteraction::create([
            'demande_id'      => $demande->id,
            'type'            => DemandeInteraction::TYPE_TRANSFERT,
            'from_service_id' => $this->direction()->id,
            'to_service_id'   => $this->liquidation()->id,
            'initiated_by'    => $dirUser->id,
            'statut'          => DemandeInteraction::STATUT_EN_ATTENTE,
        ]);

        $response = $this->actingAs($liqUser)
            ->post(route('admin.interactions.accepter', $interaction));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $interaction->refresh();
        $this->assertEquals(DemandeInteraction::STATUT_ACCEPTE, $interaction->statut);
    }

    /** @test */
    public function service_user_can_refuse_transfer_reception(): void
    {
        $dirUser  = $this->makeUser('direction', $this->direction()->id);
        $liqUser  = User::factory()->create(['service_id' => $this->liquidation()->id]);
        $liqUser->assignRole('admin');
        $demande  = $this->makeDemande($dirUser);
        $statusId = WorkflowStep::idForCode('TRANSFERT_EN_ATTENTE');

        $demande->update([
            'current_service_id' => $this->liquidation()->id,
            'current_step_id'    => $statusId,
        ]);

        $interaction = DemandeInteraction::create([
            'demande_id'      => $demande->id,
            'type'            => DemandeInteraction::TYPE_TRANSFERT,
            'from_service_id' => $this->direction()->id,
            'to_service_id'   => $this->liquidation()->id,
            'initiated_by'    => $dirUser->id,
            'statut'          => DemandeInteraction::STATUT_EN_ATTENTE,
        ]);

        $response = $this->actingAs($liqUser)
            ->post(route('admin.interactions.refuser', $interaction), [
                'motif' => 'Dossier incomplet',
            ]);

        $response->assertRedirect();

        $interaction->refresh();
        $demande->refresh();

        $this->assertEquals(DemandeInteraction::STATUT_REJETE, $interaction->statut);
        $this->assertEquals($this->direction()->id, $demande->current_service_id);
    }

    /** @test */
    public function wrong_service_user_cannot_accept_reception(): void
    {
        // A non-admin user from a different service cannot access admin routes at all.
        $dirUser   = $this->makeUser('direction', $this->direction()->id);
        $wrongUser = $this->makeUser(null, $this->direction()->id); // no admin role → blocked by middleware
        $demande   = $this->makeDemande($dirUser);
        $statusId  = WorkflowStep::idForCode('TRANSFERT_EN_ATTENTE');

        $interaction = DemandeInteraction::create([
            'demande_id'      => $demande->id,
            'type'            => DemandeInteraction::TYPE_TRANSFERT,
            'from_service_id' => $this->direction()->id,
            'to_service_id'   => $this->liquidation()->id,
            'initiated_by'    => $dirUser->id,
            'statut'          => DemandeInteraction::STATUT_EN_ATTENTE,
        ]);

        $response = $this->actingAs($wrongUser)
            ->post(route('admin.interactions.accepter', $interaction));

        $response->assertForbidden();
    }

    // ─── Transfer demande ────────────────────────────────────────────────────

    /** @test */
    public function direction_user_can_transfer_annotated_demande(): void
    {
        // No service-specific WorkflowSteps configured → validateTransition returns true (allow-all)
        $dirUser = $this->makeUser('direction', $this->direction()->id);
        $demande = $this->makeDemande($dirUser);
        $demande->update([
            'current_service_id' => $this->direction()->id,
            'annotation'         => 'Dossier examiné',
            'annotated_by'       => $dirUser->id,
            'annotated_at'       => now(),
            'current_step_id'    => WorkflowStep::idForCode('SOUMISE'),
        ]);

        $response = $this->actingAs($dirUser)
            ->post(route('demande.transfert'), [
                'demande_id'  => $demande->id,
                'service_id'  => $this->liquidation()->id,
                'commentaire' => 'Pour traitement',
            ]);

        $response->assertRedirect(route('personal.cart'));

        $demande->refresh();
        $this->assertEquals('TRANSFERT_EN_ATTENTE', $demande->currentStep?->code);
    }

    /** @test */
    public function transfer_is_blocked_when_demande_not_annotated(): void
    {
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

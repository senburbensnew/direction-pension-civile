<?php

namespace Tests\Feature;

use App\Models\DemandeCreationCompte;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class DemandeCreationCompteManagementTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        UserType::firstOrCreate(['name' => 'pensionne']);
    }

    private function makeAdmin(): User
    {
        $user = User::factory()->create(['username' => 'admin.agent']);
        $user->assignRole('admin');

        return $user;
    }

    private function makeFormalites(): User
    {
        $user = User::factory()->create(['username' => 'formalites.agent']);
        $user->assignRole('service_accueil_formalites');

        return $user;
    }

    private function makePendingDemande(array $attrs = []): DemandeCreationCompte
    {
        $user = User::factory()->create([
            'email' => $attrs['email'] ?? 'jean.pierre@example.com',
            'username' => 'jean.pierre.'.uniqid(),
            'nif' => $attrs['nif'] ?? '123-456-789-0',
            'pension_code' => $attrs['pension_code'] ?? '8-34321',
            'account_status' => User::STATUS_EN_ATTENTE_VALIDATION,
        ]);
        $user->assignRole('pensionne');

        return DemandeCreationCompte::create(array_merge([
            'code' => 'DCC-'.uniqid(),
            'user_type' => 'pensionne',
            'firstname' => 'Jean',
            'lastname' => 'Pierre',
            'email' => 'jean.pierre@example.com',
            'nif' => '123-456-789-0',
            'pension_code' => '8-34321',
            'telephone' => '+50922000000',
            'adresse' => 'Port-au-Prince',
            'status' => DemandeCreationCompte::STATUS_EN_ATTENTE,
            'user_id' => $user->id,
        ], $attrs));
    }

    /** @test */
    public function guest_cannot_open_management_screens(): void
    {
        $this->get(route('admin.comptes-demandes.index'))->assertRedirect();
        $this->get(route('formalites.comptes-demandes.index'))->assertRedirect();
    }

    /** @test */
    public function pensionne_cannot_open_management_screens(): void
    {
        $user = User::factory()->create();
        $user->assignRole('pensionne');

        $this->actingAs($user)
            ->get(route('admin.comptes-demandes.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('formalites.comptes-demandes.index'))
            ->assertForbidden();
    }

    /** @test */
    public function admin_can_list_and_open_a_pending_request(): void
    {
        $demande = $this->makePendingDemande();

        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('admin.comptes-demandes.index'))
            ->assertOk()
            ->assertSee('Demandes de création de compte')
            ->assertSee($demande->code)
            ->assertSee('jean.pierre');

        $this->actingAs($admin)
            ->get(route('admin.comptes-demandes.show', $demande))
            ->assertOk()
            ->assertSee('Accepter le dossier')
            ->assertSee('Refuser')
            ->assertSee('Pièces jointes')
            ->assertSee('Journal d’activité');
    }

    /** @test */
    public function formalites_agent_can_list_and_open_a_pending_request(): void
    {
        $demande = $this->makePendingDemande();

        $agent = $this->makeFormalites();

        $this->actingAs($agent)
            ->get(route('formalites.comptes-demandes.index'))
            ->assertOk()
            ->assertSee($demande->code);

        $this->actingAs($agent)
            ->get(route('formalites.comptes-demandes.show', $demande))
            ->assertOk()
            ->assertSee('Accepter le dossier');
    }

    /** @test */
    public function formalites_agent_can_accept_and_create_a_pensionne_account(): void
    {
        Storage::fake('public');
        $demande = $this->makePendingDemande();
        $demande->addMedia(UploadedFile::fake()->image('cin.jpg', 400, 300))
            ->toMediaCollection('identite_cin', 'public');
        $this->assertTrue($demande->hasMedia('identite_cin'));
        $existingUserId = $demande->user_id;
        $agent = $this->makeFormalites();

        $this->actingAs($agent)
            ->post(route('formalites.comptes-demandes.accepter', $demande), [
                'firstname' => 'Jean',
                'lastname' => 'Pierre',
                'email' => 'jean.pierre@example.com',
            ])
            ->assertRedirect(route('formalites.comptes-demandes.show', $demande))
            ->assertSessionHas('success')
            ->assertSessionMissing('created_password');

        $demande->refresh();
        $this->assertSame(DemandeCreationCompte::STATUS_ACCEPTEE, $demande->status);
        $this->assertSame($existingUserId, $demande->user_id);
        $this->assertSame($agent->id, $demande->reviewed_by);

        $user = User::find($demande->user_id);
        $this->assertSame('jean.pierre@example.com', $user->email);
        $this->assertTrue($user->isProvisionnel());
        $this->assertTrue($user->is_active);
        $this->assertDatabaseCount('users', 2);
        $this->assertFalse($demande->hasMedia('identite_cin'));
        $this->assertSame('Jean', $demande->firstname);
        $this->assertTrue($demande->histories()->where('event', \App\Models\DemandeCreationCompteHistory::EVENT_ACCEPTEE)->exists());
        $this->assertTrue($demande->histories()->where('event', \App\Models\DemandeCreationCompteHistory::EVENT_PIECES_SUPPRIMEES)->exists());
    }

    /** @test */
    public function admin_can_refuse_a_pending_request(): void
    {
        $demande = $this->makePendingDemande();
        $userId = $demande->user_id;

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.comptes-demandes.refuser', $demande), [
                'refusal_reason' => 'Pièce d’identité illisible.',
            ])
            ->assertRedirect(route('admin.comptes-demandes.show', $demande));

        $demande->refresh();
        $this->assertSame(DemandeCreationCompte::STATUS_REFUSEE, $demande->status);
        $this->assertSame('Pièce d’identité illisible.', $demande->refusal_reason);
        $this->assertNull($demande->user_id);
        $this->assertNull($demande->email);
        $this->assertNull($demande->telephone);
        $this->assertNull($demande->nif);
        $this->assertNull($demande->pension_code);
        $this->assertNull($demande->adresse);
        $this->assertNull($demande->submitted_payload);
        $this->assertDatabaseMissing('users', ['id' => $userId]);
        $this->assertDatabaseCount('users', 1);
        $this->assertTrue($demande->histories()->where('event', \App\Models\DemandeCreationCompteHistory::EVENT_REFUSEE)->exists());
        $this->assertTrue($demande->histories()->where('event', \App\Models\DemandeCreationCompteHistory::EVENT_COMPTE_SUPPRIME)->exists());
    }

    /** @test */
    public function accepted_request_cannot_be_processed_again(): void
    {
        $demande = $this->makePendingDemande();
        $agent = $this->makeFormalites();

        $this->actingAs($agent)
            ->post(route('formalites.comptes-demandes.accepter', $demande), [
                'email' => 'jean.pierre@example.com',
            ])
            ->assertSessionHas('success');

        $this->actingAs($agent)
            ->post(route('formalites.comptes-demandes.refuser', $demande), [
                'refusal_reason' => 'Trop tard pour refuser.',
            ])
            ->assertSessionHasErrors('status');

        $this->assertSame(DemandeCreationCompte::STATUS_ACCEPTEE, $demande->fresh()->status);
        $this->assertDatabaseCount('users', 2);
    }

    /** @test */
    public function accept_fails_when_no_provisional_user_is_linked(): void
    {
        $demande = $this->makePendingDemande();
        $demande->update(['user_id' => null]);

        $this->actingAs($this->makeFormalites())
            ->from(route('formalites.comptes-demandes.show', $demande))
            ->post(route('formalites.comptes-demandes.accepter', $demande), [])
            ->assertRedirect()
            ->assertSessionHasErrors('user');

        $this->assertSame(DemandeCreationCompte::STATUS_EN_ATTENTE, $demande->fresh()->status);
    }

    /** @test */
    public function provisional_user_can_only_open_appointment_pages(): void
    {
        $user = User::factory()->create([
            'account_status' => User::STATUS_EN_ATTENTE_VALIDATION,
        ]);
        $user->assignRole('pensionne');

        $this->actingAs($user)
            ->get(route('demandes.virements.create'))
            ->assertRedirect(route('demandes.rencontre.create'));

        $this->actingAs($user)
            ->get(route('demandes.rencontre.create'))
            ->assertOk();
    }

    /** @test */
    public function realizing_an_appointment_activates_a_provisional_account(): void
    {
        $owner = User::factory()->create([
            'account_status' => User::STATUS_EN_ATTENTE_VALIDATION,
        ]);
        $owner->assignRole('pensionne');
        $this->seedStatuses();

        $compte = DemandeCreationCompte::create([
            'code' => 'DCC-ACT-'.uniqid(),
            'user_type' => 'pensionne',
            'status' => DemandeCreationCompte::STATUS_ACCEPTEE,
            'user_id' => $owner->id,
        ]);

        $demande = \App\Models\Demande::create([
            'type' => \App\Enums\TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => $owner->id,
            'data' => ['rdv_statut' => \App\Enums\RencontreStatutEnum::VALIDE->value],
        ]);

        app(\App\Services\RencontreWorkflowService::class)
            ->clore($demande, $this->makeFormalites(), \App\Enums\RencontreStatutEnum::REALISE);

        $this->assertFalse($owner->fresh()->isProvisionnel());
        $this->assertSame(User::STATUS_ACTIF, $owner->fresh()->account_status);
        $this->assertTrue($compte->fresh()->histories()->where('event', \App\Models\DemandeCreationCompteHistory::EVENT_COMPTE_ACTIVE)->exists());
    }
}

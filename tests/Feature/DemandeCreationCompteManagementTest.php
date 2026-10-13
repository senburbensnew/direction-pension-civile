<?php

namespace Tests\Feature;

use App\Enums\AccountStatusEnum;
use App\Enums\RencontreStatutEnum;
use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\DemandeCreationCompte;
use App\Models\DemandeCreationCompteHistory;
use App\Models\User;
use App\Services\Account\UserCategoryRoleSyncService;
use App\Services\RencontreWorkflowService;
use Database\Seeders\UserCategoriesSeeder;
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
        $this->seed(UserCategoriesSeeder::class);
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
        $user->assignRole(User::ROLE_AGENT_FORMALITES);

        return $user;
    }

    private function makeAgentRdv(): User
    {
        $user = User::factory()->create(['username' => 'rdv.agent']);
        $user->assignRole(User::ROLE_AGENT_RDV);

        return $user;
    }

    private function makePendingDemande(array $attrs = []): DemandeCreationCompte
    {
        $user = $this->createPensionneUser([
            'email' => $attrs['email'] ?? 'jean.pierre@example.com',
            'username' => 'jean.pierre.'.uniqid(),
            'nif' => $attrs['nif'] ?? '123-456-789-0',
            'account_status' => AccountStatusEnum::PROVISOIRE,
        ], $attrs['pension_code'] ?? '8-34321');

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

    private function markRendezVousRealise(DemandeCreationCompte $demande): void
    {
        $this->seedStatuses();

        Demande::create([
            'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => $demande->user_id,
            'data' => [
                'motif_key' => 'demande_formalites_mandats',
                'rdv_statut' => RencontreStatutEnum::REALISE->value,
                'date_souhaitee' => now()->subDay()->toDateString(),
                'heure_souhaitee' => '10:00',
            ],
        ]);
    }

    /** @test */
    public function guest_cannot_open_management_screens(): void
    {
        $this->get(route('formalites.comptes-demandes.index'))->assertRedirect();
    }

    /** @test */
    public function pensionne_cannot_open_management_screens(): void
    {
        $user = User::factory()->create();
        $user->assignRole('pensionne');

        $this->actingAs($user)
            ->get(route('formalites.comptes-demandes.index'))
            ->assertForbidden();
    }

    /** @test */
    public function admin_cannot_open_formalites_comptes_management(): void
    {
        $demande = $this->makePendingDemande();
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('formalites.comptes-demandes.index'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('formalites.comptes-demandes.show', $demande))
            ->assertForbidden();
    }

    /** @test */
    public function formalites_agent_can_list_and_open_a_pending_request(): void
    {
        $demande = $this->makePendingDemande();

        $agent = $this->makeFormalites();

        $this->actingAs($agent)
            ->get(route('formalites.comptes-demandes.index'))
            ->assertOk()
            ->assertSee($demande->libellePublic());

        $this->actingAs($agent)
            ->get(route('formalites.comptes-demandes.show', $demande))
            ->assertOk()
            ->assertSee('rendez-vous réalisé')
            ->assertDontSee('Accepter le dossier');

        $this->markRendezVousRealise($demande);

        $this->actingAs($agent)
            ->get(route('formalites.comptes-demandes.show', $demande->fresh()))
            ->assertOk()
            ->assertSee('Accepter le dossier');
    }

    /** @test */
    public function agent_rdv_without_formalites_role_cannot_open_management_screens(): void
    {
        $this->actingAs($this->makeAgentRdv())
            ->get(route('formalites.comptes-demandes.index'))
            ->assertForbidden();
    }

    /** @test */
    public function admin_cannot_accept_or_refuse_via_removed_routes(): void
    {
        $demande = $this->makePendingDemande();
        $this->markRendezVousRealise($demande);

        $this->actingAs($this->makeAdmin())
            ->post('/admin/comptes-demandes/'.$demande->id.'/accepter', [
                'email' => 'jean.pierre@example.com',
            ])
            ->assertNotFound();

        $this->assertSame(DemandeCreationCompte::STATUS_EN_ATTENTE, $demande->fresh()->status);
    }

    /** @test */
    public function formalites_cannot_accept_before_rendez_vous_realise(): void
    {
        $demande = $this->makePendingDemande();

        $this->actingAs($this->makeFormalites())
            ->post(route('formalites.comptes-demandes.accepter', $demande), [
                'email' => 'jean.pierre@example.com',
            ])
            ->assertSessionHasErrors('status');

        $this->assertSame(DemandeCreationCompte::STATUS_EN_ATTENTE, $demande->fresh()->status);
    }

    /** @test */
    public function formalites_cannot_accept_when_rdv_realise_but_before_scheduled_time(): void
    {
        $demande = $this->makePendingDemande();
        $this->seedStatuses();

        Demande::create([
            'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => $demande->user_id,
            'data' => [
                'motif_key' => 'demande_formalites_mandats',
                'rdv_statut' => RencontreStatutEnum::REALISE->value,
                'date_souhaitee' => now()->addDay()->toDateString(),
                'heure_souhaitee' => '14:00',
            ],
        ]);

        $this->assertTrue($demande->fresh()->hasRendezVousRealise());
        $this->assertFalse($demande->fresh()->canBeDecided());

        $this->actingAs($this->makeFormalites())
            ->post(route('formalites.comptes-demandes.accepter', $demande), [
                'email' => 'jean.pierre@example.com',
            ])
            ->assertSessionHasErrors('status');

        $this->assertSame(DemandeCreationCompte::STATUS_EN_ATTENTE, $demande->fresh()->status);
    }

    /** @test */
    public function formalites_rdv_realise_allows_account_accept_with_formalites_mandats_motif(): void
    {
        Storage::fake('public');
        $demande = $this->makePendingDemande();
        $this->seedStatuses();

        Demande::create([
            'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => $demande->user_id,
            'data' => [
                'motif_key' => 'demande_formalites_mandats',
                'rdv_statut' => RencontreStatutEnum::REALISE->value,
                'date_souhaitee' => now()->subDay()->toDateString(),
                'heure_souhaitee' => '10:00',
            ],
        ]);

        $this->assertTrue($demande->fresh()->hasRendezVousRealise());

        $this->actingAs($this->makeFormalites())
            ->post(route('formalites.comptes-demandes.accepter', $demande), [
                'email' => 'jean.pierre@example.com',
            ])
            ->assertRedirect(route('formalites.comptes-demandes.show', $demande));

        $this->assertSame(DemandeCreationCompte::STATUS_ACCEPTEE, $demande->fresh()->status);
    }

    /** @test */
    public function formalites_agent_can_accept_and_create_a_pensionne_account(): void
    {
        Storage::fake('public');
        $demande = $this->makePendingDemande();
        $this->markRendezVousRealise($demande);
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
        $this->assertSame($agent->id, $demande->traitee_par);
        $this->assertNotNull($demande->approuvee_at);

        $user = User::find($demande->user_id);
        $this->assertSame('jean.pierre@example.com', $user->email);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->canAccessFullServices());
        $this->assertTrue($user->allowsLogin());
        $this->assertDatabaseCount('users', 2);
        $this->assertTrue($demande->hasMedia('identite_cin'));
        $this->assertSame('Jean', $demande->firstname);
        $this->assertTrue($demande->histories()->where('event', DemandeCreationCompteHistory::EVENT_ACCEPTEE)->exists());
        $this->assertFalse($demande->histories()->where('event', DemandeCreationCompteHistory::EVENT_PIECES_SUPPRIMEES)->exists());
    }

    /** @test */
    public function formalites_agent_can_refuse_a_pending_request(): void
    {
        Storage::fake('public');
        $demande = $this->makePendingDemande();
        $this->markRendezVousRealise($demande);
        $demande->addMedia(UploadedFile::fake()->image('cin.jpg', 400, 300))
            ->toMediaCollection('identite_cin', 'public');
        $userId = $demande->user_id;

        $this->actingAs($this->makeFormalites())
            ->post(route('formalites.comptes-demandes.refuser', $demande), [
                'refusal_reason' => 'Pièce d’identité illisible.',
            ])
            ->assertRedirect(route('formalites.comptes-demandes.show', $demande));

        $demande->refresh();
        $this->assertSame(DemandeCreationCompte::STATUS_REFUSEE, $demande->status);
        $this->assertSame('Pièce d’identité illisible.', $demande->refusal_reason);
        $this->assertSame($userId, $demande->user_id);
        $this->assertNotNull($demande->rejetee_at);
        $this->assertSame('jean.pierre@example.com', $demande->email);
        $this->assertDatabaseHas('users', [
            'id' => $userId,
            'account_status' => AccountStatusEnum::BLOQUE->value,
        ]);
        $this->assertDatabaseCount('users', 2);
        $this->assertFalse($demande->user->allowsLogin());
        $this->assertTrue($demande->histories()->where('event', DemandeCreationCompteHistory::EVENT_REFUSEE)->exists());
        $this->assertTrue($demande->histories()->where('event', DemandeCreationCompteHistory::EVENT_COMPTE_BLOQUE)->exists());
        $this->assertTrue($demande->hasMedia('identite_cin'));
    }

    /** @test */
    public function formalites_agent_can_download_submitted_identity_piece(): void
    {
        Storage::fake('public');
        $demande = $this->makePendingDemande();
        $demande->addMedia(UploadedFile::fake()->image('cin.jpg', 400, 300))
            ->toMediaCollection('identite_cin', 'public');
        $media = $demande->getFirstMedia('identite_cin');

        $agent = $this->makeFormalites();

        $download = $this->actingAs($agent)
            ->get(route('formalites.comptes-demandes.piece', [$demande, $media]));
        $download->assertOk();
        $this->assertStringContainsString(
            'attachment',
            (string) $download->headers->get('content-disposition')
        );

        $inline = $this->actingAs($agent)
            ->get(route('formalites.comptes-demandes.piece', [$demande, $media]).'?inline=1');
        $inline->assertOk();
        $this->assertStringContainsString(
            'inline',
            (string) $inline->headers->get('content-disposition')
        );

        $other = $this->makePendingDemande([
            'nif' => '999-888-777-6',
            'email' => 'other@example.com',
            'telephone' => '+50922009999',
            'pension_code' => '8-99999',
        ]);
        $this->actingAs($agent)
            ->get(route('formalites.comptes-demandes.piece', [$other, $media]))
            ->assertNotFound();
    }

    /** @test */
    public function accepted_request_cannot_be_processed_again(): void
    {
        $demande = $this->makePendingDemande();
        $this->markRendezVousRealise($demande);
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
        $this->markRendezVousRealise($demande);
        $demande->update(['user_id' => null]);

        $this->actingAs($this->makeFormalites())
            ->from(route('formalites.comptes-demandes.show', $demande))
            ->post(route('formalites.comptes-demandes.accepter', $demande), [])
            ->assertRedirect()
            ->assertSessionHasErrors('status');

        $this->assertSame(DemandeCreationCompte::STATUS_EN_ATTENTE, $demande->fresh()->status);
    }

    /** @test */
    public function provisional_user_can_only_open_appointment_pages(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatusEnum::PROVISOIRE,
        ]);
        $user->assignRole('pensionne');
        app(UserCategoryRoleSyncService::class)
            ->assignCategory($user, 'pensionne', primary: true, syncRoles: false);

        $this->actingAs($user)
            ->get(route('demandes.virements.create'))
            ->assertRedirect(route('compte.provisoire'));

        $this->actingAs($user)
            ->get(route('demandes.rencontre.create'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('personal.dashboard'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertRedirect(route('compte.provisoire'));

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk();
    }

    /** @test */
    public function realizing_an_appointment_records_history_for_pending_account_request(): void
    {
        $owner = User::factory()->create([
            'account_status' => AccountStatusEnum::PROVISOIRE,
        ]);
        $owner->assignRole('pensionne');
        $this->seedStatuses();
        $this->seedServices();

        $compte = DemandeCreationCompte::create([
            'code' => 'DCC-ACT-'.uniqid(),
            'user_type' => 'pensionne',
            'status' => DemandeCreationCompte::STATUS_EN_ATTENTE,
            'user_id' => $owner->id,
            'telephone' => '+50922000000',
            'adresse' => 'Port-au-Prince',
        ]);

        $demande = Demande::create([
            'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => $owner->id,
            'data' => [
                'motif_key' => 'demande_formalites_mandats',
                'rdv_statut' => RencontreStatutEnum::ACTIF->value,
                'date_souhaitee' => now()->subDay()->toDateString(),
                'heure_souhaitee' => '10:00',
            ],
        ]);

        app(RencontreWorkflowService::class)
            ->clore($demande->fresh(), $this->makeFormalites(), RencontreStatutEnum::REALISE);

        $this->assertTrue($owner->fresh()->isProvisionnel());
        $this->assertSame(
            AccountStatusEnum::PROVISOIRE,
            $owner->fresh()->account_status
        );
        $this->assertTrue($compte->fresh()->histories()->where('event', DemandeCreationCompteHistory::EVENT_RDV_REALISE)->exists());
    }

    /** @test */
    public function account_decision_uses_latest_realise_rdv_when_a_newer_active_rdv_exists(): void
    {
        $this->seedStatuses();
        $demande = $this->makePendingDemande();

        Demande::create([
            'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => $demande->user_id,
            'data' => [
                'motif_key' => 'demande_formalites_mandats',
                'rdv_statut' => RencontreStatutEnum::REALISE->value,
                'date_souhaitee' => now()->subDays(2)->toDateString(),
                'heure_souhaitee' => '10:00',
            ],
        ]);

        Demande::create([
            'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => $demande->user_id,
            'data' => [
                'motif_key' => 'demande_formalites_mandats',
                'rdv_statut' => RencontreStatutEnum::ACTIF->value,
                'date_souhaitee' => now()->addDay()->toDateString(),
                'heure_souhaitee' => '14:00',
            ],
        ]);

        $this->assertTrue($demande->fresh()->hasRendezVousRealise());
        $this->assertTrue($demande->fresh()->canBeDecided());
    }
}

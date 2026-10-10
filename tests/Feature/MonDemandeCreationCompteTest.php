<?php

namespace Tests\Feature;

use App\Enums\AccountStatusEnum;
use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\DemandeCreationCompte;
use App\Models\User;
use Database\Seeders\UserCategoriesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class MonDemandeCreationCompteTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        $this->seed(UserCategoriesSeeder::class);
    }

    private function provisionalWithDemande(): User
    {
        $user = User::factory()->create([
            'account_status' => AccountStatusEnum::PROVISOIRE,
        ]);
        $user->assignRole('pensionne');

        DemandeCreationCompte::create([
            'code' => 'DCC-MON-'.uniqid(),
            'user_type' => 'pensionne',
            'firstname' => 'Jean',
            'lastname' => 'Pierre',
            'telephone' => '+50922000001',
            'adresse' => 'Port-au-Prince',
            'status' => DemandeCreationCompte::STATUS_EN_ATTENTE,
            'soumise_at' => now(),
            'user_id' => $user->id,
        ]);

        return $user;
    }

    /** @test */
    public function provisional_user_can_view_their_account_creation_request(): void
    {
        $user = $this->provisionalWithDemande();

        $this->actingAs($user)
            ->get(route('compte.demande.show'))
            ->assertOk()
            ->assertSee('Ma demande de création de compte')
            ->assertSee('Jean');
    }

    /** @test */
    public function provisional_user_can_update_pending_request_fields(): void
    {
        $user = $this->provisionalWithDemande();

        $this->actingAs($user)
            ->patch(route('compte.demande.update'), [
                'firstname' => 'Jean',
                'lastname' => 'Paul',
                'telephone' => '+50922000002',
                'adresse' => 'Nouvelle adresse',
                'email' => 'jean.paul@example.com',
            ])
            ->assertRedirect(route('compte.demande.show'))
            ->assertSessionHas('success');

        $demande = $user->fresh()->demandeCreationCompte;
        $this->assertSame('Paul', $demande->lastname);
        $this->assertSame('+50922000002', $demande->telephone);
        $this->assertSame('jean.paul@example.com', $user->fresh()->email);
    }

    /** @test */
    public function provisional_user_cannot_open_pension_demarche_routes(): void
    {
        $user = $this->provisionalWithDemande();

        $this->actingAs($user)
            ->get(route('demandes.virements.create'))
            ->assertRedirect(route('compte.provisoire'));
    }

    /** @test */
    public function provisional_user_can_view_their_rencontre_demande(): void
    {
        $user = $this->provisionalWithDemande();

        $rdv = Demande::create([
            'code' => 'RDV-TEST-001',
            'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => $user->id,
            'data' => [
                'motif_key' => 'demande_formalites_mandats',
                'motif' => 'Demande de formalités de pension et mandats',
                'date_souhaitee' => '2026-12-01',
                'heure_souhaitee' => '14:00',
                'rdv_statut' => 'demande',
            ],
        ]);

        $this->actingAs($user)
            ->get(route('personal.request.authenticated-user-request.show', $rdv->id))
            ->assertOk()
            ->assertSee('RDV-TEST-001')
            ->assertSee('Suivi du rendez-vous');

        $this->actingAs($user)
            ->get(route('compte.provisoire'))
            ->assertOk()
            ->assertSee('Mes rendez-vous')
            ->assertSee('RDV-TEST-001');
    }

    /** @test */
    public function active_user_exposes_pension_identity_helpers(): void
    {
        $user = $this->createPensionneUser([
            'account_status' => AccountStatusEnum::ACTIF,
            'nif' => '123-456-789-0',
        ], '8-34321');

        $this->assertTrue($user->exposesPensionIdentity());
        $this->assertSame('123-456-789-0', $user->displayNif());
        $this->assertSame('8-34321', $user->displayPensionCode());
    }

    /** @test */
    public function provisional_user_masks_pension_identity_on_helpers(): void
    {
        $user = $this->createPensionneUser([
            'account_status' => AccountStatusEnum::PROVISOIRE,
            'nif' => '123-456-789-0',
        ], '8-34321');

        $this->assertFalse($user->exposesPensionIdentity());
        $this->assertNull($user->displayNif());
        $this->assertNull($user->displayPensionCode());
    }
}

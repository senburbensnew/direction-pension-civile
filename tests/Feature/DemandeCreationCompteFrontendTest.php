<?php

namespace Tests\Feature;

use App\Enums\AccountStatusEnum;
use App\Models\DemandeCreationCompte;
use Database\Seeders\UserCategoriesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsDemandeCreationCompteFixtures;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

/**
 * Parcours interface (Blade) — création de compte et suivi provisoire / agent Formalités.
 */
class DemandeCreationCompteFrontendTest extends TestCase
{
    use BuildsDemandeCreationCompteFixtures;
    use RefreshDatabase;
    use SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        $this->seedServices();
        $this->seed(UserCategoriesSeeder::class);
        Storage::fake('public');
        Storage::fake(config('uploads.disks.private', 'private'));
    }

    /** @test */
    public function guest_account_form_exposes_all_requester_profiles(): void
    {
        $this->get(route('demandes.compte.create'))
            ->assertOk()
            ->assertSee(__('guest.account_request.profile_mineur'))
            ->assertSee(__('guest.account_request.profile_mandataire'))
            ->assertSee('Pensionné')
            ->assertSee('name="is_mineur"', false)
            ->assertSee('name="is_mandataire"', false);
    }

    /** @test */
    public function guest_form_represents_mandataire_selection_after_validation_error(): void
    {
        $this->from(route('demandes.compte.create'))
            ->post(route('demandes.compte.store'), [
                'user_type' => 'externe',
                'is_mandataire' => '1',
                'telephone' => '+50938123456',
                'adresse' => 'Port-au-Prince',
            ])
            ->assertRedirect(route('demandes.compte.create'))
            ->assertSessionHasErrors();

        $this->get(route('demandes.compte.create'))
            ->assertOk()
            ->assertSee(__('guest.account_request.profile_mandataire'))
            ->assertSee(__('guest.account_request.mandataire_pensionne'));
    }

    /** @test */
    public function provisional_user_sees_pending_status_and_edit_form(): void
    {
        $demande = $this->makePendingTitulaireDemande();
        $user = $demande->user;

        $this->actingAs($user)
            ->get(route('compte.demande.show'))
            ->assertOk()
            ->assertSee('Ma demande de création de compte')
            ->assertSee('En attente')
            ->assertSee('Corriger ou compléter mes informations')
            ->assertSee('Jean');
    }

    /** @test */
    public function provisional_user_sees_refusal_reason_and_cannot_edit_after_refusal(): void
    {
        $demande = $this->makePendingTitulaireDemande([
            'status' => DemandeCreationCompte::STATUS_REFUSEE,
            'refusal_reason' => 'Pièce d’identité illisible.',
        ]);
        $user = $demande->user;

        $this->actingAs($user)
            ->get(route('compte.demande.show'))
            ->assertOk()
            ->assertSee('Refusée')
            ->assertSee('Motif du refus')
            ->assertSee('Pièce d’identité illisible.')
            ->assertDontSee('Corriger ou compléter mes informations');
    }

    /** @test */
    public function provisional_user_sees_accepted_status_without_edit_form(): void
    {
        $demande = $this->makePendingTitulaireDemande([
            'status' => DemandeCreationCompte::STATUS_ACCEPTEE,
            'approuvee_at' => now(),
        ]);
        $demande->user->update(['account_status' => AccountStatusEnum::ACTIF]);

        $this->actingAs($demande->user)
            ->get(route('compte.demande.show'))
            ->assertOk()
            ->assertSee('Acceptée')
            ->assertDontSee('Corriger ou compléter mes informations');
    }

    /** @test */
    public function formalites_agent_sees_titulaire_dossier_and_decision_after_rdv(): void
    {
        $demande = $this->makePendingTitulaireDemande();
        $demande->addMedia(UploadedFile::fake()->image('cin.jpg'))
            ->toMediaCollection('identite_cin', 'public');
        $agent = $this->makeFormalitesReviewer();

        $this->actingAs($agent)
            ->get(route('formalites.comptes-demandes.show', $demande))
            ->assertOk()
            ->assertSee('Demande de compte')
            ->assertSee('Jean')
            ->assertSee('rendez-vous réalisé', false)
            ->assertDontSee('Accepter le dossier');

        $this->attachRealisedFormalitesRendezVous($demande);

        $this->actingAs($agent)
            ->get(route('formalites.comptes-demandes.show', $demande->fresh()))
            ->assertOk()
            ->assertSee('Accepter le dossier')
            ->assertSee('Refuser le dossier')
            ->assertSee('Carte d’identification nationale');
    }

    /** @test */
    public function formalites_agent_sees_mandataire_dossier_on_show_page(): void
    {
        $demande = $this->makePendingMandataireDemande();
        $agent = $this->makeFormalitesReviewer();

        $this->actingAs($agent)
            ->get(route('formalites.comptes-demandes.show', $demande))
            ->assertOk()
            ->assertSee('Mandataire — code pension', false)
            ->assertSee($demande->code);
    }

    /** @test */
    public function formalites_agent_sees_minor_representative_dossier_on_show_page(): void
    {
        $demande = $this->makePendingMinorDemande();
        $agent = $this->makeFormalitesReviewer();

        $this->actingAs($agent)
            ->get(route('formalites.comptes-demandes.show', $demande))
            ->assertOk()
            ->assertSee('Pensionné mineur', false)
            ->assertSee('Enfant');
    }

    /** @test */
    public function provisional_portal_lists_rencontre_booking_entry_points(): void
    {
        $demande = $this->makePendingTitulaireDemande();
        $user = $demande->user;

        $this->actingAs($user)
            ->get(route('compte.provisoire'))
            ->assertOk()
            ->assertSee('Prendre rendez-vous', false)
            ->assertSee('Service Formalités', false);
    }
}

<?php

namespace Tests\Feature;

use App\Enums\AccountStatusEnum;
use App\Enums\BeneficiaireRelationDemandeStatusEnum;
use App\Enums\PensionMandatStatusEnum;
use App\Models\BeneficiaireRelationDemande;
use App\Models\DemandeCreationCompte;
use App\Models\DemandeCreationCompteHistory;
use App\Models\PensionMandat;
use App\Models\PensionnaireReference;
use App\Models\User;
use App\Services\AccountCreation\DemandeCreationCompteOwnerUpdateService;
use App\Services\AccountCreation\DemandeCreationCompteReviewService;
use App\Services\OCR\OcrService;
use Database\Seeders\UserCategoriesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Concerns\BuildsDemandeCreationCompteFixtures;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

/**
 * Logique HTTP / services — création de compte (tous profils et décisions Formalités).
 */
class DemandeCreationCompteBackendTest extends TestCase
{
    use BuildsDemandeCreationCompteFixtures;
    use RefreshDatabase;
    use SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(OcrService::class, function ($mock) {
            $mock->shouldReceive('extract')->andReturn("NOM : PIERRE\nPRENOM : Jean");
        });

        $this->seedRoles();
        $this->seedServices();
        $this->seedWorkflowSteps();
        $this->seed(UserCategoriesSeeder::class);
        Notification::fake();
        Storage::fake('public');
        Storage::fake(config('uploads.disks.private', 'private'));
    }

    /** @test */
    public function availability_endpoint_reports_pending_nif_conflict(): void
    {
        DemandeCreationCompte::create([
            'code' => 'DCC-AVAIL',
            'user_type' => 'pensionne',
            'nif' => '123-456-789-0',
            'telephone' => '+50922000000',
            'adresse' => 'PAP',
            'status' => DemandeCreationCompte::STATUS_EN_ATTENTE,
        ]);

        $this->getJson(route('demandes.compte.disponibilite', [
            'field' => 'nif',
            'value' => '123-456-789-0',
        ]))
            ->assertOk()
            ->assertJson(['available' => false]);

        $this->getJson(route('demandes.compte.disponibilite', [
            'field' => 'nif',
            'value' => '999-888-777-6',
        ]))
            ->assertOk()
            ->assertJson(['available' => true]);
    }

    /** @test */
    public function ocr_endpoint_returns_extracted_fields_for_identity_upload(): void
    {
        $file = UploadedFile::fake()->image('cin.jpg', 400, 300);

        $this->post(route('demandes.compte.ocr'), [
            'user_type' => 'pensionne',
            'telephone' => '+50922000000',
            'nif' => '123-456-789-0',
            'pension_code' => '8-34321',
            'adresse' => 'Port-au-Prince',
            'piece_identite_type' => 'cin',
            'piece_identite' => $file,
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('fields.nom', 'PIERRE');
    }

    /** @test */
    public function store_creates_provisional_pensionne_with_media_and_history(): void
    {
        PensionnaireReference::query()->updateOrCreate(
            ['PENSIONNAIRE_ID' => '8-34321'],
            ['NOM' => 'PIERRE', 'PRENOM' => 'JEAN']
        );

        $this->post(route('demandes.compte.store'), [
            'user_type' => 'pensionne',
            'telephone' => '+50922000000',
            'nif' => '111-222-333-4',
            'pension_code' => '8-34321',
            'email' => 'backend.titulaire@example.com',
            'adresse' => 'Port-au-Prince',
            'piece_identite_type' => 'cin',
            'piece_identite' => UploadedFile::fake()->image('cin.jpg'),
            'password' => 'password123',
            'accept_terms' => '1',
        ])->assertRedirect();

        $demande = DemandeCreationCompte::query()->latest('id')->first();
        $this->assertNotNull($demande);
        $this->assertTrue($demande->isPending());
        $this->assertTrue($demande->hasMedia('identite_cin'));
        $this->assertTrue($demande->histories()->where('event', DemandeCreationCompteHistory::EVENT_SOUMISE)->exists());
        $this->assertTrue(Hash::check('password123', $demande->user->password));
    }

    /** @test */
    public function owner_update_service_rejects_non_owner(): void
    {
        $demande = $this->makePendingTitulaireDemande();
        $intruder = User::factory()->create();

        $this->expectException(HttpException::class);
        app(DemandeCreationCompteOwnerUpdateService::class)->assertOwnerCanEdit($demande, $intruder);
    }

    /** @test */
    public function owner_update_persists_titulaire_fields_via_http(): void
    {
        $demande = $this->makePendingTitulaireDemande();
        $user = $demande->user;

        $this->actingAs($user)
            ->patch(route('compte.demande.update'), [
                'firstname' => 'Marie',
                'lastname' => 'Claire',
                'email' => 'marie.claire@example.com',
                'telephone' => '+50938123456',
                'adresse' => 'Delmas',
            ])
            ->assertRedirect(route('compte.demande.show'))
            ->assertSessionHas('success');

        $demande->refresh();
        $this->assertSame('Marie', $demande->firstname);
        $this->assertSame('marie.claire@example.com', $user->fresh()->email);
    }

    /** @test */
    public function review_service_accepts_titulaire_after_realised_rdv(): void
    {
        $demande = $this->makePendingTitulaireDemande();
        $this->attachRealisedFormalitesRendezVous($demande);
        $reviewer = $this->makeFormalitesReviewer();

        app(DemandeCreationCompteReviewService::class)->accept($demande, $reviewer, [
            'email' => 'jean.pierre@example.com',
        ]);

        $demande->refresh();
        $this->assertSame(DemandeCreationCompte::STATUS_ACCEPTEE, $demande->status);
        $this->assertSame(AccountStatusEnum::ACTIF, $demande->user->account_status);
    }

    /** @test */
    public function review_service_refuses_mandataire_and_blocks_account(): void
    {
        $demande = $this->makePendingMandataireDemande();
        $this->attachRealisedFormalitesRendezVous($demande);
        $reviewer = $this->makeFormalitesReviewer();

        app(DemandeCreationCompteReviewService::class)->refuse(
            $demande,
            $reviewer,
            'Dossier mandataire incomplet.'
        );

        $demande->refresh();
        $this->assertSame(DemandeCreationCompte::STATUS_REFUSEE, $demande->status);
        $this->assertSame(AccountStatusEnum::BLOQUE, $demande->user->account_status);

        $mandat = PensionMandat::query()->where('demande_creation_compte_id', $demande->id)->first();
        $this->assertSame(PensionMandatStatusEnum::REFUSE, $mandat?->status);
    }

    /** @test */
    public function formalites_http_accept_and_refuse_routes_match_service_outcomes(): void
    {
        $demande = $this->makePendingTitulaireDemande([
            'email' => 'http.accept@example.com',
        ]);
        $this->attachRealisedFormalitesRendezVous($demande);
        $agent = $this->makeFormalitesReviewer();

        $this->actingAs($agent)
            ->post(route('formalites.comptes-demandes.accepter', $demande), [
                'email' => 'http.accept@example.com',
            ])
            ->assertRedirect(route('formalites.comptes-demandes.show', $demande));

        $this->assertSame(DemandeCreationCompte::STATUS_ACCEPTEE, $demande->fresh()->status);

        $refused = $this->makePendingTitulaireDemande([
            'email' => 'http.refuse@example.com',
            'nif' => '555-666-777-8',
            'pension_code' => '8-55555',
        ]);
        $this->attachRealisedFormalitesRendezVous($refused);

        $this->actingAs($agent)
            ->post(route('formalites.comptes-demandes.refuser', $refused), [
                'refusal_reason' => 'Refus via route HTTP.',
            ])
            ->assertRedirect(route('formalites.comptes-demandes.show', $refused));

        $this->assertSame(DemandeCreationCompte::STATUS_REFUSEE, $refused->fresh()->status);
    }

    /** @test */
    public function review_service_accepts_minor_and_links_beneficiary_relation(): void
    {
        $demande = $this->makePendingMinorDemande();
        $this->attachRealisedFormalitesRendezVous($demande);
        $reviewer = $this->makeFormalitesReviewer();

        app(DemandeCreationCompteReviewService::class)->accept($demande, $reviewer, [
            'email' => 'mere.rep@example.com',
        ]);

        $relation = BeneficiaireRelationDemande::query()
            ->where('demande_creation_compte_id', $demande->id)
            ->first();
        $this->assertSame(BeneficiaireRelationDemandeStatusEnum::ACCEPTEE, $relation?->status);
    }
}

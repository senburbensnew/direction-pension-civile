<?php

namespace Tests\Feature;

use App\Enums\AccountStatusEnum;
use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\DemandeCreationCompte;
use App\Models\PensionnaireReference;
use App\Models\User;
use App\Notifications\DemandeCreationCompteSoumise;
use App\Services\OCR\OcrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class DemandeCreationCompteTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(OcrService::class, function ($mock) {
            $mock->shouldReceive('extract')->andReturn("NOM : PIERRE\nPRENOM : Jean");
        });

        $this->seedPensionReference();
    }

    private function seedPensionReference(
        string $code = '8-34321',
        string $nom = 'PIERRE',
        string $prenom = 'JEAN'
    ): void {
        PensionnaireReference::query()->updateOrCreate(
            ['PENSIONNAIRE_ID' => strtoupper(trim($code))],
            ['NOM' => $nom, 'PRENOM' => $prenom]
        );
    }

    private function acteNaissanceOcrText(): string
    {
        return <<<TXT
        REPUBLIQUE D'HAITI
        ACTE DE NAISSANCE
        Numéro d'acte : 12345
        Nom : MILORME
        Prénom : PIERRE RUBENS
        Sexe : M
        Date de naissance : 31/07/1990
        Lieu de naissance : JACMEL
        Père : JEAN MILORME
        Mère : MARIE LOUIS
        Commune : JACMEL
        Date de l'acte : 05/08/1990
        TXT;
    }

    private function permisOcrText(): string
    {
        return <<<TXT
        REPUBLIQUE D'HAITI
        Service de la Circulation
        PERMIS DE CONDUIRE
        Dossier
        UU-32806-BC
        NIF
        004-521-636-4
        Nom
        MILORME, PIERRE RUBENS
        Adresse
        LAMANDOU1, JACMEL
        Type
        BC
        Date de Naissance
        Sexe
        G. Sang
        31/07/1990
        M
        O+
        Lieu d'émission
        Emis le
        Expire le
        P-AU-P
        29/12/2023
        31/07/2028
        TXT;
    }

    private function matriculeFiscalOcrText(): string
    {
        return <<<TXT
        REPUBLIQUE D'HAITI
        DIRECTION GENERALE DES IMPOTS
        MATRICULE FISCAL DU CONTRIBUABLE
        NIF
        004-521-636-4
        Nom
        MILORME
        Prénom
        PIERRE RUBENS
        Profession / Secteur d'Activité
        TRAVAILLEURS N'AYANT DECLARE AUCUNE PROFESSION
        Code SAE
        Adresses : 1
        LAMANDOU, JACMEL
        Date
        04-NOV-25
        TXT;
    }

    /** @test */
    public function guest_can_see_the_account_request_form(): void
    {
        $this->get(route('demandes.compte.create'))
            ->assertOk()
            ->assertSee('Demande de création de compte')
            ->assertSee('OCR')
            ->assertSee('Étape')
            ->assertSee('Soumettre')
            ->assertSee('Type de compte')
            ->assertSee('Pensionné')
            ->assertSee('Numéro de téléphone')
            ->assertSee('NIF')
            ->assertSee('Code pension')
            ->assertSee('JPG, PNG, WEBP ou PDF')
            ->assertSee('Mon profil')
            ->assertDontSee('id="email"', false)
            ->assertDontSee('id="ninu"', false)
            ->assertSee('Mot de passe')
            ->assertDontSee('password_confirmation')
            ->assertDontSee('Nom d’utilisateur')
            ->assertSee('Adresse')
            ->assertSee('Pensionné mineur')
            ->assertSee('Permis de conduire')
            ->assertSee('Passeport')
            ->assertSee('Choisir un document')
            ->assertSee('Carte d’identification nationale')
            ->assertSee('conditions d’utilisation')
            ->assertSee('politique de confidentialité')
            ->assertSee('Lecture et vérification OCR')
            ->assertSee('Informations extraites')
            ->assertDontSee('GALIPEC')
            ->assertDontSee('Fonctionnaire', false);
    }

    /** @test */
    public function guest_can_submit_a_pensionne_account_request(): void
    {
        Storage::fake('public');

        $response = $this->post(route('demandes.compte.store'), [
            'user_type' => 'pensionne',
            'telephone' => '+509 22 00 0000',
            'nif' => '123-456-789-0',
            'pension_code' => '8-34321',
            'email' => 'jean.pierre@example.com',
            'adresse' => '12, rue Capois, Port-au-Prince',
            'piece_identite_type' => 'cin',
            'piece_identite' => UploadedFile::fake()->image('cin.jpg', 800, 600),
            'password' => 'password123',
            'accept_terms' => '1',
        ]);

        $response->assertRedirect(route('demandes.rencontre.create'));
        $response->assertSessionMissing('success');
        $this->assertAuthenticated();

        $this->assertDatabaseHas('demande_creation_comptes', [
            'telephone' => '+509 22 00 0000',
            'nif' => '123-456-789-0',
            'pension_code' => '8-34321',
            'email' => 'jean.pierre@example.com',
            'adresse' => '12, rue Capois, Port-au-Prince',
            'user_type' => 'pensionne',
            'status' => DemandeCreationCompte::STATUS_EN_ATTENTE,
        ]);
        $this->assertDatabaseMissing('demandes', [
            'type' => TypeDemandeEnum::DEMANDE_CREATION_COMPTE->value,
        ]);

        $demande = DemandeCreationCompte::first();
        $this->assertFalse($demande->is_mineur);
        $this->assertNotNull($demande->accepted_terms_at);
        $this->assertNotNull($demande->soumise_at);
        $this->assertNotNull($demande->user_id);
        $this->assertTrue($demande->hasMedia('identite_cin'));
        $this->assertFalse($demande->hasMedia('identite_passeport'));
        $this->assertSame(['cin'], $demande->pieces_identite);
        $this->assertSame(0, Demande::count());
        $this->assertSame(1, User::count());
        $user = User::first();
        $this->assertTrue($user->isProvisionnel());
        $this->assertSame(AccountStatusEnum::PROVISOIRE, $user->account_status);
        $this->assertNull($user->email_verified_at);
        $this->assertTrue($user->hasRole('pensionne'));
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertIsArray($demande->submitted_payload);
        $this->assertSame('+509 22 00 0000', $demande->submitted_payload['telephone'] ?? null);
        $this->assertArrayNotHasKey('password', $demande->submitted_payload);
        $this->assertTrue($demande->histories()->where('event', \App\Models\DemandeCreationCompteHistory::EVENT_SOUMISE)->exists());
        $this->assertTrue($demande->histories()->where('event', \App\Models\DemandeCreationCompteHistory::EVENT_COMPTE_CREE)->exists());
    }

    /** @test */
    public function guest_can_open_rdv_page_after_account_submission(): void
    {
        Storage::fake('public');
        $this->seedRoles();
        $this->seedServices();

        $this->post(route('demandes.compte.store'), $this->validAccountStorePayload())
            ->assertRedirect(route('demandes.rencontre.create'));

        $provisional = User::query()
            ->where('account_status', AccountStatusEnum::PROVISOIRE)
            ->firstOrFail();

        $this->assertAuthenticatedAs($provisional);

        $this->get(route('demandes.rencontre.create'))
            ->assertOk()
            ->assertSee('Demande de rendez-vous');
    }

    /** @test */
    public function submitting_while_logged_in_as_agent_switches_to_provisional_user(): void
    {
        Storage::fake('public');
        $this->seedRoles();
        $this->seedServices();

        $agent = User::factory()->create();
        $agent->assignRole('fonctionnaire');

        $this->actingAs($agent);

        $this->post(route('demandes.compte.store'), $this->validAccountStorePayload([
            'telephone' => '+509 22 00 0011',
            'nif' => '123-456-789-1',
            'email' => 'autre.pensionne@example.com',
        ]))->assertRedirect(route('demandes.rencontre.create'));

        $provisional = User::query()
            ->where('account_status', AccountStatusEnum::PROVISOIRE)
            ->firstOrFail();

        $this->assertAuthenticatedAs($provisional);
        $this->assertNotSame($agent->id, $provisional->id);

        $this->get(route('demandes.rencontre.create'))
            ->assertOk()
            ->assertSee('Demande de rendez-vous');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validAccountStorePayload(array $overrides = []): array
    {
        return array_merge([
            'user_type' => 'pensionne',
            'telephone' => '+509 22 00 0000',
            'nif' => '123-456-789-0',
            'pension_code' => '8-34321',
            'email' => 'jean.pierre@example.com',
            'adresse' => '12, rue Capois, Port-au-Prince',
            'piece_identite_type' => 'cin',
            'piece_identite' => UploadedFile::fake()->image('cin.jpg', 800, 600),
            'password' => 'password123',
            'accept_terms' => '1',
        ], $overrides);
    }

    /** @test */
    public function provisional_user_receives_notification_after_submission(): void
    {
        Notification::fake();
        Storage::fake('public');

        $this->post(route('demandes.compte.store'), [
            'user_type' => 'pensionne',
            'telephone' => '+509 22 00 0000',
            'nif' => '123-456-789-0',
            'pension_code' => '8-34321',
            'email' => 'jean.pierre@example.com',
            'adresse' => '12, rue Capois, Port-au-Prince',
            'piece_identite_type' => 'cin',
            'piece_identite' => UploadedFile::fake()->image('cin.jpg', 800, 600),
            'password' => 'password123',
            'accept_terms' => '1',
        ])->assertRedirect(route('demandes.rencontre.create'));

        Notification::assertSentTo(
            User::first(),
            DemandeCreationCompteSoumise::class,
            fn (DemandeCreationCompteSoumise $notification) => $notification->demande->id === DemandeCreationCompte::first()->id
        );
    }

    /** @test */
    public function provisional_user_is_notified_even_without_email(): void
    {
        Notification::fake();
        Storage::fake('public');

        $this->post(route('demandes.compte.store'), [
            'user_type' => 'pensionne',
            'telephone' => '+509 22 00 0099',
            'nif' => '123-456-789-9',
            'pension_code' => '8-34321',
            'adresse' => '12, rue Capois, Port-au-Prince',
            'piece_identite_type' => 'cin',
            'piece_identite' => UploadedFile::fake()->image('cin.jpg', 800, 600),
            'password' => 'password123',
            'accept_terms' => '1',
        ])->assertRedirect(route('demandes.rencontre.create'));

        Notification::assertSentTo(User::first(), DemandeCreationCompteSoumise::class);
    }

    /** @test */
    public function guest_cannot_submit_without_a_nif(): void
    {
        Storage::fake('public');

        $this->post(route('demandes.compte.store'), [
            'user_type' => 'pensionne',
            'telephone' => '+509 22 00 0000',
            'pension_code' => '8-34321',
            'adresse' => '12, rue Capois, Port-au-Prince',
            'piece_identite_type' => 'cin',
            'piece_identite' => UploadedFile::fake()->create('cin.pdf', 120, 'application/pdf'),
            'password' => 'password123',
            'accept_terms' => '1',
        ])->assertSessionHasErrors('nif');

        $this->assertNull(DemandeCreationCompte::first());
    }

    /** @test */
    public function pension_code_is_optional_for_a_pensionne(): void
    {
        Storage::fake('public');

        $this->post(route('demandes.compte.store'), [
            'user_type' => 'pensionne',
            'telephone' => '+509 22 00 0000',
            'nif' => '123-456-789-0',
            'adresse' => '12, rue Capois, Port-au-Prince',
            'piece_identite_type' => 'cin',
            'piece_identite' => UploadedFile::fake()->image('cin.jpg', 800, 600),
            'password' => 'password123',
            'accept_terms' => '1',
        ])
            ->assertRedirect(route('demandes.rencontre.create'));

        $demande = DemandeCreationCompte::first();
        $this->assertNull($demande->pension_code);
    }

    /** @test */
    public function ajax_store_returns_json_redirect_url_after_success(): void
    {
        Storage::fake('public');

        $this->post(route('demandes.compte.store'), [
            'user_type' => 'pensionne',
            'telephone' => '+509 22 00 0000',
            'nif' => '123-456-789-0',
            'adresse' => '12, rue Capois, Port-au-Prince',
            'piece_identite_type' => 'cin',
            'piece_identite' => UploadedFile::fake()->image('cin.jpg', 800, 600),
            'password' => 'password123',
            'accept_terms' => '1',
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('redirect_url', route('demandes.rencontre.create'));

        $this->assertAuthenticated();
    }

    /** @test */
    public function guest_can_submit_a_minor_pensionne_account_request(): void
    {
        Storage::fake('public');

        $this->post(route('demandes.compte.store'), [
            'user_type' => 'pensionne',
            'telephone' => '+509 38123456',
            'nif' => '123-456-789-0',
            'pension_code' => '8-34321',
            'adresse' => 'Delmas 31',
            'is_mineur' => '1',
            'acte_naissance' => UploadedFile::fake()->image('naissance.jpg', 800, 600),
            'representant_lien' => 'mere',
            'piece_identite_representant_type' => 'passeport',
            'piece_identite_representant' => UploadedFile::fake()->image('passeport.jpg', 800, 600),
            'password' => 'password123',
            'accept_terms' => '1',
        ])->assertRedirect(route('demandes.rencontre.create'));

        $demande = DemandeCreationCompte::first();
        $this->assertTrue($demande->is_mineur);
        $this->assertSame('123-456-789-0', $demande->nif);
        $this->assertSame('8-34321', $demande->pension_code);
        $this->assertNull($demande->email);
        $this->assertSame('mere', $demande->representant_lien);
        $this->assertTrue($demande->hasMedia('acte_naissance'));
        $this->assertTrue($demande->hasMedia('identite_representant_passeport'));
        $this->assertDatabaseCount('users', 1);
        $this->assertTrue($demande->user->isProvisionnel());

        $beneficiaire = \App\Models\Beneficiaire::query()->where('is_mineur', true)->first();
        $this->assertNotNull($beneficiaire);
        $this->assertSame('123-456-789-0', $beneficiaire->nif);
        $this->assertTrue(
            \App\Models\UserBeneficiaireLink::query()
                ->where('user_id', $demande->user_id)
                ->where('beneficiaire_id', $beneficiaire->id)
                ->where('relation', 'mere')
                ->exists()
        );

        $user = $demande->user->fresh(['roles', 'categoryAssignments']);
        $resolver = \App\Services\Account\UserCategoryResolver::for($user);
        $this->assertFalse($user->hasRole('pensionne'));
        $this->assertTrue($resolver->hasCategory('externe'));
        $this->assertTrue($resolver->hasPensionPortalAccess());
        $this->assertFalse($demande->representant_est_pensionne);
    }

    /** @test */
    public function minor_representative_can_also_be_pensionne_titulaire_when_declared(): void
    {
        Storage::fake('public');

        $this->seedPensionReference('8-34322', 'DUPONT', 'MARIE');

        $this->post(route('demandes.compte.store'), [
            'user_type' => 'pensionne',
            'telephone' => '+509 38123457',
            'nif' => '123-456-789-1',
            'pension_code' => '8-34322',
            'adresse' => 'Delmas 32',
            'is_mineur' => '1',
            'acte_naissance' => UploadedFile::fake()->image('naissance.jpg', 800, 600),
            'representant_lien' => 'pere',
            'representant_est_pensionne' => '1',
            'piece_identite_representant_type' => 'passeport',
            'piece_identite_representant' => UploadedFile::fake()->image('passeport.jpg', 800, 600),
            'password' => 'password123',
            'accept_terms' => '1',
        ])->assertRedirect(route('demandes.rencontre.create'));

        $demande = DemandeCreationCompte::query()->latest('id')->first();
        $this->assertTrue($demande->representant_est_pensionne);

        $user = $demande->user->fresh(['roles', 'categoryAssignments']);
        $this->assertTrue($user->hasRole('pensionne'));
        $resolver = \App\Services\Account\UserCategoryResolver::for($user);
        $this->assertTrue($resolver->hasCategory('externe'));
        $this->assertTrue($resolver->hasCategory('pensionne'));
    }

    /** @test */
    public function guest_can_submit_mandataire_account_request(): void
    {
        Storage::fake('public');

        $this->seedPensionReference('8-34399', 'LAMOUR', 'ROSE');

        $this->post(route('demandes.compte.store'), [
            'user_type' => 'pensionne',
            'telephone' => '+509 38123458',
            'pension_code' => '8-34399',
            'adresse' => 'Delmas 33',
            'is_mandataire' => '1',
            'piece_identite_type' => 'passeport',
            'piece_identite' => UploadedFile::fake()->image('passeport.jpg', 800, 600),
            'mandat_designation' => UploadedFile::fake()->create('mandat.pdf', 100, 'application/pdf'),
            'password' => 'password123',
            'accept_terms' => '1',
        ])->assertRedirect(route('demandes.rencontre.create'));

        $demande = DemandeCreationCompte::query()->latest('id')->first();
        $this->assertTrue($demande->is_mandataire);
        $this->assertFalse($demande->is_mineur);
        $this->assertSame('8-34399', $demande->pension_code);
        $this->assertTrue($demande->hasMedia('mandat_designation'));

        $user = $demande->user->fresh(['categoryAssignments']);
        $resolver = \App\Services\Account\UserCategoryResolver::for($user);
        $this->assertTrue($resolver->hasCategory('externe'));
        $this->assertFalse($user->hasRole('pensionne'));
        $this->assertTrue($resolver->hasPensionPortalAccess());

        $this->assertDatabaseHas('pension_mandats', [
            'user_id' => $user->id,
            'status' => 'en_attente',
        ]);

        $this->assertFalse(
            \App\Models\UserBeneficiaireLink::query()->where('user_id', $user->id)->exists()
        );
    }

    /** @test */
    public function guest_cannot_submit_without_required_fields(): void
    {
        $this->from(route('demandes.compte.create'))
            ->post(route('demandes.compte.store'), [])
            ->assertRedirect(route('demandes.compte.create'))
            ->assertSessionHasErrors(['user_type', 'telephone', 'adresse', 'password', 'accept_terms'])
            ->assertSessionDoesntHaveErrors('nif');
    }

    /** @test */
    public function guest_cannot_submit_without_accepting_terms(): void
    {
        Storage::fake('public');

        $this->from(route('demandes.compte.create'))
            ->post(route('demandes.compte.store'), [
                'user_type' => 'pensionne',
                'telephone' => '+509 22 00 0000',
                'nif' => '123-456-789-0',
            'pension_code' => '8-34321',
                'adresse' => '12, rue Capois',
                'piece_identite_type' => 'cin',
                'piece_identite' => UploadedFile::fake()->image('cin.jpg', 800, 600),
            ])
            ->assertRedirect(route('demandes.compte.create'))
            ->assertSessionHasErrors('accept_terms');

        $this->assertDatabaseCount('demande_creation_comptes', 0);
    }

    /** @test */
    public function guest_cannot_use_an_existing_nif_or_pension_code(): void
    {
        Storage::fake('public');
        $this->createPensionneUser([
            'nif' => '123-456-789-0',
        ], '8-34321');

        $this->from(route('demandes.compte.create'))
            ->post(route('demandes.compte.store'), [
                'user_type' => 'pensionne',
                'telephone' => '+509 22 00 0000',
                'nif' => '123-456-789-0',
                'pension_code' => '1-00000',
                'adresse' => '12, rue Capois, Port-au-Prince',
                'piece_identite_type' => 'cin',
                'piece_identite' => UploadedFile::fake()->image('cin.jpg', 800, 600),
                'password' => 'password123',
            'accept_terms' => '1',
            ])
            ->assertRedirect(route('demandes.compte.create'))
            ->assertSessionHasErrors('nif');

        $this->from(route('demandes.compte.create'))
            ->post(route('demandes.compte.store'), [
                'user_type' => 'pensionne',
                'telephone' => '+509 22 00 0000',
                'nif' => '987-654-321-0',
                'pension_code' => '8-34321',
                'adresse' => '12, rue Capois, Port-au-Prince',
                'piece_identite_type' => 'cin',
                'piece_identite' => UploadedFile::fake()->image('cin.jpg', 800, 600),
                'password' => 'password123',
            'accept_terms' => '1',
            ])
            ->assertRedirect(route('demandes.compte.create'))
            ->assertSessionHasErrors('pension_code');

        $this->assertDatabaseCount('demande_creation_comptes', 0);
    }

    /** @test */
    public function guest_cannot_use_an_existing_email(): void
    {
        Storage::fake('public');
        User::factory()->create(['email' => 'jean.pierre@example.com']);

        $this->from(route('demandes.compte.create'))
            ->post(route('demandes.compte.store'), [
                'user_type' => 'pensionne',
                'telephone' => '+509 22 00 0000',
                'nif' => '123-456-789-0',
                'pension_code' => '8-34321',
                'email' => 'Jean.Pierre@example.com',
                'adresse' => '12, rue Capois, Port-au-Prince',
                'piece_identite_type' => 'cin',
                'piece_identite' => UploadedFile::fake()->image('cin.jpg', 800, 600),
                'password' => 'password123',
            'accept_terms' => '1',
            ])
            ->assertRedirect(route('demandes.compte.create'))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('demande_creation_comptes', 0);
    }

    /** @test */
    public function guest_can_check_nif_and_pension_code_availability_while_typing(): void
    {
        $this->createPensionneUser([
            'nif' => '123-456-789-0',
        ], '8-34321');

        $this->getJson(route('demandes.compte.disponibilite', [
            'field' => 'nif',
            'value' => '987-654-321-0',
        ]))
            ->assertOk()
            ->assertJsonPath('available', true);

        $this->getJson(route('demandes.compte.disponibilite', [
            'field' => 'nif',
            'value' => '123-456-789-0',
        ]))
            ->assertOk()
            ->assertJsonPath('available', false)
            ->assertJsonPath('message', 'Ce NIF est déjà utilisé.');

        $this->getJson(route('demandes.compte.disponibilite', [
            'field' => 'pension_code',
            'value' => '1-00000',
        ]))
            ->assertOk()
            ->assertJsonPath('available', true);

        $this->getJson(route('demandes.compte.disponibilite', [
            'field' => 'pension_code',
            'value' => '8-34321',
        ]))
            ->assertOk()
            ->assertJsonPath('available', false)
            ->assertJsonPath('message', 'Ce code pension est déjà utilisé.');
    }

    /** @test */
    public function guest_can_check_email_availability_while_typing(): void
    {
        User::factory()->create(['email' => 'jean.pierre@example.com']);

        $this->getJson(route('demandes.compte.disponibilite', [
            'field' => 'email',
            'value' => 'autre@example.com',
        ]))
            ->assertOk()
            ->assertJsonPath('available', true);

        $this->getJson(route('demandes.compte.disponibilite', [
            'field' => 'email',
            'value' => 'Jean.Pierre@example.com',
        ]))
            ->assertOk()
            ->assertJsonPath('available', false)
            ->assertJsonPath('message', 'Cette adresse e-mail est déjà utilisée.');

        $this->getJson(route('demandes.compte.disponibilite', [
            'field' => 'email',
            'value' => '',
        ]))
            ->assertOk()
            ->assertJsonPath('available', true)
            ->assertJsonPath('empty', true);
    }

    /** @test */
    public function guest_can_extract_ocr_fields_from_identity_document(): void
    {
        Storage::fake('public');

        $this->post(route('demandes.compte.ocr'), [
            'user_type' => 'pensionne',
            'telephone' => '+509 22 00 0000',
            'nif' => '123-456-789-0',
            'pension_code' => '8-34321',
            'adresse' => '12, rue Capois, Port-au-Prince',
            'piece_identite_type' => 'cin',
            'piece_identite' => UploadedFile::fake()->image('cin.jpg', 800, 600),
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('fields.nom', 'PIERRE')
            ->assertJsonPath('fields.prenom', 'Jean')
            ->assertJsonPath('documents.0.label', 'Pièce d’identité');
    }

    /** @test */
    public function guest_ocr_extracts_licence_fields_for_step_two(): void
    {
        Storage::fake('public');

        $this->mock(OcrService::class, function ($mock) {
            $mock->shouldReceive('extract')->andReturn($this->permisOcrText());
        });

        $this->post(route('demandes.compte.ocr'), [
            'user_type' => 'pensionne',
            'telephone' => '+509 22 00 0000',
            'nif' => '123-456-789-0',
            'pension_code' => '8-34321',
            'adresse' => '12, rue Capois, Port-au-Prince',
            'piece_identite_type' => 'permis',
            'piece_identite' => UploadedFile::fake()->image('permis.jpg', 800, 600),
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('fields.type_document', 'permis')
            ->assertJsonPath('fields.nom', 'MILORME')
            ->assertJsonPath('fields.prenom', 'PIERRE RUBENS')
            ->assertJsonPath('fields.nif', '0045216364')
            ->assertJsonPath('fields.dossier', 'UU-32806-BC')
            ->assertJsonPath('fields.emis_le', '29/12/2023')
            ->assertJsonPath('fields.expire_le', '31/07/2028')
            ->assertJsonPath('documents.0.fields.nom', 'MILORME');
    }

    /** @test */
    public function guest_ocr_extracts_matricule_fiscal_fields(): void
    {
        Storage::fake('public');

        $this->mock(OcrService::class, function ($mock) {
            $mock->shouldReceive('extract')->andReturn($this->matriculeFiscalOcrText());
        });

        $this->post(route('demandes.compte.ocr'), [
            'piece_identite_type' => 'matricule_fiscal',
            'piece_identite' => UploadedFile::fake()->image('matricule.jpg', 1200, 800),
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('fields.type_document', 'matricule_fiscal')
            ->assertJsonPath('fields.nif', '0045216364')
            ->assertJsonPath('fields.nom', 'MILORME')
            ->assertJsonPath('fields.prenom', 'PIERRE RUBENS')
            ->assertJsonMissingPath('fields.profession')
            ->assertJsonMissingPath('fields.date_emission')
            ->assertJsonMissingPath('fields.adresse');
    }

    /** @test */
    public function guest_ocr_extracts_birth_certificate_and_guardian_id_for_a_minor(): void
    {
        Storage::fake('public');

        $this->mock(OcrService::class, function ($mock) {
            $mock->shouldReceive('extract')
                ->once()
                ->andReturn($this->acteNaissanceOcrText());
            $mock->shouldReceive('extract')
                ->once()
                ->andReturn($this->permisOcrText());
        });

        $this->post(route('demandes.compte.ocr'), [
            'user_type' => 'pensionne',
            'telephone' => '+509 22 00 0000',
            'nif' => '123-456-789-0',
            'pension_code' => '8-34321',
            'adresse' => 'Delmas 31',
            'is_mineur' => '1',
            'acte_naissance' => UploadedFile::fake()->image('naissance.jpg', 800, 600),
            'representant_lien' => 'mere',
            'piece_identite_representant_type' => 'permis',
            'piece_identite_representant' => UploadedFile::fake()->image('permis.jpg', 800, 600),
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('documents.0.key', 'acte_naissance')
            ->assertJsonPath('documents.0.fields.type_document', 'acte_naissance')
            ->assertJsonPath('documents.0.fields.nom', 'MILORME')
            ->assertJsonPath('documents.0.fields.nom_pere', 'JEAN MILORME')
            ->assertJsonPath('documents.1.key', 'piece_identite_representant')
            ->assertJsonPath('documents.1.fields.type_document', 'permis')
            ->assertJsonPath('fields.nom', 'MILORME')
            ->assertJsonPath('fields.prenom', 'PIERRE RUBENS')
            ->assertJsonPath('fields.nif', '0045216364');
    }

    /** @test */
    public function guest_cannot_submit_without_a_password(): void
    {
        Storage::fake('public');

        $this->from(route('demandes.compte.create'))
            ->post(route('demandes.compte.store'), [
                'user_type' => 'pensionne',
                'telephone' => '+509 22 00 0000',
                'nif' => '123-456-789-0',
                'pension_code' => '8-34321',
                'adresse' => '12, rue Capois, Port-au-Prince',
                'piece_identite_type' => 'cin',
                'piece_identite' => UploadedFile::fake()->image('cin.jpg', 800, 600),
                'accept_terms' => '1',
            ])
            ->assertRedirect(route('demandes.compte.create'))
            ->assertSessionHasErrors('password');

        $this->assertDatabaseCount('users', 0);
    }
}

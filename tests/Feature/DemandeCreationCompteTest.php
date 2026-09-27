<?php

namespace Tests\Feature;

use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\DemandeCreationCompte;
use App\Models\User;
use App\Services\OCR\OcrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DemandeCreationCompteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(OcrService::class, function ($mock) {
            $mock->shouldReceive('extract')->andReturn("NOM : PIERRE\nPRENOM : Jean");
        });
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

    /** @test */
    public function guest_can_see_the_account_request_form(): void
    {
        $this->get(route('demandes.compte.create'))
            ->assertOk()
            ->assertSee('Demande de création de compte')
            ->assertSee('Formulaire')
            ->assertSee('OCR')
            ->assertSee('Résumé')
            ->assertSee('Soumettre')
            ->assertSee('Type de compte')
            ->assertSee('Pensionné')
            ->assertSee('Numéro de téléphone')
            ->assertSee('NIF')
            ->assertSee('Code pension')
            ->assertSee('JPG, PNG, WEBP ou PDF')
            ->assertSee('Adresse e-mail')
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
            ->assertSee('Document fourni et données extraites')
            ->assertSee('Données lues')
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
        $this->assertNotNull($demande->user_id);
        $this->assertTrue($demande->hasMedia('identite_cin'));
        $this->assertFalse($demande->hasMedia('identite_passeport'));
        $this->assertSame(['cin'], $demande->pieces_identite);
        $this->assertSame(0, Demande::count());
        $this->assertSame(1, User::count());
        $user = User::first();
        $this->assertTrue($user->isProvisionnel());
        $this->assertTrue($user->hasRole('pensionne'));
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertIsArray($demande->submitted_payload);
        $this->assertSame('+509 22 00 0000', $demande->submitted_payload['telephone'] ?? null);
        $this->assertArrayNotHasKey('password', $demande->submitted_payload);
        $this->assertTrue($demande->histories()->where('event', \App\Models\DemandeCreationCompteHistory::EVENT_SOUMISE)->exists());
        $this->assertTrue($demande->histories()->where('event', \App\Models\DemandeCreationCompteHistory::EVENT_COMPTE_CREE)->exists());
    }

    /** @test */
    public function guest_can_submit_a_pdf_without_a_nif(): void
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
        ])->assertRedirect(route('demandes.rencontre.create'))
            ->assertSessionMissing('success');

        $demande = DemandeCreationCompte::first();
        $this->assertNull($demande->nif);
        $this->assertSame('8-34321', $demande->pension_code);
        $this->assertTrue($demande->hasMedia('identite_cin'));
        $this->assertSame('cin.pdf', $demande->getFirstMedia('identite_cin')->file_name);
    }

    /** @test */
    public function pension_code_is_required_for_a_pensionne(): void
    {
        Storage::fake('public');

        $this->from(route('demandes.compte.create'))
            ->post(route('demandes.compte.store'), [
                'user_type' => 'pensionne',
                'telephone' => '+509 22 00 0000',
                'adresse' => '12, rue Capois, Port-au-Prince',
                'piece_identite_type' => 'cin',
                'piece_identite' => UploadedFile::fake()->image('cin.jpg', 800, 600),
                'password' => 'password123',
            'accept_terms' => '1',
            ])
            ->assertRedirect(route('demandes.compte.create'))
            ->assertSessionHasErrors('pension_code')
            ->assertSessionDoesntHaveErrors('nif');

        $this->assertDatabaseCount('demande_creation_comptes', 0);
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
        User::factory()->create([
            'nif' => '123-456-789-0',
            'pension_code' => '8-34321',
        ]);

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
        User::factory()->create([
            'nif' => '123-456-789-0',
            'pension_code' => '8-34321',
        ]);

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

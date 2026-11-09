<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\DemandeCreationCompte\StoreDemandeCreationCompteRequest;
use App\Models\DemandeCreationCompte;
use App\Models\PensionnaireReference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class StoreDemandeCreationCompteRequestTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPensionReference();
    }

    /** @test */
    public function rules_accept_a_valid_titulaire_payload(): void
    {
        $validator = $this->validatorFor($this->validTitulairePayload());

        $this->assertFalse($validator->fails(), implode(', ', $validator->errors()->all()));
    }

    /** @test */
    public function rules_require_identity_fields_for_adult_pensionne(): void
    {
        $payload = $this->validTitulairePayload();
        unset($payload['piece_identite_type'], $payload['piece_identite']);

        $validator = $this->validatorFor($payload);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('piece_identite_type', $validator->errors()->toArray());
        $this->assertArrayHasKey('piece_identite', $validator->errors()->toArray());
    }

    /** @test */
    public function rules_allow_nullable_nif_for_mineur(): void
    {
        $validator = $this->validatorFor([
            'user_type' => 'pensionne',
            'telephone' => '+50938123456',
            'adresse' => 'Port-au-Prince',
            'is_mineur' => '1',
            'acte_naissance' => UploadedFile::fake()->image('naissance.jpg'),
            'representant_lien' => 'mere',
            'piece_identite_representant_type' => 'cin',
            'piece_identite_representant' => UploadedFile::fake()->image('rep.jpg'),
            'password' => 'password123',
            'accept_terms' => '1',
        ]);

        $this->assertFalse($validator->errors()->has('nif'), implode(', ', $validator->errors()->get('nif')));
    }

    /** @test */
    public function rules_require_mandat_designation_for_mandataire(): void
    {
        $validator = $this->validatorFor([
            'user_type' => 'pensionne',
            'telephone' => '+50938123456',
            'nif' => '123-456-789-1',
            'adresse' => 'Port-au-Prince',
            'is_mandataire' => '1',
            'piece_identite_type' => 'passeport',
            'piece_identite' => UploadedFile::fake()->image('passeport.jpg'),
            'password' => 'password123',
            'accept_terms' => '1',
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('mandat_designation', $validator->errors()->toArray());
    }

    /** @test */
    public function rules_reject_invalid_ninu_format(): void
    {
        $validator = $this->validatorFor($this->validTitulairePayload([
            'ninu' => '123-456-789-0',
        ]));

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('ninu', $validator->errors()->toArray());
    }

    /** @test */
    public function rules_reject_ninu_already_used_on_existing_user(): void
    {
        User::factory()->create(['ninu' => '9876543210']);

        $validator = $this->validatorFor($this->validTitulairePayload([
            'ninu' => '9876543210',
            'nif' => '123-456-789-3',
        ]));

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('ninu', $validator->errors()->toArray());
    }

    /** @test */
    public function rules_reject_ninu_already_used_on_pending_demande(): void
    {
        DemandeCreationCompte::create([
            'code' => 'DCC-PENDING-NINU',
            'user_type' => 'pensionne',
            'ninu' => '5555555555',
            'nif' => '123-456-789-9',
            'telephone' => '+50922000002',
            'adresse' => 'PAP',
            'status' => DemandeCreationCompte::STATUS_EN_ATTENTE,
        ]);

        $validator = $this->validatorFor($this->validTitulairePayload([
            'ninu' => '5555555555',
            'nif' => '123-456-789-4',
            'telephone' => '+50938123499',
        ]));

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('ninu', $validator->errors()->toArray());
    }

    /** @test */
    public function rules_reject_telephone_already_used_on_existing_user(): void
    {
        User::factory()->create(['phone' => '+50938111111']);

        $validator = $this->validatorFor($this->validTitulairePayload([
            'telephone' => '+509 38 11 11 11',
            'nif' => '123-456-789-5',
        ]));

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('telephone', $validator->errors()->toArray());
    }

    /** @test */
    public function rules_reject_telephone_already_used_on_pending_demande(): void
    {
        DemandeCreationCompte::create([
            'code' => 'DCC-PENDING-TEL',
            'user_type' => 'pensionne',
            'nif' => '123-456-789-6',
            'telephone' => '+50922000003',
            'adresse' => 'PAP',
            'status' => DemandeCreationCompte::STATUS_EN_ATTENTE,
        ]);

        $validator = $this->validatorFor($this->validTitulairePayload([
            'telephone' => '+509 22 00 00 03',
            'nif' => '123-456-789-7',
        ]));

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('telephone', $validator->errors()->toArray());
    }

    /** @test */
    public function rules_reject_nif_already_used_on_pending_demande(): void
    {
        DemandeCreationCompte::create([
            'code' => 'DCC-PENDING-NIF',
            'user_type' => 'pensionne',
            'nif' => '123-456-789-0',
            'telephone' => '+50922000001',
            'adresse' => 'PAP',
            'status' => DemandeCreationCompte::STATUS_EN_ATTENTE,
        ]);

        $validator = $this->validatorFor($this->validTitulairePayload([
            'nif' => '123-456-789-0',
            'telephone' => '+50938123499',
        ]));

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('nif', $validator->errors()->toArray());
    }

    /** @test */
    public function rules_reject_proxy_titulaire_pension_code_for_mandataire_with_matching_ocr(): void
    {
        PensionnaireReference::query()->updateOrCreate(
            ['PENSIONNAIRE_ID' => '8-35001'],
            ['NOM' => 'MARTIN', 'PRENOM' => 'PAUL']
        );

        $validator = $this->validatorFor([
            'user_type' => 'pensionne',
            'telephone' => '+50938123456',
            'nif' => '123-456-789-2',
            'pension_code' => '8-35001',
            'adresse' => 'Port-au-Prince',
            'is_mandataire' => '1',
            'mandataire_est_pensionne' => '1',
            'piece_identite_type' => 'passeport',
            'piece_identite' => UploadedFile::fake()->image('passeport.jpg'),
            'mandat_designation' => UploadedFile::fake()->create('mandat.pdf', 10, 'application/pdf'),
            'password' => 'password123',
            'accept_terms' => '1',
            'ocr_documents_json' => json_encode([
                [
                    'key' => 'piece_identite',
                    'fields' => ['nom' => 'MARTIN', 'prenom' => 'PAUL'],
                ],
            ]),
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('pension_code', $validator->errors()->toArray());
        $this->assertStringContainsString(
            'titulaire',
            (string) $validator->errors()->first('pension_code')
        );
    }

    /** @test */
    public function expects_demande_json_response_when_idempotency_key_is_present(): void
    {
        $request = StoreDemandeCreationCompteRequest::createFrom(
            Request::create(route('demandes.compte.store'), 'POST', [
                'idempotency_key' => 'abc-123',
            ])
        );
        $request->setContainer($this->app);

        $this->assertTrue($request->expectsDemandeJsonResponse());
    }

    protected function seedPensionReference(
        string $code = '8-34321',
        string $nom = 'PIERRE',
        string $prenom = 'JEAN'
    ): void {
        PensionnaireReference::query()->updateOrCreate(
            ['PENSIONNAIRE_ID' => strtoupper(trim($code))],
            ['NOM' => $nom, 'PRENOM' => $prenom]
        );
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validTitulairePayload(array $overrides = []): array
    {
        return array_merge([
            'user_type' => 'pensionne',
            'telephone' => '+50938123456',
            'nif' => '123-456-789-0',
            'pension_code' => '8-34321',
            'adresse' => '12, rue Capois, Port-au-Prince',
            'piece_identite_type' => 'cin',
            'piece_identite' => UploadedFile::fake()->image('cin.jpg'),
            'password' => 'password123',
            'accept_terms' => '1',
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function validatorFor(array $data): \Illuminate\Validation\Validator
    {
        $request = StoreDemandeCreationCompteRequest::createFrom(
            Request::create(route('demandes.compte.store'), 'POST', $data)
        );
        $request->setContainer($this->app);

        return Validator::make(
            $data,
            $request->rules(),
            $request->messages(),
            $request->attributes()
        );
    }
}

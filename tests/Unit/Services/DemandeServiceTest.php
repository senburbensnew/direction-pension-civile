<?php

namespace Tests\Unit\Services;

use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\WorkflowStep;
use App\Models\User;
use App\Services\DemandeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class DemandeServiceTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    private DemandeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedStatuses();
        $this->service = new DemandeService();
        Storage::fake('public');
    }

    private function makeDemande(string $type = TypeDemandeEnum::DEMANDE_ATTESTATION->value): Demande
    {
        $user = User::factory()->create();
        return Demande::create(['type' => $type, 'created_by' => $user->id]);
    }

    // ─── validateDocumentsForSubmission ──────────────────────────────────────

    /** @test */
    public function validateDocuments_returns_empty_for_types_with_no_doc_config(): void
    {
        $demande = $this->makeDemande(TypeDemandeEnum::DEMANDE_ATTESTATION->value);

        $errors = $this->service->validateDocumentsForSubmission($demande);

        $this->assertEmpty($errors);
    }

    /** @test */
    public function validateDocuments_returns_error_when_required_doc_missing(): void
    {
        $demande = $this->makeDemande(TypeDemandeEnum::DEMANDE_ADHESION->value);

        $errors = $this->service->validateDocumentsForSubmission($demande);

        $this->assertNotEmpty($errors);
        $this->assertStringContainsStringIgnoringCase('photo', $errors[0]);
    }

    /** @test */
    public function validateDocuments_passes_when_required_doc_present(): void
    {
        $demande = $this->makeDemande(TypeDemandeEnum::DEMANDE_ADHESION->value);

        $file = UploadedFile::fake()->image('fake.jpg');
        $demande->addMedia($file)->usingFileName('fake.jpg')->toMediaCollection('profile_photo', 'public');

        $errors = $this->service->validateDocumentsForSubmission($demande);

        $this->assertEmpty($errors);
    }

    /** @test */
    public function isDemandeReadyForSubmission_returns_true_when_no_errors(): void
    {
        $demande = $this->makeDemande(TypeDemandeEnum::DEMANDE_ATTESTATION->value);

        $this->assertTrue($this->service->isDemandeReadyForSubmission($demande));
    }

    /** @test */
    public function isDemandeReadyForSubmission_returns_false_when_documents_missing(): void
    {
        $demande = $this->makeDemande(TypeDemandeEnum::DEMANDE_ADHESION->value);

        $this->assertFalse($this->service->isDemandeReadyForSubmission($demande));
    }

    // ─── storeFiles ──────────────────────────────────────────────────────────

    /** @test */
    public function storeFiles_persists_uploaded_file_as_media(): void
    {
        config(['demandes.disk' => 'public']);
        $service = new DemandeService();

        $demande = $this->makeDemande(TypeDemandeEnum::DEMANDE_ADHESION->value);
        $file    = UploadedFile::fake()->image('photo.jpg');

        $service->storeFiles($demande, ['profile_photo' => $file]);

        $this->assertCount(1, $demande->getMedia('profile_photo'));
    }

    /** @test */
    public function storeFiles_handles_array_of_files_for_same_field(): void
    {
        config(['demandes.disk' => 'public']);
        $service = new DemandeService();

        $demande = $this->makeDemande(TypeDemandeEnum::DEMANDE_ADHESION->value);
        $files   = [
            UploadedFile::fake()->image('photo1.jpg'),
            UploadedFile::fake()->image('photo2.jpg'),
        ];

        $service->storeFiles($demande, ['career_certificates' => $files]);

        $this->assertCount(2, $demande->getMedia('career_certificates'));
    }
}

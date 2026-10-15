<?php

namespace Tests\Feature;

use App\Models\Demande;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class DemandeMediaAccessTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflowSteps();
    }

    public function test_owner_can_stream_private_demande_media(): void
    {
        Storage::fake('private');

        $user = User::factory()->create();

        $demande = Demande::create([
            'type' => 'demande_attestation',
            'created_by' => $user->id,
        ]);

        $demande->addMedia(UploadedFile::fake()->create('piece.pdf', 50, 'application/pdf'))
            ->toMediaCollection('supplemental');

        /** @var Media $media */
        $media = $demande->getFirstMedia('supplemental');

        $this->actingAs($user)
            ->get(route('demande.media.show', ['demande' => $demande, 'media' => $media, 'inline' => 1]))
            ->assertOk();
    }

    public function test_stranger_cannot_stream_demande_media(): void
    {
        Storage::fake('private');

        $owner = User::factory()->create();
        $other = User::factory()->create();

        $demande = Demande::create([
            'type' => 'demande_attestation',
            'created_by' => $owner->id,
        ]);

        $demande->addMedia(UploadedFile::fake()->create('piece.pdf', 50, 'application/pdf'))
            ->toMediaCollection('supplemental');

        $media = $demande->getFirstMedia('supplemental');

        $this->actingAs($other)
            ->get(route('demande.media.show', ['demande' => $demande, 'media' => $media]))
            ->assertForbidden();
    }
}

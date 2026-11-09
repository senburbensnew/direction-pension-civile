<?php

namespace Tests\Unit\Support;

use App\Models\Demande;
use App\Support\Storage\StructuredMediaPathGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

class StructuredMediaPathGeneratorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_builds_path_from_uploads_media_folder_map(): void
    {
        $demande = Demande::create([
            'type' => 'demande_attestation',
            'created_by' => null,
        ]);

        $media = new Media([
            'model_type' => Demande::class,
            'model_id' => $demande->id,
            'collection_name' => 'documents',
        ]);
        $media->id = 99;

        $generator = new StructuredMediaPathGenerator;
        $path = $this->invokeGetBasePath($generator, $media);

        $this->assertSame(
            "media/demandes/{$demande->id}/documents/99",
            $path
        );
    }

    private function invokeGetBasePath(StructuredMediaPathGenerator $generator, Media $media): string
    {
        $method = new \ReflectionMethod($generator, 'getBasePath');
        $method->setAccessible(true);

        return $method->invoke($generator, $media);
    }
}

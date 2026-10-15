<?php

namespace Tests\Unit\Services\OCR;

use App\Services\OCR\Google\GoogleCloudAuthenticator;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleCloudAuthenticatorTest extends TestCase
{
    /** @test */
    public function it_resolves_legacy_credentials_path_to_storage_secrets(): void
    {
        $secretsPath = storage_path('app/secrets/ocr-dpc-sa.json');

        if (! is_file($secretsPath)) {
            $this->markTestSkipped('Fichier ocr-dpc-sa.json absent (normal en CI).');
        }

        config([
            'ocr.google.api_key' => null,
            'ocr.google.access_token' => null,
            'ocr.google.credentials' => 'storage/app/ocr-dpc-sa.json',
        ]);

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'test-token',
            ]),
        ]);

        $auth = app(GoogleCloudAuthenticator::class)->credentials();

        $this->assertSame('Bearer test-token', $auth['headers']['Authorization'] ?? '');
    }
}

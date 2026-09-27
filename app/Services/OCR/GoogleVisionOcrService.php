<?php

namespace App\Services\OCR;

use App\Services\OCR\Concerns\ResolvesOcrDocument;
use App\Services\OCR\Exceptions\OcrException;
use App\Services\OCR\Google\GoogleCloudAuthenticator;
use App\Services\OCR\Google\GoogleCloudHttp;

class GoogleVisionOcrService implements OcrService
{
    use ResolvesOcrDocument;

    public function __construct(private GoogleCloudAuthenticator $authenticator)
    {
    }

    public function extract(mixed $document): string
    {
        $path = $this->resolvePath($document);
        $this->assertReadableFile($path);

        if ($this->isPdf($path)) {
            throw new OcrException(
                'Cloud Vision traite les images. Pour un PDF, utilisez OCR_DRIVER=google_document_ai.'
            );
        }

        $auth = $this->authenticator->credentials();
        $endpoint = rtrim((string) config('ocr.google.vision.endpoint', 'https://vision.googleapis.com/v1/images:annotate'), '/');

        $response = GoogleCloudHttp::wrap(function () use ($auth, $endpoint, $path) {
            $request = GoogleCloudHttp::client()->withHeaders($auth['headers']);

            if ($auth['query'] !== []) {
                $request = $request->withQueryParameters($auth['query']);
            }

            return $request->post($endpoint, [
                'requests' => [[
                    'image' => [
                        'content' => base64_encode((string) file_get_contents($path)),
                    ],
                    'features' => [[
                        'type' => config('ocr.google.vision.feature', 'DOCUMENT_TEXT_DETECTION'),
                    ]],
                ]],
            ]);
        });

        if (! $response->successful()) {
            throw new OcrException(
                $response->json('error.message') ?? 'Échec de l\'OCR Google Cloud Vision.'
            );
        }

        $visionError = $response->json('responses.0.error.message');
        if (is_string($visionError) && $visionError !== '') {
            throw new OcrException($visionError);
        }

        $text = $response->json('responses.0.fullTextAnnotation.text')
            ?? $response->json('responses.0.textAnnotations.0.description');

        if (! is_string($text) || trim($text) === '') {
            throw new OcrException('Google Cloud Vision n\'a extrait aucun texte.');
        }

        return trim($text);
    }
}

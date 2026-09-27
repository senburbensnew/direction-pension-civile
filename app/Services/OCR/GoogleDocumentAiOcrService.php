<?php

namespace App\Services\OCR;

use App\Services\OCR\Concerns\ResolvesOcrDocument;
use App\Services\OCR\Exceptions\OcrException;
use App\Services\OCR\Google\GoogleCloudAuthenticator;
use App\Services\OCR\Google\GoogleCloudHttp;

class GoogleDocumentAiOcrService implements OcrService
{
    use ResolvesOcrDocument;

    public function __construct(private GoogleCloudAuthenticator $authenticator)
    {
    }

    public function extract(mixed $document): string
    {
        return $this->extractStructured($document)['text'];
    }

    /**
     * @return array{text: string, fields: array<string, string>}
     */
    public function extractStructured(mixed $document): array
    {
        $path = $this->resolvePath($document);
        $this->assertReadableFile($path);

        $project = (string) config('ocr.google.document_ai.project_id');
        $location = (string) config('ocr.google.document_ai.location', 'us');
        $processor = (string) config('ocr.google.document_ai.processor_id');

        if ($project === '' || $processor === '') {
            throw new OcrException(
                'Document AI n\'est pas configuré. Définissez OCR_GOOGLE_PROJECT_ID et OCR_GOOGLE_PROCESSOR_ID.'
            );
        }

        $auth = $this->authenticator->credentials();
        $base = config('ocr.google.document_ai.endpoint') ?: "https://{$location}-documentai.googleapis.com";
        $endpoint = sprintf(
            '%s/v1/projects/%s/locations/%s/processors/%s:process',
            rtrim((string) $base, '/'),
            $project,
            $location,
            $processor
        );

        $response = GoogleCloudHttp::wrap(function () use ($auth, $endpoint, $path) {
            $request = GoogleCloudHttp::client()->withHeaders($auth['headers']);

            if ($auth['query'] !== []) {
                $request = $request->withQueryParameters($auth['query']);
            }

            return $request->post($endpoint, [
                'rawDocument' => [
                    'content' => base64_encode((string) file_get_contents($path)),
                    'mimeType' => $this->mimeType($path),
                ],
            ]);
        });

        if (! $response->successful()) {
            throw new OcrException(
                $response->json('error.message') ?? 'Échec de l\'OCR Google Document AI.'
            );
        }

        $payload = $response->json();
        $text = is_array($payload) ? ($payload['document']['text'] ?? null) : null;
        if (! is_string($text) || trim($text) === '') {
            throw new OcrException('Google Document AI n\'a extrait aucun texte.');
        }

        return [
            'text' => trim($text),
            'fields' => is_array($payload) ? $this->formFieldsFromDocument($payload, $text) : [],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, string>
     */
    private function formFieldsFromDocument(array $payload, string $fullText): array
    {
        $fields = [];

        foreach ($payload['document']['pages'] ?? [] as $page) {
            if (! is_array($page)) {
                continue;
            }

            foreach ($page['formFields'] ?? [] as $field) {
                if (! is_array($field)) {
                    continue;
                }

                $name = $this->layoutText($field['fieldName'] ?? [], $fullText);
                $value = $this->layoutText($field['fieldValue'] ?? [], $fullText);

                if ($name !== '' && $value !== '') {
                    $fields[$name] = $value;
                }
            }
        }

        foreach ($payload['document']['entities'] ?? [] as $entity) {
            if (! is_array($entity)) {
                continue;
            }

            $name = trim((string) ($entity['type'] ?? $entity['mentionText'] ?? ''));
            $value = trim((string) ($entity['mentionText'] ?? $entity['normalizedValue']['text'] ?? ''));

            if ($name !== '' && $value !== '' && $name !== $value && ! isset($fields[$name])) {
                $fields[$name] = $value;
            }
        }

        return $fields;
    }

    /**
     * @param  mixed  $layout
     */
    private function layoutText(mixed $layout, string $fullText): string
    {
        if (! is_array($layout)) {
            return '';
        }

        if (isset($layout['mentionText']) && is_string($layout['mentionText']) && trim($layout['mentionText']) !== '') {
            return trim(preg_replace('/\s+/', ' ', $layout['mentionText']) ?? $layout['mentionText']);
        }

        $out = '';
        foreach ($layout['textAnchor']['textSegments'] ?? [] as $segment) {
            if (! is_array($segment)) {
                continue;
            }

            $start = (int) ($segment['startIndex'] ?? 0);
            $end = (int) ($segment['endIndex'] ?? 0);

            if ($end > $start) {
                $out .= mb_substr($fullText, $start, $end - $start);
            }
        }

        return trim(preg_replace('/\s+/', ' ', $out) ?? $out);
    }
}

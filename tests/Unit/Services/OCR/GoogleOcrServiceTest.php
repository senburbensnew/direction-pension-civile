<?php

namespace Tests\Unit\Services\OCR;

use App\Services\OCR\Exceptions\OcrException;
use App\Services\OCR\GoogleDocumentAiOcrService;
use App\Services\OCR\GoogleVisionOcrService;
use App\Services\OCR\OcrService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleOcrServiceTest extends TestCase
{
    private string $imagePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->imagePath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'ocr-test.png';
        file_put_contents($this->imagePath, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        ));
    }

    protected function tearDown(): void
    {
        if (is_file($this->imagePath)) {
            unlink($this->imagePath);
        }

        parent::tearDown();
    }

    /** @test */
    public function google_driver_binds_cloud_vision(): void
    {
        config(['ocr.driver' => 'google']);

        $this->assertInstanceOf(GoogleVisionOcrService::class, app(OcrService::class));
    }

    /** @test */
    public function document_ai_driver_binds_document_ai(): void
    {
        config(['ocr.driver' => 'google_document_ai']);

        $this->assertInstanceOf(GoogleDocumentAiOcrService::class, app(OcrService::class));
    }

    /** @test */
    public function vision_extracts_text_from_the_google_response(): void
    {
        config(['ocr.google.api_key' => 'test-key']);

        Http::fake([
            'https://vision.googleapis.com/*' => Http::response([
                'responses' => [[
                    'fullTextAnnotation' => ['text' => "NOM : DUPONT\nNIF : 123-456-789-0"],
                ]],
            ]),
        ]);

        $text = app(GoogleVisionOcrService::class)->extract($this->imagePath);

        $this->assertStringContainsString('DUPONT', $text);
        $this->assertStringContainsString('123-456-789-0', $text);
    }

    /** @test */
    public function vision_rejects_pdf_files(): void
    {
        $pdf = sys_get_temp_dir().DIRECTORY_SEPARATOR.'ocr-test.pdf';
        file_put_contents($pdf, '%PDF-1.4');

        try {
            config(['ocr.google.api_key' => 'test-key']);
            $this->expectException(OcrException::class);
            app(GoogleVisionOcrService::class)->extract($pdf);
        } finally {
            if (is_file($pdf)) {
                unlink($pdf);
            }
        }
    }

    /** @test */
    public function document_ai_extracts_text_from_the_google_response(): void
    {
        config([
            'ocr.google.api_key' => 'test-key',
            'ocr.google.document_ai.project_id' => 'dpc-test',
            'ocr.google.document_ai.processor_id' => 'processor-1',
            'ocr.google.document_ai.location' => 'us',
        ]);

        Http::fake([
            'https://us-documentai.googleapis.com/*' => Http::response([
                'document' => ['text' => 'Certificat de carrière — Jean Pierre'],
            ]),
        ]);

        $text = app(GoogleDocumentAiOcrService::class)->extract($this->imagePath);

        $this->assertSame('Certificat de carrière — Jean Pierre', $text);
    }

    /** @test */
    public function document_ai_extracts_form_fields_as_key_values(): void
    {
        config([
            'ocr.google.api_key' => 'test-key',
            'ocr.google.document_ai.project_id' => 'dpc-test',
            'ocr.google.document_ai.processor_id' => 'processor-1',
            'ocr.google.document_ai.location' => 'us',
        ]);

        Http::fake([
            'https://us-documentai.googleapis.com/*' => Http::response([
                'document' => [
                    'text' => 'NOM DUPONT NIF 123-456-789-0',
                    'pages' => [[
                        'formFields' => [[
                            'fieldName' => ['mentionText' => 'NOM'],
                            'fieldValue' => ['mentionText' => 'DUPONT'],
                        ]],
                    ]],
                    'entities' => [[
                        'type' => 'nif',
                        'mentionText' => '123-456-789-0',
                    ]],
                ],
            ]),
        ]);

        $result = app(GoogleDocumentAiOcrService::class)->extractStructured($this->imagePath);

        $this->assertSame('NOM DUPONT NIF 123-456-789-0', $result['text']);
        $this->assertSame('DUPONT', $result['fields']['NOM']);
        $this->assertSame('123-456-789-0', $result['fields']['nif']);
    }

    /** @test */
    public function document_ai_requires_project_and_processor(): void
    {
        config([
            'ocr.google.api_key' => 'test-key',
            'ocr.google.document_ai.project_id' => null,
            'ocr.google.document_ai.processor_id' => null,
        ]);

        $this->expectException(OcrException::class);

        app(GoogleDocumentAiOcrService::class)->extract($this->imagePath);
    }
}

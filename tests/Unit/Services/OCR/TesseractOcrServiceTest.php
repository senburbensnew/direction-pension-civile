<?php

namespace Tests\Unit\Services\OCR;

use App\Services\OCR\Exceptions\OcrException;
use App\Services\OCR\OcrService;
use App\Services\OCR\TesseractOcrService;
use Tests\TestCase;

class TesseractOcrServiceTest extends TestCase
{
    /** @test */
    public function ocr_service_is_bound_to_tesseract_by_default(): void
    {
        $this->assertInstanceOf(TesseractOcrService::class, app(OcrService::class));
    }

    /** @test */
    public function it_rejects_a_missing_file(): void
    {
        $this->expectException(OcrException::class);

        app(OcrService::class)->extract('C:/tmp/fichier-ocr-inexistant.png');
    }

    /** @test */
    public function it_rejects_an_unsupported_document(): void
    {
        $this->expectException(OcrException::class);

        app(OcrService::class)->extract(123);
    }
}

<?php

namespace App\Services\OCR;

interface OcrService
{
    /**
     * Extract raw text from a document (path, uploaded file, or media).
     */
    public function extract(mixed $document): string;
}

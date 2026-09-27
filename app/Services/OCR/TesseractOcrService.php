<?php

namespace App\Services\OCR;

use App\Services\OCR\Concerns\ResolvesOcrDocument;
use App\Services\OCR\Exceptions\OcrException;
use Symfony\Component\Process\Process;

class TesseractOcrService implements OcrService
{
    use ResolvesOcrDocument;

    public function extract(mixed $document): string
    {
        $path = $this->resolvePath($document);
        $this->assertReadableFile($path);

        $process = new Process([
            config('ocr.tesseract.binary', 'tesseract'),
            $path,
            'stdout',
            '-l',
            config('ocr.tesseract.language', 'fra+eng'),
            '--psm',
            (string) config('ocr.tesseract.psm', 6),
        ]);

        $process->setTimeout((int) config('ocr.tesseract.timeout', 60));
        $process->run();

        if (! $process->isSuccessful()) {
            $error = trim($process->getErrorOutput() ?: $process->getOutput());

            throw new OcrException($error !== '' ? $error : "Échec de l'extraction OCR (Tesseract).");
        }

        return trim($process->getOutput());
    }
}

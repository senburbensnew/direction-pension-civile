<?php

namespace App\Services\OCR\Concerns;

use App\Services\OCR\Exceptions\OcrException;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait ResolvesOcrDocument
{
    private function resolvePath(mixed $document): string
    {
        if (is_string($document)) {
            return $document;
        }

        if ($document instanceof UploadedFile) {
            return (string) $document->getRealPath();
        }

        if ($document instanceof Media) {
            return $document->getPath();
        }

        if (is_object($document)) {
            if (method_exists($document, 'getPath')) {
                return (string) $document->getPath();
            }

            if (method_exists($document, 'getRealPath')) {
                return (string) $document->getRealPath();
            }

            foreach (['path', 'file_path', 'absolute_path'] as $property) {
                if (isset($document->{$property}) && is_string($document->{$property})) {
                    return $document->{$property};
                }
            }
        }

        if (is_array($document) && isset($document['path']) && is_string($document['path'])) {
            return $document['path'];
        }

        throw new OcrException('Document OCR non pris en charge : fournissez un chemin, un fichier ou un média.');
    }

    private function assertReadableFile(string $path): void
    {
        if (! is_file($path)) {
            throw new OcrException("Fichier introuvable pour l'OCR : {$path}");
        }
    }

    private function mimeType(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'tif', 'tiff' => 'image/tiff',
            default => mime_content_type($path) ?: 'application/octet-stream',
        };
    }

    private function isPdf(string $path): bool
    {
        return $this->mimeType($path) === 'application/pdf';
    }
}

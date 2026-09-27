<?php

namespace App\Services\AccountCreation;

use App\Enums\TypeDemandeEnum;
use App\Services\OCR\DocumentExtractor;
use App\Services\OCR\Exceptions\OcrException;
use App\Services\OCR\GoogleDocumentAiOcrService;
use App\Services\OCR\OcrService;
use Illuminate\Http\UploadedFile;

class IdentityVerificationService
{
    public function __construct(
        private OcrService $ocr,
        private DocumentExtractor $extractor,
    ) {
    }

    /**
     * @return array{text: string, fields: array<string, string>, error: bool}
     */
    public function extract(UploadedFile $document, ?string $declaredDocumentType = null): array
    {
        $ocrText = '';
        $aiFields = [];
        $error = false;

        try {
            if ($this->ocr instanceof GoogleDocumentAiOcrService) {
                $structured = $this->ocr->extractStructured($document);
                $ocrText = trim($structured['text'] ?? '');
                $aiFields = $structured['fields'] ?? [];
            } else {
                $ocrText = trim($this->ocr->extract($document));
            }
        } catch (OcrException) {
            $error = true;
        }

        if ($ocrText === '') {
            $error = true;
        }

        $fields = $ocrText !== ''
            ? $this->extractor->extractKeyValues(
                $ocrText,
                TypeDemandeEnum::DEMANDE_CREATION_COMPTE,
                $aiFields,
                $declaredDocumentType,
            )
            : [];

        if ($ocrText !== '') {
            foreach ($this->extractor->extractFields($ocrText, TypeDemandeEnum::DEMANDE_CREATION_COMPTE) as $key => $value) {
                if ($value !== '') {
                    $fields[$key] = $value;
                }
            }
        }

        if ($declaredDocumentType && ! isset($fields['type_document'])
            && in_array($declaredDocumentType, ['cin', 'passeport', 'permis', 'acte_naissance'], true)) {
            $fields['type_document'] = $declaredDocumentType;
        }

        return [
            'text' => $ocrText,
            'fields' => $fields,
            'error' => $error,
        ];
    }
}

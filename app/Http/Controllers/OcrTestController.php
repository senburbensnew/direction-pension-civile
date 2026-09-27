<?php

namespace App\Http\Controllers;

use App\Enums\TypeDemandeEnum;
use App\Services\OCR\DocumentExtractor;
use App\Services\OCR\Exceptions\OcrException;
use App\Services\OCR\GoogleDocumentAiOcrService;
use App\Services\OCR\OcrService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OcrTestController extends Controller
{
    public function __construct(
        private OcrService $ocr,
        private DocumentExtractor $extractor,
    ) {
    }

    public function create(): View
    {
        return view('admin.ocr.test', [
            'driver' => config('ocr.driver'),
            'text' => null,
            'fields' => [],
            'fieldsJson' => null,
            'error' => null,
        ]);
    }

    public function store(Request $request): View
    {
        $request->validate([
            'document' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ], [
            'document.required' => 'Choisissez un fichier à analyser.',
            'document.mimes' => 'Formats acceptés : JPG, PNG, WEBP ou PDF.',
        ]);

        $text = '';
        $fields = [];
        $error = null;

        try {
            $aiFields = [];

            if ($this->ocr instanceof GoogleDocumentAiOcrService) {
                $structured = $this->ocr->extractStructured($request->file('document'));
                $text = $structured['text'];
                $aiFields = $structured['fields'];
            } else {
                $text = $this->ocr->extract($request->file('document'));
            }

            $fields = $this->extractor->extractKeyValues(
                $text,
                TypeDemandeEnum::DEMANDE_CREATION_COMPTE,
                $aiFields
            );
        } catch (OcrException $e) {
            $error = $e->getMessage();
        }

        return view('admin.ocr.test', [
            'driver' => config('ocr.driver'),
            'text' => $text,
            'fields' => $fields,
            'fieldsJson' => $fields === []
                ? null
                : json_encode($fields, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'error' => $error,
        ]);
    }
}

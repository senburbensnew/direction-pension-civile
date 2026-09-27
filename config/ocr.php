<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OCR driver
    |--------------------------------------------------------------------------
    |
    | The bound OcrService implementation. Swap the driver (or bind another
    | class in AppServiceProvider) without changing controllers.
    |
    */

    'driver' => env('OCR_DRIVER', 'google_document_ai'),

    'tesseract' => [
        'binary' => env('OCR_TESSERACT_BINARY', 'tesseract'),
        'language' => env('OCR_LANGUAGE', 'fra+eng'),
        'psm' => (int) env('OCR_TESSERACT_PSM', 6),
        'timeout' => (int) env('OCR_TIMEOUT', 60),
    ],

    'google' => [
        'api_key' => env('OCR_GOOGLE_API_KEY'),
        'access_token' => env('OCR_GOOGLE_ACCESS_TOKEN'),
        'credentials' => env('OCR_GOOGLE_CREDENTIALS'),
        'timeout' => (int) env('OCR_TIMEOUT', 60),
        'verify_ssl' => env('OCR_GOOGLE_VERIFY_SSL', true),
        'ca_bundle' => env('OCR_GOOGLE_CA_BUNDLE', storage_path('certs/cacert.pem')),

        'vision' => [
            'endpoint' => env('OCR_GOOGLE_VISION_ENDPOINT', 'https://vision.googleapis.com/v1/images:annotate'),
            'feature' => env('OCR_GOOGLE_VISION_FEATURE', 'DOCUMENT_TEXT_DETECTION'),
        ],

        'document_ai' => [
            'project_id' => env('OCR_GOOGLE_PROJECT_ID'),
            'location' => env('OCR_GOOGLE_LOCATION', 'us'),
            'processor_id' => env('OCR_GOOGLE_PROCESSOR_ID'),
            'endpoint' => env('OCR_GOOGLE_DOCUMENT_AI_ENDPOINT') ?: null,
        ],
    ],

];

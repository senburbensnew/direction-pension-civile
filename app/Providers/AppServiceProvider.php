<?php

namespace App\Providers;

use App\Models\Demande;
use App\Notifications\Channels\TwilioSmsChannel;
use App\Observers\DemandeObserver;
use App\Services\OCR\GoogleDocumentAiOcrService;
use App\Services\OCR\GoogleVisionOcrService;
use App\Services\OCR\OcrService;
use App\Services\OCR\TesseractOcrService;
use App\Support\Storage\StructuredMediaPathGenerator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OcrService::class, function ($app) {
            return match (config('ocr.driver', 'tesseract')) {
                'google', 'google_vision' => $app->make(GoogleVisionOcrService::class),
                'google_document_ai', 'document_ai' => $app->make(GoogleDocumentAiOcrService::class),
                default => $app->make(TesseractOcrService::class),
            };
        });
    }

    public function boot(): void
    {
        config([
            'media-library.path_generator' => StructuredMediaPathGenerator::class,
        ]);

        Notification::extend('twilio', fn () => $this->app->make(TwilioSmsChannel::class));

        Demande::observe(DemandeObserver::class);

        Gate::after(function ($user, $ability) {
            if ($user->hasRole('admin')) {
                return true;
            }
        });
    }
}

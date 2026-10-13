<?php

namespace App\Http\Controllers\Concerns;

use App\Services\ImageStorageService;
use Illuminate\Http\UploadedFile;

trait StoresOptimizedImages
{
    protected function storeOptimizedImage(UploadedFile $file, string $directory, string $configKey): string
    {
        return app(ImageStorageService::class)->storeUploadedImage(
            $file,
            $directory,
            config('media.upload_max_widths.'.$configKey),
        );
    }

    protected function deleteOptimizedImage(?string $storageRelative): void
    {
        if (! $storageRelative || str_starts_with($storageRelative, 'images/')) {
            return;
        }

        app(ImageStorageService::class)->deleteWithVariants($storageRelative);
    }
}

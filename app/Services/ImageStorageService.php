<?php

namespace App\Services;

use App\Support\PublicMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Image;

class ImageStorageService
{
    public function storeUploadedImage(
        UploadedFile $file,
        string $directory,
        ?int $maxWidth = null,
        ?int $quality = null,
    ): string {
        if ($this->shouldStoreAsIs($file)) {
            $directory = trim(str_replace('\\', '/', $directory), '/');
            Storage::disk('public')->makeDirectory($directory);

            return $file->store($directory, 'public');
        }

        return $this->storeOptimized($file, $directory, $maxWidth, $quality);
    }

    private function shouldStoreAsIs(UploadedFile $file): bool
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mime = strtolower($file->getMimeType() ?? '');

        return $extension === 'svg' || str_contains($mime, 'svg');
    }

    public function storeOptimized(
        UploadedFile $file,
        string $directory,
        ?int $maxWidth = null,
        ?int $quality = null,
    ): string {
        $maxWidth = $maxWidth ?? 1920;
        $quality = $quality ?? (int) config('media.quality', 82);

        $directory = trim(str_replace('\\', '/', $directory), '/');
        $basename = Str::uuid()->toString();
        $relative = $directory.'/'.$basename.'.jpg';
        $disk = Storage::disk('public');
        $disk->makeDirectory($directory);

        $absolute = $disk->path($relative);

        Image::load($file->getRealPath())
            ->orientation()
            ->fit(Fit::Max, $maxWidth, $maxWidth * 2)
            ->quality($quality)
            ->save($absolute);

        $this->writeDerivatives($absolute, $quality);

        return $relative;
    }

    public function writeDerivatives(string $sourceAbsolute, ?int $quality = null): void
    {
        $quality = $quality ?? (int) config('media.quality', 82);
        $this->writeWebpSibling($sourceAbsolute, $quality);
        $this->writeResponsiveVariants($sourceAbsolute, $quality);
    }

    public function writeWebpSibling(string $sourceAbsolute, int $quality = 82): void
    {
        if (! function_exists('imagewebp')) {
            return;
        }

        $webpAbsolute = preg_replace('/\.(jpe?g|png|gif)$/i', '.webp', $sourceAbsolute);
        if (! $webpAbsolute || $webpAbsolute === $sourceAbsolute) {
            return;
        }

        Image::load($sourceAbsolute)
            ->quality($quality)
            ->format('webp')
            ->save($webpAbsolute);
    }

    public function writeResponsiveVariants(string $sourceAbsolute, int $quality = 82): void
    {
        $widths = config('media.responsive_widths', [480, 960, 1600]);
        $originalWidth = @getimagesize($sourceAbsolute)[0] ?? 0;
        if ($originalWidth <= 0) {
            return;
        }

        $dir = dirname($sourceAbsolute);
        $filename = pathinfo($sourceAbsolute, PATHINFO_FILENAME);
        $extension = strtolower(pathinfo($sourceAbsolute, PATHINFO_EXTENSION) ?: 'jpg');

        foreach ($widths as $targetWidth) {
            if ($targetWidth >= $originalWidth) {
                continue;
            }

            $variantAbsolute = $dir.DIRECTORY_SEPARATOR.$filename.'-w'.$targetWidth.'.'.$extension;

            Image::load($sourceAbsolute)
                ->fit(Fit::Max, $targetWidth, $targetWidth * 2)
                ->quality($quality)
                ->save($variantAbsolute);

            $this->writeWebpSibling($variantAbsolute, $quality);
        }
    }

    public function deleteWithVariants(string $storageRelative): void
    {
        $storageRelative = ltrim(str_replace('\\', '/', $storageRelative), '/');
        $disk = Storage::disk('public');

        if ($disk->exists($storageRelative)) {
            $absolute = $disk->path($storageRelative);
            $dir = dirname($absolute);
            $filename = pathinfo($absolute, PATHINFO_FILENAME);
            $extension = pathinfo($absolute, PATHINFO_EXTENSION);

            $disk->delete($storageRelative);
            PublicMedia::deleteWebpSibling($storageRelative);

            foreach (config('media.responsive_widths', [480, 960, 1600]) as $width) {
                $variant = dirname($storageRelative).'/'.$filename.'-w'.$width.'.'.$extension;
                $disk->delete($variant);
                PublicMedia::deleteWebpSibling($variant);
            }
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Services\ImageStorageService;
use Illuminate\Console\Command;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Image;
use Symfony\Component\Finder\Finder;

class OptimizePublicImagesCommand extends Command
{
    protected $signature = 'images:optimize-public
                            {--max-width=1600 : Max width for large hero-style images}
                            {--quality=82 : JPEG/WebP quality}
                            {--storage : Also optimize files on the public disk (e.g. carousel uploads)}';

    protected $description = 'Generate WebP variants and downscale oversized images in public/images';

    public function handle(ImageStorageService $images): int
    {
        if (! extension_loaded('gd')) {
            $this->error('The GD extension is required.');

            return self::FAILURE;
        }

        $maxWidth = (int) $this->option('max-width');
        $quality = (int) $this->option('quality');
        $root = public_path('images');

        if (! is_dir($root)) {
            $this->warn('public/images does not exist.');

            return self::SUCCESS;
        }

        $finder = (new Finder)
            ->files()
            ->in($root)
            ->name('*.jpg')
            ->name('*.jpeg')
            ->name('*.JPG')
            ->name('*.JPEG')
            ->name('*.png')
            ->name('*.PNG');

        $countWebp = 0;
        $countResize = 0;

        foreach ($finder as $file) {
            $absolute = $file->getRealPath();
            if (! $absolute || preg_match('/-w\d+\.(jpe?g|png)$/i', $absolute)) {
                continue;
            }

            $width = @getimagesize($absolute)[0] ?? 0;
            if ($width > $maxWidth) {
                Image::load($absolute)
                    ->orientation()
                    ->fit(Fit::Max, $maxWidth, $maxWidth * 2)
                    ->quality($quality)
                    ->save($absolute);
                $countResize++;
            }

            $images->writeDerivatives($absolute, $quality);
            $countWebp++;
        }

        if ($this->option('storage')) {
            [$r, $w] = $this->optimizeDirectory(storage_path('app/public'), $maxWidth, $quality, $images);
            $countResize += $r;
            $countWebp += $w;
        }

        $this->info("Resized {$countResize} file(s), generated/updated {$countWebp} WebP variant(s).");

        return self::SUCCESS;
    }

    /**
     * @return array{0: int, 1: int} resize count, webp count
     */
    private function optimizeDirectory(
        string $root,
        int $maxWidth,
        int $quality,
        ImageStorageService $images,
    ): array {
        if (! is_dir($root)) {
            return [0, 0];
        }

        $finder = (new Finder)
            ->files()
            ->in($root)
            ->name('*.jpg')
            ->name('*.jpeg')
            ->name('*.JPG')
            ->name('*.JPEG')
            ->name('*.png')
            ->name('*.PNG');

        $countResize = 0;
        $countWebp = 0;

        foreach ($finder as $file) {
            $absolute = $file->getRealPath();
            if (! $absolute || preg_match('/-w\d+\.(jpe?g|png)$/i', $absolute)) {
                continue;
            }

            $width = @getimagesize($absolute)[0] ?? 0;
            if ($width > $maxWidth) {
                Image::load($absolute)
                    ->orientation()
                    ->fit(Fit::Max, $maxWidth, $maxWidth * 2)
                    ->quality($quality)
                    ->save($absolute);
                $countResize++;
            }

            $images->writeDerivatives($absolute, $quality);
            $countWebp++;
        }

        return [$countResize, $countWebp];
    }
}

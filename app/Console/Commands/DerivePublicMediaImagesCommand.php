<?php

namespace App\Console\Commands;

use App\Services\ImageStorageService;
use Illuminate\Console\Command;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Image;
use Symfony\Component\Finder\Finder;

class DerivePublicMediaImagesCommand extends Command
{
    protected $signature = 'images:derive-public
                            {--max-width=1600 : Downscale sources wider than this before deriving}
                            {--quality= : JPEG/WebP quality (defaults to config media.quality)}';

    protected $description = 'Generate WebP, PNG (from JPEG), and responsive variants for CMS and public/images assets';

    public function handle(ImageStorageService $images): int
    {
        if (! extension_loaded('gd')) {
            $this->error('The GD extension is required.');

            return self::FAILURE;
        }

        $maxWidth = (int) $this->option('max-width');
        $quality = $this->option('quality') !== null
            ? (int) $this->option('quality')
            : (int) config('media.quality', 82);

        $roots = array_filter([
            storage_path('app/public'),
            public_path('images'),
        ], is_dir(...));

        $processed = 0;

        foreach ($roots as $root) {
            $processed += $this->processRoot($root, $maxWidth, $quality, $images);
        }

        $this->info("Processed {$processed} source image(s).");

        return self::SUCCESS;
    }

    private function processRoot(
        string $root,
        int $maxWidth,
        int $quality,
        ImageStorageService $images,
    ): int {
        $finder = (new Finder)
            ->files()
            ->in($root)
            ->name('*.jpg')
            ->name('*.jpeg')
            ->name('*.JPG')
            ->name('*.JPEG')
            ->name('*.png')
            ->name('*.PNG');

        $count = 0;

        foreach ($finder as $file) {
            $absolute = $file->getRealPath();
            if (! $absolute || $this->shouldSkip($absolute)) {
                continue;
            }

            $width = @getimagesize($absolute)[0] ?? 0;
            if ($width > $maxWidth) {
                Image::load($absolute)
                    ->orientation()
                    ->fit(Fit::Max, $maxWidth, $maxWidth * 2)
                    ->quality($quality)
                    ->save($absolute);
            }

            $images->writeDerivatives($absolute, $quality);
            $images->writePngSibling($absolute);
            $count++;
        }

        return $count;
    }

    private function shouldSkip(string $absolute): bool
    {
        if (preg_match('/-w\d+\.(jpe?g|png|webp)$/i', $absolute)) {
            return true;
        }

        return (bool) preg_match('/\.webp$/i', $absolute);
    }
}

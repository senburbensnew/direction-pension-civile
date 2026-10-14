<?php

namespace App\Support\Storage;

use App\Models\ActualiteImage;
use App\Models\Carousel;
use App\Models\InstitutionImage;
use App\Models\MediathequeItem;
use App\Models\Official;
use App\Models\Partenaire;
use App\Models\Publication;
use App\Models\Report;
use App\Models\User;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\Storage;

class LegacyPublicStorageMigrator
{
    /** @var array<string, string> ancien préfixe (storage public) → nouveau */
    private const PREFIX_MAP = [
        'mediatheque' => 'cms/mediatheque',
        'profile_photos' => 'users/profile-photos',
        'reports' => 'cms/reports',
        'publications' => 'cms/publications',
        'actualites' => 'cms/actualites',
        'carousel' => 'cms/carousel',
        'partenaires' => 'cms/partenaires',
        'officials' => 'cms/officials',
        'institution-images' => 'cms/institution',
    ];

    public function __construct(private ?OutputStyle $output = null) {}

    public function migrate(bool $dryRun): int
    {
        $moved = 0;

        foreach (self::PREFIX_MAP as $from => $to) {
            $moved += $this->moveTree(UploadPaths::publicDisk(), $from, $to, $dryRun);
        }

        $moved += $this->moveDemandeLegacyFolders($dryRun);
        $moved += $this->moveStorageMediaAudios($dryRun);
        $moved += $this->migratePublicDocumentsFolder($dryRun);
        $moved += $this->migratePublicImagesFolder($dryRun);
        $this->rewriteDatabasePaths($dryRun);
        $moved += $this->moveRootSecrets($dryRun);

        return $moved;
    }

    private function moveTree(string $diskName, string $fromPrefix, string $toPrefix, bool $dryRun): int
    {
        $disk = Storage::disk($diskName);

        if (! $disk->exists($fromPrefix)) {
            return 0;
        }

        $count = 0;

        foreach ($disk->allFiles($fromPrefix) as $relative) {
            $target = $this->remapPath($relative, $fromPrefix, $toPrefix);

            if ($target === $relative) {
                continue;
            }

            if ($disk->exists($target)) {
                if ($disk->size($relative) === $disk->size($target)) {
                    $this->line("  doublon supprimé : {$relative} (déjà sous {$target})");
                    if (! $dryRun) {
                        $disk->delete($relative);
                    }
                    $count++;
                } else {
                    $this->line("  conflit ignoré : {$relative} ≠ {$target} (tailles différentes)");
                }

                continue;
            }

            $this->line("  {$relative} → {$target}");

            if ($dryRun) {
                $count++;

                continue;
            }

            $disk->makeDirectory(dirname($target));
            $disk->move($relative, $target);
            $count++;
        }

        if (! $dryRun) {
            $this->removeEmptyDirectories($disk->path($fromPrefix));
        }

        return $count;
    }

    private function moveDemandeLegacyFolders(bool $dryRun): int
    {
        $disk = Storage::disk(UploadPaths::publicDisk());
        $count = 0;

        foreach ($disk->allFiles('demandes') as $relative) {
            if (str_starts_with($relative, 'demandes/embedded/')) {
                continue;
            }

            if (! preg_match('#^demandes/([^/]+)/(\d{4})/(\d{2})/#', $relative, $m)) {
                continue;
            }

            $target = str_replace(
                "demandes/{$m[1]}/{$m[2]}/{$m[3]}",
                UploadPaths::demandeEmbeddedPath($m[1]),
                $relative
            );

            if ($target === $relative || $disk->exists($target)) {
                continue;
            }

            $this->line("  {$relative} → {$target}");

            if (! $dryRun) {
                $disk->makeDirectory(dirname($target));
                $disk->move($relative, $target);
            }

            $count++;
        }

        return $count;
    }

    private function moveStorageMediaAudios(bool $dryRun): int
    {
        $disk = Storage::disk(UploadPaths::publicDisk());

        if (! $disk->exists('media/audios')) {
            return 0;
        }

        $count = 0;

        foreach ($disk->files('media/audios') as $relative) {
            $basename = basename($relative);
            $target = UploadPaths::mediatheque('audio').'/'.$basename;
            $rootCopy = UploadPaths::cms('mediatheque').'/'.$basename;

            if ($disk->exists($target)) {
                if ($disk->exists($relative)) {
                    $this->line("  supprimé (doublon) : {$relative}");
                    if (! $dryRun) {
                        $disk->delete($relative);
                    }
                    $count++;
                }

                continue;
            }

            if ($disk->exists($rootCopy)) {
                $this->line("  {$rootCopy} → {$target}");
                if (! $dryRun) {
                    $disk->makeDirectory(dirname($target));
                    $disk->move($rootCopy, $target);
                }
                $count++;

                continue;
            }

            $this->line("  {$relative} → {$target}");

            if (! $dryRun) {
                $disk->makeDirectory(dirname($target));
                $disk->move($relative, $target);
            }

            $count++;
        }

        return $count;
    }

    private function migratePublicDocumentsFolder(bool $dryRun): int
    {
        $sourceDir = public_path('documents');

        if (! is_dir($sourceDir)) {
            return 0;
        }

        $disk = Storage::disk(UploadPaths::publicDisk());
        $targetPrefix = UploadPaths::cms('publications');
        $count = 0;

        foreach (scandir($sourceDir) ?: [] as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $absolute = $sourceDir.DIRECTORY_SEPARATOR.$file;

            if (! is_file($absolute)) {
                continue;
            }

            $target = $targetPrefix.'/'.$file;

            if ($disk->exists($target)) {
                if (filesize($absolute) === $disk->size($target)) {
                    $this->line("  doublon supprimé : public/documents/{$file}");
                    if (! $dryRun) {
                        unlink($absolute);
                    }
                    $count++;
                } else {
                    $this->warn("  conflit ignoré : public/documents/{$file} ≠ storage/{$target}");
                }

                continue;
            }

            $this->line("  public/documents/{$file} → {$target}");

            if (! $dryRun) {
                $disk->makeDirectory($targetPrefix);
                $disk->put($target, file_get_contents($absolute));
                unlink($absolute);
            }

            $count++;
        }

        if (! $dryRun && is_dir($sourceDir) && $this->countFiles($sourceDir) === 0) {
            @rmdir($sourceDir);
        }

        return $count;
    }

    private function countFiles(string $directory): int
    {
        $count = 0;

        foreach (new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
        ) as $file) {
            if ($file->isFile()) {
                $count++;
            }
        }

        return $count;
    }

    private function rewriteDatabasePaths(bool $dryRun): void
    {
        foreach (self::PREFIX_MAP as $from => $to) {
            $this->rewriteColumn(Report::class, 'file_path', $from, $to, $dryRun);
            $this->rewriteColumn(Publication::class, 'file_path', $from, $to, $dryRun);
            $this->rewriteColumn(MediathequeItem::class, 'file_path', $from, $to, $dryRun);
            $this->rewriteColumn(Carousel::class, 'image', $from, $to, $dryRun);
            $this->rewriteColumn(ActualiteImage::class, 'image_path', $from, $to, $dryRun);
            $this->rewriteColumn(Partenaire::class, 'logo', $from, $to, $dryRun);
            $this->rewriteColumn(Official::class, 'photo', $from, $to, $dryRun);
            $this->rewriteColumn(InstitutionImage::class, 'image', $from, $to, $dryRun);
            $this->rewriteColumn(User::class, 'profile_photo', $from, $to, $dryRun);
        }

        MediathequeItem::query()
            ->where('file_path', 'like', 'media/audios/%')
            ->each(function (MediathequeItem $item) use ($dryRun) {
                $basename = basename($item->file_path);
                $newPath = UploadPaths::cms('mediatheque').'/audio/'.$basename;
                $this->line("  DB mediatheque #{$item->id} : {$item->file_path} → {$newPath}");
                if (! $dryRun) {
                    $item->update(['file_path' => $newPath]);
                }
            });

        MediathequeItem::query()
            ->where('file_path', 'like', UploadPaths::cms('mediatheque').'/images/%')
            ->each(function (MediathequeItem $item) use ($dryRun) {
                $basename = basename($item->file_path);
                $newPath = UploadPaths::mediatheque('image').'/'.$basename;
                $this->line("  DB mediatheque #{$item->id} : {$item->file_path} → {$newPath}");
                if (! $dryRun) {
                    $item->update(['file_path' => $newPath]);
                }
            });

        Publication::query()
            ->where('file_path', 'like', 'documents/%')
            ->each(function (Publication $publication) use ($dryRun) {
                $basename = basename($publication->file_path);
                $newPath = UploadPaths::cms('publications').'/'.$basename;
                $this->line("  DB publication #{$publication->id} : {$publication->file_path} → {$newPath}");
                if (! $dryRun) {
                    $publication->update(['file_path' => $newPath]);
                }
            });

        $this->rewriteLegacyImagePaths($dryRun);
    }

    private function migratePublicImagesFolder(bool $dryRun): int
    {
        $root = public_path('images');

        if (! is_dir($root)) {
            return 0;
        }

        $count = 0;
        $count += $this->migratePublicDirectoryTree($root.DIRECTORY_SEPARATOR.'carousel', UploadPaths::cms('carousel'), $dryRun);
        $count += $this->migratePublicDirectoryTree($root.DIRECTORY_SEPARATOR.'historique', UploadPaths::cms('institution').'/historique', $dryRun);

        foreach (scandir($root) ?: [] as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $absolute = $root.DIRECTORY_SEPARATOR.$file;

            if (is_dir($absolute)) {
                continue;
            }

            $target = CmsPathResolver::storageTargetForPublicImageFile($file);

            if ($target === null) {
                continue;
            }

            $count += $this->moveAbsoluteFileToPublicDisk($absolute, $target, 'public/images/'.$file, $dryRun);
        }

        if (! $dryRun) {
            foreach (['carousel', 'historique'] as $dir) {
                $path = $root.DIRECTORY_SEPARATOR.$dir;
                if (is_dir($path) && $this->countFiles($path) === 0) {
                    @rmdir($path);
                }
            }
        }

        return $count;
    }

    private function migratePublicDirectoryTree(string $sourceDir, string $targetPrefix, bool $dryRun): int
    {
        if (! is_dir($sourceDir)) {
            return 0;
        }

        $count = 0;

        foreach (new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \FilesystemIterator::SKIP_DOTS)
        ) as $fileInfo) {
            if (! $fileInfo->isFile()) {
                continue;
            }

            $relative = ltrim(str_replace('\\', '/', substr($fileInfo->getPathname(), strlen($sourceDir))), '/');
            $target = $targetPrefix.'/'.$relative;
            $label = 'public/images/'.basename(dirname($sourceDir)).'/'.$relative;

            $count += $this->moveAbsoluteFileToPublicDisk($fileInfo->getPathname(), $target, $label, $dryRun);
        }

        return $count;
    }

    private function moveAbsoluteFileToPublicDisk(string $absolute, string $target, string $label, bool $dryRun): int
    {
        $disk = Storage::disk(UploadPaths::publicDisk());

        if ($disk->exists($target)) {
            if (is_file($absolute) && filesize($absolute) === $disk->size($target)) {
                $this->line("  doublon supprimé : {$label}");
                if (! $dryRun) {
                    unlink($absolute);
                }

                return 1;
            }

            $this->warn("  conflit ignoré : {$label} ≠ {$target}");

            return 0;
        }

        $this->line("  {$label} → {$target}");

        if ($dryRun) {
            return 1;
        }

        $disk->makeDirectory(dirname(str_replace('\\', '/', $target)));
        $disk->put($target, file_get_contents($absolute));
        unlink($absolute);

        return 1;
    }

    private function rewriteLegacyImagePaths(bool $dryRun): void
    {
        $maps = [
            [Carousel::class, 'image'],
            [Official::class, 'photo'],
            [Partenaire::class, 'logo'],
            [Report::class, 'cover_path'],
            [ActualiteImage::class, 'image_path'],
            [InstitutionImage::class, 'image'],
        ];

        foreach ($maps as [$modelClass, $column]) {
            $modelClass::query()
                ->whereNotNull($column)
                ->where($column, '!=', '')
                ->each(function ($row) use ($column, $dryRun, $modelClass) {
                    $old = $row->{$column};
                    $new = CmsPathResolver::legacyPathToStorage($old);

                    if ($new === null || $new === $old || str_starts_with($new, 'images/')) {
                        return;
                    }

                    $this->line('  DB '.class_basename($modelClass)." #{$row->id} : {$old} → {$new}");

                    if (! $dryRun) {
                        $row->update([$column => $new]);
                    }
                });
        }
    }

    private function rewriteColumn(string $model, string $column, string $from, string $to, bool $dryRun): void
    {
        $fromPrefix = $from.'/';

        $model::query()
            ->where($column, 'like', $fromPrefix.'%')
            ->each(function ($row) use ($column, $from, $to, $dryRun, $model) {
                $old = $row->{$column};
                $new = $this->remapPath($old, $from, $to);
                if ($new === $old) {
                    return;
                }
                $this->line('  DB '.class_basename($model)." #{$row->id} : {$old} → {$new}");
                if (! $dryRun) {
                    $row->update([$column => $new]);
                }
            });
    }

    private function remapPath(string $path, string $fromPrefix, string $toPrefix): string
    {
        if ($path === $fromPrefix) {
            return $toPrefix;
        }

        if (str_starts_with($path, $fromPrefix.'/')) {
            return $toPrefix.substr($path, strlen($fromPrefix));
        }

        return $path;
    }

    private function moveRootSecrets(bool $dryRun): int
    {
        $root = storage_path('app');
        $candidates = ['ocr-dpc-sa.json', 'google-credentials.json'];

        $count = 0;

        foreach ($candidates as $name) {
            $source = $root.DIRECTORY_SEPARATOR.$name;
            if (! is_file($source)) {
                continue;
            }

            $targetDir = $root.DIRECTORY_SEPARATOR.'secrets';
            $target = $targetDir.DIRECTORY_SEPARATOR.$name;

            $this->line("  secrets: {$name} → secrets/{$name}");

            if ($dryRun) {
                $count++;

                continue;
            }

            if (! is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            if (! is_file($target)) {
                rename($source, $target);
            } else {
                unlink($source);
            }

            $count++;
        }

        return $count;
    }

    private function removeEmptyDirectories(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        foreach (scandir($directory) ?: [] as $entry) {
            if (in_array($entry, ['.', '..'], true)) {
                continue;
            }

            $path = $directory.DIRECTORY_SEPARATOR.$entry;
            if (is_dir($path)) {
                $this->removeEmptyDirectories($path);
            }
        }

        if (count(scandir($directory) ?: []) === 2) {
            @rmdir($directory);
        }
    }

    private function line(string $message): void
    {
        $this->output?->writeln($message);
    }
}

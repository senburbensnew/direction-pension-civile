<?php

namespace App\Console\Commands;

use App\Models\MediathequeItem;
use App\Support\Storage\UploadPaths;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class EnsureUploadStructureCommand extends Command
{
    protected $signature = 'uploads:ensure-structure
                            {--normalize-mediatheque : Renommer cms/mediatheque/images → image}';

    protected $description = 'Crée l’arborescence canonique storage/ (cms, users, private/temp, media)';

    /** @return list<string> */
    private function canonicalPublicDirectories(): array
    {
        $dirs = [];

        foreach (config('uploads.paths.cms', []) as $cmsPath) {
            $dirs[] = $cmsPath;
        }

        foreach (['image', 'video', 'audio', 'document'] as $type) {
            $dirs[] = UploadPaths::mediatheque($type);
        }

        $dirs[] = UploadPaths::cms('institution').'/historique';
        $dirs[] = UploadPaths::userProfilePhoto();

        return array_values(array_unique($dirs));
    }

    /** @return list<string> */
    private function canonicalPrivateDirectories(): array
    {
        $dirs = [
            config('uploads.paths.temp.form_stash'),
            config('uploads.paths.secrets'),
            'demandes/embedded',
        ];

        foreach (array_values(config('uploads.media_folders', [])) as $folder) {
            if (is_string($folder) && $folder !== '') {
                $dirs[] = 'media/'.$folder;
            }
        }

        return array_values(array_unique($dirs));
    }

    public function handle(): int
    {
        $publicDisk = Storage::disk(UploadPaths::publicDisk());
        $privateDisk = Storage::disk(UploadPaths::privateDisk());
        $created = 0;

        foreach ($this->canonicalPublicDirectories() as $relative) {
            if ($this->ensureDirectory($publicDisk, $relative)) {
                $this->line("  public: {$relative}/");
                $created++;
            }
        }

        foreach ($this->canonicalPrivateDirectories() as $relative) {
            if ($this->ensureDirectory($privateDisk, $relative)) {
                $this->line("  private: {$relative}/");
                $created++;
            }
        }

        if ($this->option('normalize-mediatheque')) {
            $created += $this->normalizeMediathequeImageFolder($publicDisk);
        }

        $this->info($created > 0
            ? "Structure vérifiée ({$created} dossier(s) créé(s))."
            : 'Structure déjà conforme.');

        $this->comment('Référence : documentation/storage-arborescence.md');

        return self::SUCCESS;
    }

    private function ensureDirectory($disk, string $relative): bool
    {
        $relative = trim(str_replace('\\', '/', $relative), '/');

        if ($disk->exists($relative)) {
            return false;
        }

        $disk->makeDirectory($relative);

        $keep = $relative.'/.gitkeep';
        if (! $disk->exists($keep)) {
            $disk->put($keep, '');
        }

        return true;
    }

    private function normalizeMediathequeImageFolder($disk): int
    {
        $from = UploadPaths::cms('mediatheque').'/images';
        $to = UploadPaths::mediatheque('image');

        if (! $disk->exists($from)) {
            return 0;
        }

        $moved = 0;

        foreach ($disk->allFiles($from) as $relative) {
            $target = $to.'/'.basename($relative);

            if ($disk->exists($target)) {
                $this->warn("  doublon ignoré : {$relative}");

                continue;
            }

            $this->line('  mediatheque: images/ → image/ : '.basename($relative));
            $disk->move($relative, $target);
            $moved++;
        }

        if ($moved > 0) {
            MediathequeItem::query()
                ->where('file_path', 'like', $from.'/%')
                ->each(function (MediathequeItem $item) use ($to) {
                    $item->update(['file_path' => $to.'/'.basename($item->file_path)]);
                });
        }

        $legacyDir = $disk->path($from);
        if (is_dir($legacyDir) && $this->directoryIsEmpty($legacyDir)) {
            File::deleteDirectory($legacyDir);
        }

        return $moved > 0 ? 1 : 0;
    }

    private function directoryIsEmpty(string $path): bool
    {
        foreach (scandir($path) ?: [] as $entry) {
            if ($entry !== '.' && $entry !== '..') {
                return false;
            }
        }

        return true;
    }
}

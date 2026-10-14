<?php

namespace App\Console\Commands;

use App\Models\MediathequeItem;
use App\Support\Storage\UploadPaths;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PruneLegacyStorageCommand extends Command
{
    protected $signature = 'uploads:prune-legacy
                            {--dry-run : Afficher sans supprimer}';

    protected $description = 'Nettoie storage/ : dossiers legacy vides, temporaires et médias orphelins';

    /** @var list<string> */
    private array $publicLegacyRoots = [
        'mediatheque',
        'profile_photos',
        'reports',
        'publications',
        'actualites',
        'carousel',
        'partenaires',
        'officials',
        'institution-images',
        'media',
        'rencontres',
        'demandes',
        'images',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $removed = 0;

        $publicRoot = storage_path('app/public');

        foreach ($this->publicLegacyRoots as $dir) {
            $path = $publicRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $dir);
            if (! is_dir($path)) {
                continue;
            }

            $fileCount = $this->countFiles($path);

            if ($fileCount > 0) {
                $this->warn("Conservé ({$fileCount} fichier(s)) : public/{$dir}");

                continue;
            }

            $this->line("Suppression : public/{$dir}/");

            if (! $dryRun) {
                File::deleteDirectory($path);
            }

            $removed++;
        }

        $stashRoot = storage_path('app/private/temp/form-stash');
        if (is_dir($stashRoot)) {
            $stashFiles = $this->countFiles($stashRoot);
            if ($stashFiles > 0) {
                $this->line("Suppression : private/temp/form-stash/ ({$stashFiles} fichier(s) temporaires)");
                if (! $dryRun) {
                    File::deleteDirectory($stashRoot);
                    File::ensureDirectoryExists($stashRoot);
                }
                $removed++;
            }
        }

        $legacyStash = storage_path('app/form-upload-stash');
        if (is_dir($legacyStash)) {
            $this->line('Suppression : form-upload-stash/ (ancien emplacement local)');
            if (! $dryRun) {
                File::deleteDirectory($legacyStash);
            }
            $removed++;
        }

        foreach (glob(storage_path('app/_*.php')) ?: [] as $debugScript) {
            $this->line('Suppression : app/'.basename($debugScript));
            if (! $dryRun) {
                @unlink($debugScript);
            }
            $removed++;
        }

        $mediaLibraryRoot = storage_path('media-library');
        if (is_dir($mediaLibraryRoot) && $this->countFiles($mediaLibraryRoot) === 0) {
            $this->line('Suppression : media-library/ (vide, hors app/)');
            if (! $dryRun) {
                File::deleteDirectory($mediaLibraryRoot);
            }
            $removed++;
        }

        $privateMedia = storage_path('app/private/media');
        if (is_dir($privateMedia) && Media::query()->count() === 0) {
            $orphans = $this->countFiles($privateMedia);
            if ($orphans > 0) {
                $this->line("Suppression : private/media/ ({$orphans} fichier(s) orphelin(s), table media vide)");
                if (! $dryRun) {
                    File::deleteDirectory($privateMedia);
                }
                $removed++;
            }
        }

        $removed += $this->dedupeMediathequeAudios($dryRun);

        $this->info($dryRun
            ? "Simulation : {$removed} élément(s) seraient supprimés."
            : "Nettoyage terminé ({$removed} élément(s)).");

        return self::SUCCESS;
    }

    private function dedupeMediathequeAudios(bool $dryRun): int
    {
        $disk = Storage::disk(UploadPaths::publicDisk());
        $base = UploadPaths::cms('mediatheque');
        $removed = 0;

        if (! $disk->exists($base)) {
            return 0;
        }

        foreach ($disk->files($base) as $relative) {
            if (! preg_match('/\.(mp3|wav|ogg|m4a|aac)$/i', $relative)) {
                continue;
            }

            $basename = basename($relative);
            $canonical = UploadPaths::mediatheque('audio').'/'.$basename;

            if ($disk->exists($canonical) && $disk->size($relative) === $disk->size($canonical)) {
                $this->line("Doublon audio supprimé : {$relative}");
                if (! $dryRun) {
                    $disk->delete($relative);
                }
                $removed++;

                continue;
            }

            if (! $disk->exists($canonical)) {
                $this->line("Audio déplacé : {$relative} → {$canonical}");
                if (! $dryRun) {
                    $disk->makeDirectory(dirname($canonical));
                    $disk->move($relative, $canonical);
                }
                $removed++;
            }
        }

        if (! $dryRun) {
            MediathequeItem::query()
                ->where('type', 'audio')
                ->where('file_path', 'like', $base.'/%')
                ->where('file_path', 'not like', $base.'/audio/%')
                ->each(function (MediathequeItem $item) {
                    $basename = basename($item->file_path);
                    $item->update([
                        'file_path' => UploadPaths::mediatheque('audio').'/'.$basename,
                    ]);
                });
        }

        return $removed;
    }

    private function countFiles(string $directory): int
    {
        $count = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $count++;
            }
        }

        return $count;
    }
}

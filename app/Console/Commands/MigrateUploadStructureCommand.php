<?php

namespace App\Console\Commands;

use App\Models\BeneficiaireRelationDemande;
use App\Models\Demande;
use App\Models\DemandeCreationCompte;
use App\Models\PensionMandat;
use App\Support\Storage\LegacyPublicStorageMigrator;
use App\Support\Storage\UploadPaths;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MigrateUploadStructureCommand extends Command
{
    protected $signature = 'uploads:migrate-structure
                            {--dry-run : Afficher les actions sans modifier les fichiers}
                            {--move-embedded : Déplacer les fichiers demandes.data vers demandes/embedded/…}
                            {--skip-public-legacy : Ne pas déplacer mediatheque/, reports/, profile_photos/, etc.}';

    protected $description = 'Aligne disques et chemins des fichiers existants sur config/uploads.php';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Mode simulation (--dry-run) : aucune écriture.');
        }

        $this->migrateMediaRecords($dryRun);

        if (! $this->option('skip-public-legacy')) {
            $this->info('Migration des dossiers publics legacy → cms/… / users/…');
            $moved = (new LegacyPublicStorageMigrator($this->output))->migrate($dryRun);
            $this->line("Fichiers publics traités : {$moved}");
        }

        if ($this->option('move-embedded')) {
            $this->migrateEmbeddedDemandeFiles($dryRun);
        } else {
            $this->line('Astuce : ajoutez --move-embedded pour normaliser les chemins dans demandes.data.');
        }

        $this->info('Terminé.');

        return self::SUCCESS;
    }

    private function migrateMediaRecords(bool $dryRun): void
    {
        $targets = [
            Demande::class => config('demandes.disk', 'private'),
            DemandeCreationCompte::class => config('uploads.disks.comptes', 'private'),
            PensionMandat::class => config('uploads.disks.private', 'private'),
            BeneficiaireRelationDemande::class => config('uploads.disks.private', 'private'),
        ];

        Media::query()->orderBy('id')->chunkById(100, function ($chunk) use ($targets, $dryRun) {
            foreach ($chunk as $media) {
                $targetDisk = $targets[$media->model_type] ?? null;

                if ($targetDisk === null || $media->disk === $targetDisk) {
                    continue;
                }

                $this->line("Media #{$media->id} : {$media->disk} → {$targetDisk}");

                if ($dryRun) {
                    continue;
                }

                $from = Storage::disk($media->disk);
                $to = Storage::disk($targetDisk);

                $relative = $media->getPathRelativeToRoot();
                if (! $from->exists($relative)) {
                    $this->warn("  Fichier introuvable : {$relative} (disque {$media->disk})");

                    continue;
                }

                $contents = $from->readStream($relative);
                $to->writeStream($relative, $contents);
                if (is_resource($contents)) {
                    fclose($contents);
                }

                $from->delete($relative);
                $media->disk = $targetDisk;
                $media->save();
            }
        });
    }

    private function migrateEmbeddedDemandeFiles(bool $dryRun): void
    {
        Demande::query()->orderBy('id')->chunkById(50, function ($demandes) use ($dryRun) {
            foreach ($demandes as $demande) {
                $paths = $demande->collectEmbeddedStoragePaths();
                $data = $demande->data ?? [];
                $changed = false;

                foreach ($paths as $path) {
                    if (str_starts_with($path, 'demandes/embedded/')) {
                        continue;
                    }

                    if (! preg_match('#^demandes/([^/]+)/(\d{4})/(\d{2})/#', $path, $m)) {
                        continue;
                    }

                    $type = $m[1];
                    $newPath = str_replace(
                        "demandes/{$type}/{$m[2]}/{$m[3]}",
                        UploadPaths::demandeEmbeddedPath($type),
                        $path
                    );

                    if ($newPath === $path) {
                        continue;
                    }

                    $this->line("Demande #{$demande->id} : {$path} → {$newPath}");

                    if ($dryRun) {
                        continue;
                    }

                    $diskName = UploadPaths::demandesDisk();
                    $disk = Storage::disk($diskName);

                    if (! $disk->exists($path)) {
                        $this->warn("  Fichier absent sur {$diskName} : {$path}");

                        continue;
                    }

                    $disk->makeDirectory(dirname($newPath));
                    $disk->move($path, $newPath);
                    $data = $this->replacePathInArray($data, $path, $newPath);
                    $changed = true;
                }

                if ($changed && ! $dryRun) {
                    $demande->update(['data' => $data]);
                }
            }
        });
    }

    private function replacePathInArray(array $data, string $from, string $to): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value) && $value === $from) {
                $data[$key] = $to;
            } elseif (is_array($value)) {
                $data[$key] = $this->replacePathInArray($value, $from, $to);
            }
        }

        return $data;
    }
}

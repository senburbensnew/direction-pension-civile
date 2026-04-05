<?php

namespace App\Services;

use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\DemandeDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class DemandeService
{
    /**
     * Disk de stockage (private/public)
     */
    protected string $disk;

    /**
     * Liste des chemins stockés (utile si rollback manuel)
     */
    protected array $storedPaths = [];

    public function __construct()
    {
        // Disk configurable via config/demandes.php
        $this->disk = config('demandes.disk', 'private');
    }

    /*
    |--------------------------------------------------------------------------
    | STOCKAGE DES FICHIERS
    |--------------------------------------------------------------------------
    */

    /**
     * Stocke tous les fichiers d’une demande
     *
     * @param Demande $demande
     * @param array   $files (ex: $request->allFiles())
     */
    public function storeFiles(Demande $demande, array $files): void
    {
        foreach ($files as $field => $fileOrArray) {

            // Cas champs multiples (input[])
            if (is_array($fileOrArray)) {
                foreach ($fileOrArray as $file) {
                    if ($file instanceof UploadedFile) {
                        $this->storeSingleFile($demande, $field, $file);
                    }
                }
                continue;
            }

            // Cas fichier simple
            if ($fileOrArray instanceof UploadedFile) {
                $this->storeSingleFile($demande, $field, $fileOrArray);
            }
        }
    }

    /**
     * Stocke un seul fichier (sécurisé)
     */
    protected function storeSingleFile(
        Demande $demande,
        string $type,
        UploadedFile $file
    ): void {
        // Sécurité : fichier valide
        if (!$file->isValid()) {
            throw new \RuntimeException("Fichier invalide pour le champ {$type}");
        }

        // Chemin de base selon le type de demande
        $basePath = $this->getBasePathForDemande($demande);

        try {
            // 1️⃣ Stockage physique du fichier
            $path = $file->store($basePath, $this->disk);

            // 2️⃣ Enregistrement en base
            DemandeDocument::create([
                'demande_id'    => $demande->id,
                'type'          => $type,
                'disk'          => $this->disk,
                'path'          => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type'     => $file->getMimeType(),
                'size'          => $file->getSize(),
            ]);

            // Sauvegarde pour rollback éventuel
            $this->storedPaths[] = $path;

        } catch (\Throwable $e) {

            // Nettoyage du fichier si erreur DB
            if (isset($path)) {
                Storage::disk($this->disk)->delete($path);
            }

            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CHEMINS DE STOCKAGE
    |--------------------------------------------------------------------------
    */

    /**
     * Retourne le chemin de stockage selon le type de demande
     */
    protected function getBasePathForDemande(Demande $demande): string
    {
        return match ($demande->type) {
            TypeDemandeEnum::DEMANDE_PENSION->value =>
                'demandes/pension',

            TypeDemandeEnum::DEMANDE_PENSION_REVERSION->value =>
                'demandes/pension-reversion',

            TypeDemandeEnum::DEMANDE_ADHESION->value =>
                'demandes/adhesion',

            TypeDemandeEnum::DEMANDE_ETAT_CARRIERE->value =>
                'demandes/etat-carriere',

            TypeDemandeEnum::DEMANDE_RENCONTRE->value =>
                'demandes/rencontre',

            default =>
                'demandes/autres',
        } . '/' . now()->format('Y/m');
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDATION DOCUMENTAIRE AVANT SOUMISSION
    |--------------------------------------------------------------------------
    */

    /**
     * Vérifie que tous les documents requis sont présents
     *
     * @return array<string> Liste des erreurs
     */
    public function validateDocumentsForSubmission(Demande $demande): array
    {
        $docConfig = $this->getDocumentConfig($demande);
        $errors = [];

        foreach (['multiple', 'single'] as $group) {
            foreach ($docConfig[$group] ?? [] as $field => $config) {

                $label = $config['label'] ?? $field;
                $min   = $config['min_files'] ?? 0;
                $max   = $config['max_files'] ?? 0;

                // ⚡ Optimisé : requête SQL directe
                $count = $demande->documents()
                    ->where('type', $field)
                    ->count();

                if ($min > 0 && $count < $min) {
                    $errors[] =
                        "Le champ « {$label} » nécessite au moins {$min} fichier(s).";
                }

                if ($max > 0 && $count > $max) {
                    $errors[] =
                        "Le champ « {$label} » ne peut pas contenir plus de {$max} fichier(s).";
                }
            }
        }

        return $errors;
    }

    /**
     * Indique si la demande est prête à être soumise
     */
    public function isDemandeReadyForSubmission(Demande $demande): bool
    {
        return empty($this->validateDocumentsForSubmission($demande));
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIGURATION DOCUMENTAIRE
    |--------------------------------------------------------------------------
    */

    /**
     * Retourne la configuration des documents selon le type de demande
     */
    protected function getDocumentConfig(Demande $demande): array
    {
        return match ($demande->type) {
            TypeDemandeEnum::DEMANDE_PENSION->value =>
                config('demandes.demande_pension.documents'),

            TypeDemandeEnum::DEMANDE_PENSION_REVERSION->value =>
                config('demandes.demande_pension_reversion.documents'),

            TypeDemandeEnum::DEMANDE_ADHESION->value =>
                config('demandes.demande_adhesion.documents'),

            TypeDemandeEnum::DEMANDE_ETAT_CARRIERE->value =>
                config('demandes.demande_etat_carriere.documents'),

            default => [],
        };
    }

    /*
    |--------------------------------------------------------------------------
    | ROLLBACK MANUEL (OPTIONNEL)
    |--------------------------------------------------------------------------
    */

    /**
     * Supprime tous les fichiers stockés pendant l’exécution courante
     * (utile si appel hors transaction)
     */
    public function rollback(): void
    {
        if (empty($this->storedPaths)) {
            return;
        }

        Storage::disk($this->disk)->delete($this->storedPaths);

        DemandeDocument::whereIn('path', $this->storedPaths)->delete();

        Log::info('Rollback des fichiers uploadés', [
            'paths' => $this->storedPaths,
        ]);

        $this->storedPaths = [];
    }
}
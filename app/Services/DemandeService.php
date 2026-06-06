<?php

namespace App\Services;

use App\Models\Demande;
use Illuminate\Http\UploadedFile;

class DemandeService
{
    protected string $disk;

    public function __construct()
    {
        $this->disk = config('demandes.disk', 'public');
    }

    public function storeFiles(Demande $demande, array $files): void
    {
        foreach ($files as $field => $fileOrArray) {
            if (is_array($fileOrArray)) {
                foreach ($fileOrArray as $file) {
                    if ($file instanceof UploadedFile) {
                        $this->storeSingleFile($demande, $field, $file);
                    }
                }
                continue;
            }

            if ($fileOrArray instanceof UploadedFile) {
                $this->storeSingleFile($demande, $field, $fileOrArray);
            }
        }
    }

    protected function storeSingleFile(Demande $demande, string $type, UploadedFile $file): void
    {
        if (!$file->isValid()) {
            throw new \RuntimeException("Fichier invalide pour le champ {$type}");
        }

        $demande->addMedia($file)
            ->usingFileName($file->getClientOriginalName())
            ->toMediaCollection($type, $this->disk);
    }

    public function validateDocumentsForSubmission(Demande $demande): array
    {
        $docConfig = $this->getDocumentConfig($demande);
        $errors = [];

        foreach (['multiple', 'single'] as $group) {
            foreach ($docConfig[$group] ?? [] as $field => $config) {
                $label = $config['label'] ?? $field;
                $min   = $config['min_files'] ?? 0;
                $max   = $config['max_files'] ?? 0;

                $count = $demande->getMedia($field)->count();

                if ($min > 0 && $count < $min) {
                    $errors[] = "Le champ « {$label} » nécessite au moins {$min} fichier(s).";
                }

                if ($max > 0 && $count > $max) {
                    $errors[] = "Le champ « {$label} » ne peut pas contenir plus de {$max} fichier(s).";
                }
            }
        }

        return $errors;
    }

    public function isDemandeReadyForSubmission(Demande $demande): bool
    {
        return empty($this->validateDocumentsForSubmission($demande));
    }

    protected function getDocumentConfig(Demande $demande): array
    {
        return config('demandes.' . strtolower($demande->type) . '.documents', []);
    }
}

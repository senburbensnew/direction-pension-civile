<?php

namespace App\Services;

use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\DemandeCreationCompte;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkflowStep;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class RencontreMotifService
{
    /**
     * Liste des motifs disponibles pour une demande de rendez-vous.
     *
     * @return array<string, string>
     */
    public function motifs(): array
    {
        $motifs = [];

        foreach (config('rencontre.motifs', []) as $key => $cfg) {
            $motifs[$key] = (string) ($cfg['label'] ?? $key);
        }

        return $motifs;
    }

    /**
     * Motifs proposés à la prise de rendez-vous (portail pension, y compris compte provisoire).
     *
     * @return array<string, string>
     */
    public function motifsForUser(?User $user): array
    {
        if (! $user?->hasPensionPortalAccess() && ! $this->userNeedsFinalisationCompteRdv($user)) {
            return [];
        }

        return array_intersect_key(
            $this->motifs(),
            array_flip([DemandeCreationCompte::MOTIF_RDV_FINALISATION])
        );
    }

    public function userNeedsFinalisationCompteRdv(?User $user): bool
    {
        if (! $user?->isProvisionnel()) {
            return false;
        }

        $demande = $user->demandeCreationCompte;

        return $demande !== null && $demande->isPending();
    }

    /**
     * @return array<string, string>
     */
    public function motifServiceCodes(): array
    {
        $map = [];

        foreach (config('rencontre.motifs', []) as $key => $cfg) {
            if (!empty($cfg['service'])) {
                $map[$key] = (string) $cfg['service'];
            }
        }

        return $map;
    }

    /**
     * Libellés des services.
     */
    public function serviceLabels(): array
    {
        return [
            Service::LIQUIDATION => 'Service de Liquidation',
            Service::FORMALITE => 'Service Formalités',
            Service::COMPTABILITE => 'Service de la Comptabilité / Paiement',
            Service::ADMINISTRATIF => 'Service Administratif / Base de données',
            Service::CONTROLE_PLACEMENT => 'Service de Contrôle',
            Service::DIRECTION => 'Direction de la Pension Civile',
            Service::SECRETARIAT => 'Secrétariat',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function motifServiceLabels(?User $user = null): array
    {
        $labels = [];

        $motifKeys = $user !== null
            ? array_keys($this->motifsForUser($user))
            : array_keys($this->motifs());

        foreach ($motifKeys as $motif) {
            $labels[$motif] = $this->serviceLabelForMotif($motif, $user);
        }

        return $labels;
    }

    public function serviceLabelForMotif(string $motif, ?User $user = null): string
    {
        $code = $this->motifServiceCodes()[$motif] ?? null;

        if ($code === null) {
            return '—';
        }

        $service = $this->resolve($motif, $user);

        if ($service) {
            return $this->serviceLabels()[$service->code]
                ?? $service->nom
                ?? '—';
        }

        return $this->serviceLabels()[$code] ?? '—';
    }

    /**
     * Clé motif (config) à partir d'une clé ou d'un libellé affiché.
     */
    public function normalizeMotifKey(string $motif): string
    {
        $motif = trim($motif);

        if ($motif === '' || array_key_exists($motif, $this->motifs())) {
            return $motif;
        }

        foreach ($this->motifs() as $key => $label) {
            if ($label === $motif) {
                return $key;
            }
        }

        return $motif;
    }

    /**
     * Détermine le service responsable d'un motif.
     */
    public function resolve(string $motif, ?User $user = null): ?Service
    {
        $motif = $this->normalizeMotifKey($motif);

        if ($motif === 'suivi_dossier') {
            return $this->serviceFromLatestDossier($user);
        }

        $code = $this->motifServiceCodes()[$motif] ?? null;

        if (! $code) {
            return null;
        }

        return $this->lookupServiceByCode($code);
    }

    private function lookupServiceByCode(string $code): ?Service
    {
        $service = Service::query()
            ->where('code', $code)
            ->first();

        if ($service) {
            return $service;
        }

        $catalogCode = $code;

        foreach (Service::publicCodeAliases() as $canonical => $aliases) {
            if ($code !== $canonical && ! in_array($code, $aliases, true)) {
                continue;
            }

            $catalogCode = $canonical;
            $codes = array_values(array_unique(array_merge([$canonical], $aliases)));

            $service = Service::query()
                ->whereIn('code', $codes)
                ->first();

            if ($service) {
                return $service;
            }

            break;
        }

        return $this->ensureServiceFromCatalog($catalogCode);
    }

    private function ensureServiceFromCatalog(string $code): ?Service
    {
        $entry = collect(config('dpc_services', []))->firstWhere('code', $code);

        if (! $entry) {
            return null;
        }

        return Service::query()->firstOrCreate(
            ['code' => $code],
            [
                'nom' => $entry['nom'] ?? $code,
                'description' => $entry['resume'] ?? null,
                'icon' => $entry['icon'] ?? null,
            ]
        );
    }

    public function hasMotif(string $motif): bool
    {
        return array_key_exists($motif, $this->motifs());
    }

    public function serviceCodeForMotif(string $motif): ?string
    {
        return $this->motifServiceCodes()[$motif] ?? null;
    }

    public function motifLabel(string $motif): ?string
    {
        return $this->motifs()[$motif] ?? null;
    }

    /**
     * Configuration documents pour un motif.
     *
     * @return array{single: array<string, array>, multiple: array<string, array>}
     */
    public function documentsConfig(string $motif): array
    {
        $cfg = config("rencontre.motifs.{$motif}.documents", []);

        return [
            'single' => $cfg['single'] ?? [],
            'multiple' => $cfg['multiple'] ?? [],
        ];
    }

    /**
     * Documents « à préparer » affichés au récapitulatif.
     *
     * @return array<int, string>
     */
    public function prepareHints(string $motif, string $modalite): array
    {
        $hints = config(
            "rencontre.motifs.{$motif}.prepare.{$modalite}",
            []
        );

        return is_array($hints) ? array_values($hints) : [];
    }

    /**
     * Règles de validation Laravel pour les pièces jointes d'un motif.
     *
     * @return array<string, mixed>
     */
    public function documentValidationRules(string $motif): array
    {
        $rules = [];
        $docs = $this->documentsConfig($motif);

        foreach ($docs['single'] as $key => $cfg) {
            $field = "doc_{$key}";
            $mimeRule = 'mimes:' . ($cfg['mimes'] ?? 'pdf,jpg,jpeg,png');
            $maxKb = (int) ($cfg['max_kb'] ?? 5120);

            if ($cfg['required'] ?? false) {
                $rules[$field] = [
                    'required',
                    'file',
                    $mimeRule,
                    'max:' . $maxKb,
                ];
            } else {
                $rules[$field] = [
                    'nullable',
                    'file',
                    $mimeRule,
                    'max:' . $maxKb,
                ];
            }
        }

        foreach ($docs['multiple'] as $key => $cfg) {
            $field = "doc_{$key}";
            $mimeRule = 'mimes:' . ($cfg['mimes'] ?? 'pdf,jpg,jpeg,png');
            $maxKb = (int) ($cfg['max_kb'] ?? 5120);
            $min = (int) ($cfg['min_files'] ?? 0);
            $max = (int) ($cfg['max_files'] ?? 5);

            $fieldRules = ['nullable', 'array'];

            if ($min > 0) {
                $fieldRules[] = "min:{$min}";
            }

            if ($max > 0) {
                $fieldRules[] = "max:{$max}";
            }

            $rules[$field] = $fieldRules;
            $rules["{$field}.*"] = [
                'file',
                $mimeRule,
                'max:' . $maxKb,
            ];
        }

        $sup = config('rencontre.supplement', []);
        $maxSup = (int) ($sup['max_files'] ?? 10);
        $supMimes = 'mimes:' . ($sup['mimes'] ?? 'pdf,jpg,jpeg,png,doc,docx');
        $supMaxKb = (int) ($sup['max_kb'] ?? 5120);

        $rules['doc_supplement'] = ['nullable', 'array', 'max:' . $maxSup];
        $rules['doc_supplement.*'] = ['file', $supMimes, 'max:' . $supMaxKb];

        return $rules;
    }

    /**
     * Valide que le motif soumis est connu.
     *
     * @return array<int, mixed>
     */
    public function motifValidationRule(): array
    {
        return [
            'required',
            'string',
            'max:255',
            Rule::in(array_keys($this->motifs())),
        ];
    }

    /**
     * Motifs autorisés pour l'utilisateur connecté.
     *
     * @return array<int, mixed>
     */
    public function motifValidationRuleForUser(?User $user): array
    {
        return [
            'required',
            'string',
            'max:255',
            Rule::in(array_keys($this->motifsForUser($user))),
        ];
    }

    /**
     * Enregistre les fichiers validés sur la demande (collections Spatie).
     *
     * @param  array<string, mixed>  $validatedFiles  clés doc_*
     */
    public function attachUploadedDocuments(
        Demande $demande,
        string $motif,
        array $validatedFiles
    ): void {
        $docs = $this->documentsConfig($motif);

        foreach ($docs['single'] as $key => $cfg) {
            $field = "doc_{$key}";

            if (!empty($validatedFiles[$field])) {
                $demande
                    ->addMedia($validatedFiles[$field])
                    ->toMediaCollection($key, 'public');
            }
        }

        foreach ($docs['multiple'] as $key => $cfg) {
            $field = "doc_{$key}";
            $files = Arr::wrap($validatedFiles[$field] ?? []);

            foreach ($files as $file) {
                if ($file) {
                    $demande
                        ->addMedia($file)
                        ->toMediaCollection($key, 'public');
                }
            }
        }

        $supCollection = (string) config(
            'rencontre.supplement.collection',
            'rdv_supplement'
        );

        foreach (Arr::wrap($validatedFiles['doc_supplement'] ?? []) as $file) {
            if ($file) {
                $demande
                    ->addMedia($file)
                    ->toMediaCollection($supCollection, 'public');
            }
        }
    }

    /**
     * Toutes les collections média utilisées par les motifs RDV.
     *
     * @return array<string, array{multiple: bool}>
     */
    public function allDocumentCollections(): array
    {
        $collections = [];

        foreach (config('rencontre.motifs', []) as $motifCfg) {
            foreach (['single', 'multiple'] as $group) {
                foreach ($motifCfg['documents'][$group] ?? [] as $key => $cfg) {
                    $collections[$key] = [
                        'multiple' => $group === 'multiple',
                    ];
                }
            }
        }

        $sup = config('rencontre.supplement.collection', 'rdv_supplement');
        $collections[$sup] = ['multiple' => true];

        return $collections;
    }

    private function serviceFromLatestDossier(?User $user): ?Service
    {
        if (!$user) {
            return null;
        }

        $cancelledIds = WorkflowStep::query()
            ->whereIn('code', ['REJETEE', 'ANNULEE'])
            ->pluck('id');

        $dossier = Demande::query()
            ->where('created_by', $user->id)
            ->where(
                'type',
                '!=',
                TypeDemandeEnum::DEMANDE_RENCONTRE->value
            )
            ->whereNotNull('current_service_id')
            ->when(
                $cancelledIds->isNotEmpty(),
                function ($query) use ($cancelledIds) {
                    $query->where(function ($query) use ($cancelledIds) {
                        $query
                            ->whereNull('current_step_id')
                            ->orWhereNotIn(
                                'current_step_id',
                                $cancelledIds
                            );
                    });
                }
            )
            ->latest()
            ->first();

        return $dossier?->service;
    }
}

<?php

namespace App\Models;

use App\Enums\CategorieDossierEnum;
use App\Enums\RencontreStatutEnum;
use App\Enums\TypeDemandeEnum;
use App\Enums\WorkflowStepTypeEnum;
use App\Services\RencontreMotifService;
use App\Support\Storage\UploadPaths;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Demande extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'current_service_id',
                'current_step_id',
                'annotation',
                'is_urgent',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('demande');
    }

    public function registerMediaCollections(): void
    {
        $disk = (string) config('demandes.disk', 'private');

        $add = function (string $name, bool $single = false) use ($disk) {
            $collection = $this->addMediaCollection($name)->useDisk($disk);

            if ($single) {
                $collection->singleFile();
            }
        };

        foreach (config('demandes') as $key => $typeConfig) {
            if ($key === 'disk') {
                continue;
            }

            foreach (['multiple', 'single'] as $group) {
                foreach ($typeConfig['documents'][$group] ?? [] as $collectionName => $cfg) {
                    $add($collectionName, ! ($cfg['multiple'] ?? true));
                }
            }
        }

        $add('complement');
        $add('supplemental');
        $add('identite_pensionne', true);
        $add('identite_permis', true);
        $add('identite_passeport', true);
        $add('identite_cin', true);
        $add('acte_naissance', true);
        $add('identite_representant', true);
        $add('carte_pension', true);

        foreach (
            app(RencontreMotifService::class)
                ->allDocumentCollections() as $name => $cfg
        ) {
            if (in_array($name, ['carte_pension'], true)) {
                continue;
            }

            $add($name, ! ($cfg['multiple'] ?? true));
        }
    }

    public function urlForMedia(Media $media, bool $inline = true): string
    {
        abort_unless(
            $media->model_type === static::class
            && (int) $media->model_id === (int) $this->id,
            404
        );

        if ($this->mediaIsPubliclyAccessible($media)) {
            return $media->getUrl();
        }

        return route('demande.media.show', array_filter([
            'demande' => $this,
            'media' => $media,
            'inline' => $inline ? 1 : null,
        ]));
    }

    public function urlForStoredPath(?string $path, bool $inline = true): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (! $this->dataContainsStoragePath($path)) {
            return null;
        }

        foreach ([UploadPaths::demandesDisk(), UploadPaths::publicDisk(), 'public'] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                if (in_array($disk, [UploadPaths::publicDisk(), 'public'], true)) {
                    return Storage::disk($disk)->url($path);
                }

                break;
            }
        }

        return route('demande.embedded.show', array_filter([
            'demande' => $this,
            'path' => strtr(base64_encode($path), '+/', '-_'),
            'inline' => $inline ? 1 : null,
        ]));
    }

    public function dataContainsStoragePath(string $path): bool
    {
        return in_array($path, $this->collectEmbeddedStoragePaths(), true);
    }

    /**
     * @return list<string>
     */
    public function collectEmbeddedStoragePaths(): array
    {
        $paths = [];

        $walk = function ($value) use (&$paths, &$walk) {
            if (is_string($value) && $this->looksLikeStoredPath($value)) {
                $paths[] = $value;
            } elseif (is_array($value)) {
                foreach ($value as $item) {
                    $walk($item);
                }
            }
        };

        $walk($this->data ?? []);

        return array_values(array_unique($paths));
    }

    private function mediaIsPubliclyAccessible(Media $media): bool
    {
        if (! in_array($media->disk, [UploadPaths::publicDisk(), 'public'], true)) {
            return false;
        }

        return Storage::disk($media->disk)->exists($media->getPathRelativeToRoot());
    }

    private function looksLikeStoredPath(string $value): bool
    {
        if (str_starts_with($value, 'http')) {
            return false;
        }

        if (preg_match('#^\d+/.+#', $value)) {
            return true;
        }

        if (! str_contains($value, '/')) {
            return false;
        }

        return (bool) preg_match('#^(demandes/|cms/|users/|media/|profile_photos/)#', $value);
    }

    protected $fillable = [
        'code',
        'visio_token',
        'title',
        'type',
        'created_by',
        'beneficiaire_id',
        'data',
        'current_service_id',
        'current_step_id',
        'submitted_at',
        'expires_at',
        'annotation',
        'annotated_by',
        'annotated_at',
        'folder',
        'categorie',
        'is_urgent',
    ];

    protected $attributes = [
        'data' => '[]',
    ];

    protected $casts = [
        'data' => 'array',
        'submitted_at' => 'datetime',
        'expires_at' => 'datetime',
        'annotated_at' => 'datetime',
        'is_urgent' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($demande) {
            if (is_null($demande->current_step_id)) {
                $demande->current_step_id =
                    WorkflowStep::forCode('BROUILLON')?->id;
            }

            if ($demande->beneficiaire_id === null && auth()->check()) {
                $beneficiaire = auth()->user()->pensionContext()->resolveBeneficiaire();

                if ($beneficiaire) {
                    $demande->beneficiaire_id = $beneficiaire->id;
                }
            }
        });

        static::saving(function ($demande) {
            $enum = $demande->type
                ? TypeDemandeEnum::tryFrom(
                    $demande->type
                )
                : null;

            if (
                empty($demande->title)
                && $demande->type
            ) {
                $demande->title =
                    $enum?->label()
                    ?? TypeDemande::labelFor(
                        $demande->type
                    )
                    ?? $demande->type;
            }

            if (
                empty($demande->code)
                && $demande->type
            ) {
                $prefix =
                    $demande->type
                    .'-'
                    .now()->format('Ymd')
                    .'-';

                do {
                    $random = str_pad(
                        mt_rand(1, 999999),
                        6,
                        '0',
                        STR_PAD_LEFT
                    );

                    $code = $prefix.$random;
                } while (
                    static::where(
                        'code',
                        $code
                    )->exists()
                );

                $demande->code = $code;
            }

            if ($demande->type) {
                $typeCat =
                    $enum?->categorie()->value
                    ?? CategorieDossierEnum::AUTRES->value;

                $demande->categorie =
                    $demande->is_urgent
                        ? CategorieDossierEnum::DOSSIERS_URGENTS->value
                        : $typeCat;
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function service()
    {
        return $this->belongsTo(
            Service::class,
            'current_service_id'
        );
    }

    public function currentStep()
    {
        return $this->belongsTo(
            WorkflowStep::class,
            'current_step_id'
        );
    }

    public function interactions()
    {
        return $this->hasMany(
            DemandeInteraction::class
        );
    }

    /**
     * Transferts inter-services uniquement.
     */
    public function workflows()
    {
        return $this->hasMany(
            DemandeInteraction::class
        )->where(
            'type',
            DemandeInteraction::TYPE_TRANSFERT
        );
    }

    /**
     * Demandes d'avis uniquement.
     */
    public function affectations()
    {
        return $this->hasMany(
            DemandeInteraction::class
        )->where(
            'type',
            DemandeInteraction::TYPE_AVIS
        );
    }

    public function circuitSnapshot()
    {
        return $this->hasOne(
            DemandeCircuitSnapshot::class
        );
    }

    public function histories()
    {
        return $this->hasMany(
            DemandeHistory::class
        );
    }

    public function assignments()
    {
        return $this->hasMany(
            Assignment::class
        )->orderByDesc('created_at');
    }

    public function currentAssignment()
    {
        return $this->hasOne(
            Assignment::class
        )
            ->whereNull('ended_at')
            ->latest();
    }

    public function annotatedBy()
    {
        return $this->belongsTo(
            User::class,
            'annotated_by'
        );
    }

    public function messages()
    {
        return $this->hasMany(
            DemandeMessage::class
        )->orderBy(
            'created_at',
            'asc'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function addTransfert(
        $toServiceId,
        $commentaire = null
    ): DemandeInteraction {
        return $this->interactions()->create([
            'type' => DemandeInteraction::TYPE_TRANSFERT,

            'from_service_id' => $this->getOriginal(
                'current_service_id'
            )
                ?? $this->current_service_id,

            'to_service_id' => $toServiceId,

            'initiated_by' => auth()->id(),

            'commentaire' => $commentaire,

            'statut' => DemandeInteraction::STATUT_EN_ATTENTE,
        ]);
    }

    public function localisation(): string
    {
        if ($this->currentStep) {
            return $this->currentStep->nom;
        }

        return $this->service?->nom ?? '—';
    }

    public function isAnnotated(): bool
    {
        return ! is_null(
            $this->annotated_at
        );
    }

    public function isDraft(): bool
    {
        return $this->currentStep?->code
            === 'BROUILLON';
    }

    public function isClosed(): bool
    {
        return $this->currentStep?->isTerminal()
            ?? false;
    }

    /**
     * Dossiers en cours de traitement.
     *
     * Exclut les brouillons usager et les
     * étapes terminales.
     */
    public function scopeActive($query)
    {
        return $query->whereHas(
            'currentStep',
            fn ($q) => $q
                ->where(
                    'code',
                    '!=',
                    'BROUILLON'
                )
                ->where(
                    'type_noeud',
                    '!=',
                    WorkflowStepTypeEnum::TERMINAL->value
                )
        );
    }

    public function scopeClosed($query)
    {
        return $query->whereHas(
            'currentStep',
            fn ($q) => $q->where(
                'type_noeud',
                WorkflowStepTypeEnum::TERMINAL->value
            )
        );
    }

    public function isSubmitted(): bool
    {
        return $this->currentStep?->code
            === 'SOUMISE';
    }

    public function needsComplement(): bool
    {
        return $this->currentStep?->code
            === 'COMPLEMENT_REQUIS';
    }

    public function canBeEditedByUser(): bool
    {
        return in_array(
            $this->currentStep?->code,
            [
                'BROUILLON',
                'COMPLEMENT_REQUIS',
            ],
            true
        );
    }

    public function isExpired(): bool
    {
        return $this->isDraft()
            && $this->expires_at
            && $this->expires_at->isPast();
    }

    public function isDelaiLegalDepasse(
        int $delaiJours = 30
    ): bool {
        return $this->submitted_at
            && $this->submitted_at->diffInDays(
                now()
            ) > $delaiJours;
    }

    public function joursDepuisSoumission(): ?int
    {
        return $this->submitted_at
            ? (int) $this->submitted_at->diffInDays(
                now()
            )
            : null;
    }

    public function isUrgent(): bool
    {
        if ($this->is_urgent) {
            return true;
        }

        return $this->submitted_at
            && $this->submitted_at->diffInDays(
                now()
            ) > 30;
    }

    public function categorieEnum(): ?CategorieDossierEnum
    {
        return $this->categorie
            ? CategorieDossierEnum::tryFrom(
                $this->categorie
            )
            : null;
    }

    public function categorieLabel(): string
    {
        return $this->categorieEnum()?->label()
            ?? '—';
    }

    public function civilStatus(
        $name = 'civil_status_id'
    ) {
        if (! isset($this->data[$name])) {
            return null;
        }

        return CivilStatus::find(
            $this->data[$name]
        );
    }

    public function formalites(): HasMany
    {
        return $this->hasMany(Formalite::class);
    }

    public function gender($sexeId)
    {
        return Gender::find($sexeId);
    }

    public function pensionType(
        $name = 'pension_type_id'
    ) {
        if (! isset($this->data[$name])) {
            return null;
        }

        return PensionType::find(
            $this->data[$name]
        );
    }

    public function pensionCategory(
        $name = 'pension_category_id'
    ) {
        if (! isset($this->data[$name])) {
            return null;
        }

        return PensionCategory::find(
            $this->data[$name]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForUser($query)
    {
        return $query->where(
            'created_by',
            auth()->id()
        );
    }

    public function scopeOfType(
        $query,
        $type
    ) {
        return $query->where(
            'type',
            $type
        );
    }

    public function scopePending($query)
    {
        return $query->whereHas(
            'currentStep',
            fn ($q) => $q->where(
                'code',
                'EN_ATTENTE'
            )
        );
    }

    public function scopeApproved($query)
    {
        return $query->whereHas(
            'currentStep',
            fn ($q) => $q->where(
                'code',
                'APPROUVEE'
            )
        );
    }

    public function scopeInProgress($query)
    {
        return $query->whereHas(
            'currentStep',
            fn ($q) => $q->where(
                'code',
                'EN_COURS'
            )
        );
    }

    public function scopeRejected($query)
    {
        return $query->whereHas(
            'currentStep',
            fn ($q) => $q->where(
                'code',
                'REJETEE'
            )
        );
    }

    public function scopeCanceled($query)
    {
        return $query->whereHas(
            'currentStep',
            fn ($q) => $q->where(
                'code',
                'ANNULEE'
            )
        );
    }

    public function scopeCompleted($query)
    {
        return $query->whereHas(
            'currentStep',
            fn ($q) => $q->where(
                'code',
                'FINALISEE'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Rendez-vous
    |--------------------------------------------------------------------------
    */

    public static function normalizeRencontreTime(
        ?string $time
    ): ?string {
        if (
            ! $time
            || ! preg_match(
                '/^(\d{1,2}):(\d{2})/',
                $time,
                $matches
            )
        ) {
            return null;
        }

        return sprintf(
            '%02d:%02d',
            (int) $matches[1],
            (int) $matches[2]
        );
    }

    public function isRencontre(): bool
    {
        return $this->type
            === TypeDemandeEnum::DEMANDE_RENCONTRE->value;
    }

    /**
     * URL de la salle visio.
     *
     * @param  'create'|'pilotage'|'dossier'|'corbeille'|null  $back  Page de retour après la visio
     */
    public function visioPageUrl(?string $back = null): ?string
    {
        if (! filled($this->visio_token)) {
            return null;
        }

        $params = ['token' => $this->visio_token];

        if (in_array($back, ['create', 'pilotage', 'dossier', 'corbeille'], true)) {
            $params['back'] = $back;
        }

        return route('demandes.rencontre.visio', $params);
    }

    public function rencontreStatut(): RencontreStatutEnum
    {
        $stored = RencontreStatutEnum::tryFrom(
            (string) (
                $this->data['rdv_statut'] ?? ''
            )
        );

        return $stored
            ?? RencontreStatutEnum::fromWorkflow(
                $this->currentStep?->code
            );
    }

    /**
     * Statut RDV persisté dans data (sans repli workflow).
     */
    public function rencontreStatutStocke(): ?RencontreStatutEnum
    {
        return RencontreStatutEnum::tryFrom(
            (string) ($this->data['rdv_statut'] ?? '')
        );
    }

    /**
     * @param  Builder<Demande>  $query
     */
    public function scopeWhereEffectiveRencontreStatut($query, RencontreStatutEnum|string $statut): void
    {
        if (is_string($statut)) {
            $statut = RencontreStatutEnum::from($statut);
        }

        $query->where(function ($outer) use ($statut) {
            static::applyEffectiveRencontreStatutConstraint($outer, $statut);
        });
    }

    /**
     * RDV non clos (statut métier non terminal).
     *
     * @param  Builder<Demande>  $query
     */
    public function scopeWhereEffectiveRencontreStatutOpen($query): void
    {
        $query->where(function ($outer) {
            $first = true;
            foreach (RencontreStatutEnum::cases() as $case) {
                if ($case->isTerminal()) {
                    continue;
                }
                if ($first) {
                    $outer->where(function ($q) use ($case) {
                        static::applyEffectiveRencontreStatutConstraint($q, $case);
                    });
                    $first = false;
                } else {
                    $outer->orWhere(function ($q) use ($case) {
                        static::applyEffectiveRencontreStatutConstraint($q, $case);
                    });
                }
            }
        });
    }

    /**
     * RDV clos (réalisé, refusé, annulé, non honoré).
     *
     * @param  Builder<Demande>  $query
     */
    public function scopeWhereEffectiveRencontreStatutClosed($query): void
    {
        $query->where(function ($outer) {
            $first = true;
            foreach (RencontreStatutEnum::cases() as $case) {
                if (! $case->isTerminal()) {
                    continue;
                }
                if ($first) {
                    $outer->where(function ($q) use ($case) {
                        static::applyEffectiveRencontreStatutConstraint($q, $case);
                    });
                    $first = false;
                } else {
                    $outer->orWhere(function ($q) use ($case) {
                        static::applyEffectiveRencontreStatutConstraint($q, $case);
                    });
                }
            }
        });
    }

    /**
     * @param  list<RencontreStatutEnum|string>  $statuts
     * @param  Builder<Demande>  $query
     */
    public function scopeWhereEffectiveRencontreStatutIn($query, array $statuts): void
    {
        $cases = array_map(
            fn ($statut) => is_string($statut)
                ? RencontreStatutEnum::from($statut)
                : $statut,
            $statuts
        );

        $query->where(function ($outer) use ($cases) {
            $first = true;
            foreach ($cases as $case) {
                if ($first) {
                    $outer->where(function ($q) use ($case) {
                        static::applyEffectiveRencontreStatutConstraint($q, $case);
                    });
                    $first = false;
                } else {
                    $outer->orWhere(function ($q) use ($case) {
                        static::applyEffectiveRencontreStatutConstraint($q, $case);
                    });
                }
            }
        });
    }

    /**
     * @param  Builder<Demande>  $query
     */
    protected static function applyEffectiveRencontreStatutConstraint($query, RencontreStatutEnum $statut): void
    {
        $codes = $statut->matchingWorkflowStepCodes();

        $query->where('data->rdv_statut', $statut->value)
            ->orWhere(function ($q) use ($codes) {
                $q->where(function ($missing) {
                    $missing->whereNull('data->rdv_statut')
                        ->orWhere('data->rdv_statut', '');
                })->whereHas('currentStep', function ($step) use ($codes) {
                    $step->whereIn('code', $codes);
                });
            });
    }

    public function rencontreDemandeurNom(): string
    {
        $data = $this->data ?? [];
        $fromData = trim(
            (string) ($data['prenom'] ?? '')
            .' '
            .(string) ($data['nom'] ?? '')
        );

        if ($fromData !== '') {
            return $fromData;
        }

        return $this->user?->displayName() ?: '—';
    }

    public function rencontreDemandeurEmail(): string
    {
        $data = $this->data ?? [];

        if (filled($data['email'] ?? null)) {
            return (string) $data['email'];
        }

        return (string) ($this->user?->email ?? '—');
    }

    public function rencontreObjet(): string
    {
        $data = $this->data ?? [];

        foreach (['objet', 'motif', 'objet_demande'] as $key) {
            if (filled($data[$key] ?? null)) {
                return (string) $data[$key];
            }
        }

        return '—';
    }

    public function rencontreMotifLabel(): string
    {
        $data = $this->data ?? [];

        if (filled($data['motif'] ?? null)) {
            return (string) $data['motif'];
        }

        if (filled($data['service_propose'] ?? null)) {
            return (string) $data['service_propose'];
        }

        if (! $this->isRencontre()) {
            return '—';
        }

        $motifKey = (string) ($data['motif_key'] ?? '');

        if ($motifKey !== '') {
            $label = app(RencontreMotifService::class)
                ->motifLabel($motifKey);

            if (filled($label)) {
                return (string) $label;
            }
        }

        return '—';
    }

    public function statutAffiche(): string
    {
        if ($this->isRencontre()) {
            return $this->rencontreStatutMetier()->label();
        }

        return $this->currentStep?->nom ?? '—';
    }

    /**
     * Libellé affiché aux usagers (sans code technique interne).
     */
    public function libellePublic(): string
    {
        if (filled($this->title)) {
            return (string) $this->title;
        }

        $enum = $this->type
            ? TypeDemandeEnum::tryFrom((string) $this->type)
            : null;

        return $enum?->label()
            ?? TypeDemande::labelFor((string) $this->type)
            ?? (string) $this->type;
    }

    public function statutBadgeClass(): string
    {
        if ($this->isRencontre()) {
            return $this->rencontreStatutMetier()->badgeClass();
        }

        return WorkflowStep::getStatusStyle(
            (string) (
                $this->currentStep?->code ?? ''
            )
        );
    }

    public function canBeCancelledByUser(
        ?User $user = null
    ): bool {
        $user ??= auth()->user();

        if (
            ! $user
            || (int) $this->created_by
                !== (int) $user->id
        ) {
            return false;
        }

        return $this->isRencontreActif();
    }

    public function canUploadRencontreDocuments(
        ?User $user = null
    ): bool {
        $user ??= auth()->user();

        if (
            ! $user
            || ! $this->isRencontre()
            || (int) $this->created_by !== (int) $user->id
        ) {
            return false;
        }

        return ! $this->rencontreStatut()->isRdvAnnule();
    }

    public function isRencontreActif(): bool
    {
        if (
            ! $this->isRencontre()
            || $this->rencontreStatut()->isTerminal()
        ) {
            return false;
        }

        $date =
            $this->data['date_souhaitee']
            ?? null;

        $time = self::normalizeRencontreTime(
            $this->data['heure_souhaitee']
            ?? null
        );

        if ($date && $time) {
            try {
                return ! Carbon::parse(
                    $date.' '.$time
                )->isPast();
            } catch (\Throwable) {
                return true;
            }
        }

        return true;
    }

    /**
     * Date et heure planifiées du rendez-vous (confirmation / créneau retenu).
     */
    public function rencontreScheduledAt(): ?Carbon
    {
        if (! $this->isRencontre()) {
            return null;
        }

        $date = $this->data['date_souhaitee'] ?? null;
        $time = self::normalizeRencontreTime(
            $this->data['heure_souhaitee'] ?? null
        );

        if (! $date || ! $time) {
            return null;
        }

        try {
            return Carbon::parse($date.' '.$time);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * True si aucun créneau n’est enregistré ou si l’heure prévue est passée.
     */
    public function rencontreHeureEstPassee(): bool
    {
        $at = $this->rencontreScheduledAt();

        return $at === null || $at->isPast();
    }

    /**
     * Formalité, décision compte, etc. : RDV clôturé « réalisé » et créneau écoulé.
     */
    public function peutEtreTraiteApresRendezVous(): bool
    {
        return $this->rencontreStatut() === RencontreStatutEnum::REALISE
            && $this->rencontreHeureEstPassee();
    }

    /**
     * Une formalité a déjà été enregistrée pour ce RDV ou l’exercice fiscal en cours du pensionné.
     */
    public function formaliteRdvEnregistree(): bool
    {
        if ($this->formalites()->exists()) {
            return true;
        }

        $owner = $this->user ?? User::find($this->created_by);

        if (! $owner) {
            return false;
        }

        $anneeActive = AnneeFiscale::active();

        if ($anneeActive && Formalite::forYear($owner->id, $anneeActive->id)) {
            return true;
        }

        return false;
    }

    /**
     * Afficher le bouton « Traiter la formalité » sur le dossier agent.
     */
    public function peutProposerTraitementFormaliteApresRdv(): bool
    {
        return $this->peutEtreTraiteApresRendezVous()
            && ! $this->formaliteRdvEnregistree();
    }

    /**
     * Clôture (réalisé / non honoré) : interdite avant l’heure du créneau.
     */
    public function peutEtreCloture(): bool
    {
        if (! $this->isRencontre() || $this->rencontreStatut()->isTerminal()) {
            return false;
        }

        return $this->rencontreHeureEstPassee();
    }

    /**
     * Créneau passé, RDV non clos, et aucune formalité déjà enregistrée pour ce dossier / exercice.
     */
    public function doitApparaitreCommeACloturer(): bool
    {
        return $this->peutEtreCloture()
            && ! $this->formaliteRdvEnregistree();
    }

    /**
     * Statut métier pour listes / stats (clôtures explicites conservées ; Actif + formalité → Réalisé).
     */
    public function rencontreStatutAffiche(): RencontreStatutEnum
    {
        return $this->rencontreStatutMetier();
    }

    public function rencontreStatutMetier(): RencontreStatutEnum
    {
        $statut = $this->rencontreStatut();

        if ($statut->isTerminal()) {
            return $statut;
        }

        if (
            $statut === RencontreStatutEnum::ACTIF
            && $this->formaliteRdvEnregistree()
            && $this->rencontreHeureEstPassee()
        ) {
            return RencontreStatutEnum::REALISE;
        }

        return $statut;
    }

    /**
     * Créneaux totalement saturés.
     *
     * Un créneau est complet quand TOUS les agents RDV enregistrés
     * ont chacun un RDV actif sur ce créneau exact.
     *
     * Retourne un tableau plat de chaînes :
     *
     *   ["2026-09-24 14:00", "2026-09-24 15:15", ...]
     *
     * Ne PAS confondre avec « créneau réservé » : un créneau
     * avec 1 RDV sur 3 agents reste sélectionnable.
     *
     * @return array<int, string>
     */
    public static function fullyBookedRencontreSlots(): array
    {
        /*
        |--------------------------------------------------------------------------
        | Nombre total d'agents pouvant prendre un RDV
        |--------------------------------------------------------------------------
        */

        $agentCount = User::query()
            ->role(
                (string) config('rdv.agent_role', 'agent_rdv')
            )
            ->count();

        if ($agentCount === 0) {
            return [];
        }

        /*
        |--------------------------------------------------------------------------
        | Comptage des agents DISTINCTS par créneau
        |--------------------------------------------------------------------------
        |
        | On utilise un Set d'IDs d'agents par créneau pour garantir
        | qu'un même agent avec plusieurs RDV (cas anormal) ne soit
        | compté qu'une seule fois.
        |
        */

        $agentsBySlot = [];

        static::query()
            ->where(
                'type',
                TypeDemandeEnum::DEMANDE_RENCONTRE->value
            )
            ->get()
            ->filter(function (self $demande) {
                return ! $demande->rencontreStatut()->isTerminal();
            })
            ->each(function (self $demande) use (&$agentsBySlot) {
                $date = $demande->data['date_souhaitee'] ?? null;

                $time = self::normalizeRencontreTime(
                    $demande->data['heure_souhaitee'] ?? null
                );

                $agentId = $demande->data['agent_id'] ?? null;

                if (! $date || ! $time || ! $agentId) {
                    return;
                }

                $key = $date.' '.$time;

                $agentsBySlot[$key] ??= [];
                $agentsBySlot[$key][$agentId] = true;
            });

        /*
        |--------------------------------------------------------------------------
        | Retour : créneaux où tous les agents sont occupés
        |--------------------------------------------------------------------------
        */

        return collect($agentsBySlot)
            ->filter(
                fn (array $agents) => count($agents) >= $agentCount
            )
            ->keys()
            ->values()
            ->all();
    }

    public static function activeRencontreForUser(
        ?int $userId
    ): ?self {
        if (! $userId) {
            return null;
        }

        return static::query()
            ->where(
                'type',
                TypeDemandeEnum::DEMANDE_RENCONTRE->value
            )
            ->where(
                'created_by',
                $userId
            )
            ->with('currentStep')
            ->latest()
            ->get()
            ->first(
                fn (self $demande) => $demande->isRencontreActif()
            );
    }
}

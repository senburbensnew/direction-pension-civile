<?php

namespace App\Models;

use App\Enums\CategorieDossierEnum;
use App\Enums\RencontreStatutEnum;
use App\Enums\TypeDemandeEnum;
use App\Enums\WorkflowStepTypeEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

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
        foreach (config('demandes') as $key => $typeConfig) {
            if ($key === 'disk') {
                continue;
            }

            foreach (['multiple', 'single'] as $group) {
                foreach (
                    $typeConfig['documents'][$group] ?? []
                    as $collectionName => $cfg
                ) {
                    $collection = $this->addMediaCollection(
                        $collectionName
                    );

                    if (!($cfg['multiple'] ?? true)) {
                        $collection->singleFile();
                    }
                }
            }
        }

        $this->addMediaCollection('complement');
        $this->addMediaCollection('supplemental');

        $this->addMediaCollection(
            'identite_pensionne'
        )->singleFile();

        $this->addMediaCollection(
            'identite_permis'
        )->singleFile();

        $this->addMediaCollection(
            'identite_passeport'
        )->singleFile();

        $this->addMediaCollection(
            'identite_cin'
        )->singleFile();

        $this->addMediaCollection(
            'acte_naissance'
        )->singleFile();

        $this->addMediaCollection(
            'identite_representant'
        )->singleFile();

        $this->addMediaCollection(
            'carte_pension'
        )->singleFile();
    }

    protected $fillable = [
        'code',
        'visio_token',
        'title',
        'type',
        'created_by',
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
                    . '-'
                    . now()->format('Ymd')
                    . '-';

                do {
                    $random = str_pad(
                        mt_rand(1, 999999),
                        6,
                        '0',
                        STR_PAD_LEFT
                    );

                    $code = $prefix . $random;
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
            'type' =>
                DemandeInteraction::TYPE_TRANSFERT,

            'from_service_id' =>
                $this->getOriginal(
                    'current_service_id'
                )
                ?? $this->current_service_id,

            'to_service_id' =>
                $toServiceId,

            'initiated_by' =>
                auth()->id(),

            'commentaire' =>
                $commentaire,

            'statut' =>
                DemandeInteraction::STATUT_EN_ATTENTE,
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
        return !is_null(
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
        if (!isset($this->data[$name])) {
            return null;
        }

        return CivilStatus::find(
            $this->data[$name]
        );
    }

    public function gender($sexeId)
    {
        return Gender::find($sexeId);
    }

    public function pensionType(
        $name = 'pension_type_id'
    ) {
        if (!isset($this->data[$name])) {
            return null;
        }

        return PensionType::find(
            $this->data[$name]
        );
    }

    public function pensionCategory(
        $name = 'pension_category_id'
    ) {
        if (!isset($this->data[$name])) {
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
            !$time
            || !preg_match(
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

    public function statutAffiche(): string
    {
        if ($this->isRencontre()) {
            return $this->rencontreStatut()->label();
        }

        return $this->currentStep?->nom ?? '—';
    }

    public function statutBadgeClass(): string
    {
        if ($this->isRencontre()) {
            return $this->rencontreStatut()->badgeClass();
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
            !$user
            || (int) $this->created_by
                !== (int) $user->id
        ) {
            return false;
        }

        return $this->isRencontreActif();
    }

    public function isRencontreActif(): bool
    {
        if (
            !$this->isRencontre()
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
                return !Carbon::parse(
                    $date . ' ' . $time
                )->isPast();
            } catch (\Throwable) {
                return true;
            }
        }

        return true;
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

        $agentCount = \App\Models\User::query()
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
                return !$demande->rencontreStatut()->isTerminal();
            })
            ->each(function (self $demande) use (&$agentsBySlot) {
                $date = $demande->data['date_souhaitee'] ?? null;

                $time = self::normalizeRencontreTime(
                    $demande->data['heure_souhaitee'] ?? null
                );

                $agentId = $demande->data['agent_id'] ?? null;

                if (!$date || !$time || !$agentId) {
                    return;
                }

                $key = $date . ' ' . $time;

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
        if (!$userId) {
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
                fn (self $demande) =>
                    $demande->isRencontreActif()
            );
    }
}
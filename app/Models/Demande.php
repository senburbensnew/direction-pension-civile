<?php

namespace App\Models;

use App\Enums\WorkflowStepTypeEnum;
use App\Models\CivilStatus;
use App\Models\Gender;
use App\Models\PensionCategory;
use App\Models\PensionType;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Demande extends Model implements HasMedia
{
    use HasFactory, LogsActivity, InteractsWithMedia;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['current_service_id', 'current_step_id', 'annotation', 'is_urgent'])
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
                foreach ($typeConfig['documents'][$group] ?? [] as $collectionName => $cfg) {
                    $collection = $this->addMediaCollection($collectionName);
                    if (!($cfg['multiple'] ?? true)) {
                        $collection->singleFile();
                    }
                }
            }
        }
        $this->addMediaCollection('complement');
        $this->addMediaCollection('supplemental');
    }

    protected $fillable = [
        'code',
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
        'data'         => 'array',
        'submitted_at' => 'datetime',
        'expires_at'   => 'datetime',
        'annotated_at' => 'datetime',
        'is_urgent'    => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($demande) {
            if (is_null($demande->current_step_id)) {
                $demande->current_step_id = \App\Models\WorkflowStep::forCode('BROUILLON')?->id;
            }
        });

        static::saving(function ($demande) {
            if (empty($demande->title) && $demande->type) {
                $demande->title = \App\Enums\TypeDemandeEnum::from($demande->type)->label();
            }
            if (empty($demande->code) && $demande->type) {
                $prefix = $demande->type . '-' . now()->format('Ymd') . '-';
                do {
                    $random = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
                    $code   = $prefix . $random;
                } while (static::where('code', $code)->exists());
                $demande->code = $code;
            }
            if ($demande->type) {
                $typeCat = \App\Enums\TypeDemandeEnum::from($demande->type)->categorie()->value;
                $demande->categorie = $demande->is_urgent
                    ? \App\Enums\CategorieDossierEnum::DOSSIERS_URGENTS->value
                    : $typeCat;
            }
        });
    }

    /* ================= Relations ================= */

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'current_service_id');
    }

    public function currentStep()
    {
        return $this->belongsTo(\App\Models\WorkflowStep::class, 'current_step_id');
    }

    public function interactions()
    {
        return $this->hasMany(DemandeInteraction::class);
    }

    /** Transferts inter-services uniquement. */
    public function workflows()
    {
        return $this->hasMany(DemandeInteraction::class)->where('type', DemandeInteraction::TYPE_TRANSFERT);
    }

    /** Demandes d'avis uniquement. */
    public function affectations()
    {
        return $this->hasMany(DemandeInteraction::class)->where('type', DemandeInteraction::TYPE_AVIS);
    }

    public function circuitSnapshot()
    {
        return $this->hasOne(DemandeCircuitSnapshot::class);
    }

    public function histories()
    {
        return $this->hasMany(DemandeHistory::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class)->orderByDesc('created_at');
    }

    public function currentAssignment()
    {
        return $this->hasOne(Assignment::class)->whereNull('ended_at')->latest();
    }

    public function annotatedBy()
    {
        return $this->belongsTo(User::class, 'annotated_by');
    }

    public function messages()
    {
        return $this->hasMany(DemandeMessage::class)->orderBy('created_at', 'asc');
    }

    /* ================= Helpers ================= */

    public function addTransfert($toServiceId, $commentaire = null): DemandeInteraction
    {
        return $this->interactions()->create([
            'type'            => DemandeInteraction::TYPE_TRANSFERT,
            'from_service_id' => $this->getOriginal('current_service_id') ?? $this->current_service_id,
            'to_service_id'   => $toServiceId,
            'initiated_by'    => auth()->id(),
            'commentaire'     => $commentaire,
            'statut'          => DemandeInteraction::STATUT_EN_ATTENTE,
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
        return !is_null($this->annotated_at);
    }

    public function isDraft(): bool
    {
        return $this->currentStep?->code === 'BROUILLON';
    }

    public function isClosed(): bool
    {
        return $this->currentStep?->isTerminal() ?? false;
    }

    public function scopeActive($query)
    {
        return $query->whereHas('currentStep', fn($q) =>
            $q->where('type_noeud', WorkflowStepTypeEnum::INTERMEDIAIRE->value)
        );
    }

    public function scopeClosed($query)
    {
        return $query->whereHas('currentStep', fn($q) =>
            $q->where('type_noeud', WorkflowStepTypeEnum::TERMINAL->value)
        );
    }

    public function isSubmitted(): bool
    {
        return $this->currentStep?->code === 'SOUMISE';
    }

    public function needsComplement(): bool
    {
        return $this->currentStep?->code === 'COMPLEMENT_REQUIS';
    }

    public function canBeEditedByUser(): bool
    {
        return in_array($this->currentStep?->code, ['BROUILLON', 'COMPLEMENT_REQUIS']);
    }

    public function isExpired(): bool
    {
        return $this->isDraft() && $this->expires_at && $this->expires_at->isPast();
    }

    public function isDelaiLegalDepasse(int $delaiJours = 30): bool
    {
        return $this->submitted_at && $this->submitted_at->diffInDays(now()) > $delaiJours;
    }

    public function joursDepuisSoumission(): ?int
    {
        return $this->submitted_at ? (int) $this->submitted_at->diffInDays(now()) : null;
    }

    public function isUrgent(): bool
    {
        if ($this->is_urgent) {
            return true;
        }
        return $this->submitted_at && $this->submitted_at->diffInDays(now()) > 30;
    }

    public function categorieEnum(): ?\App\Enums\CategorieDossierEnum
    {
        return $this->categorie
            ? \App\Enums\CategorieDossierEnum::tryFrom($this->categorie)
            : null;
    }

    public function categorieLabel(): string
    {
        return $this->categorieEnum()?->label() ?? '—';
    }

    public function civilStatus($name = 'civil_status_id')
    {
        if (!isset($this->data[$name])) {
            return null;
        }
        return CivilStatus::find($this->data[$name]);
    }

    public function gender($sexeId)
    {
        return Gender::find($sexeId);
    }

    public function pensionType($name = 'pension_type_id')
    {
        if (!isset($this->data[$name])) {
            return null;
        }
        return PensionType::find($this->data[$name]);
    }

    public function pensionCategory($name = 'pension_category_id')
    {
        if (!isset($this->data[$name])) {
            return null;
        }
        return PensionCategory::find($this->data[$name]);
    }

    public function scopeForUser($query)
    {
        return $query->where('created_by', auth()->id());
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopePending($query)
    {
        return $query->whereHas('currentStep', fn($q) => $q->where('code', 'EN_ATTENTE'));
    }

    public function scopeApproved($query)
    {
        return $query->whereHas('currentStep', fn($q) => $q->where('code', 'APPROUVEE'));
    }

    public function scopeInProgress($query)
    {
        return $query->whereHas('currentStep', fn($q) => $q->where('code', 'EN_COURS'));
    }

    public function scopeRejected($query)
    {
        return $query->whereHas('currentStep', fn($q) => $q->where('code', 'REJETEE'));
    }

    public function scopeCanceled($query)
    {
        return $query->whereHas('currentStep', fn($q) => $q->where('code', 'ANNULEE'));
    }

    public function scopeCompleted($query)
    {
        return $query->whereHas('currentStep', fn($q) => $q->where('code', 'FINALISEE'));
    }
}

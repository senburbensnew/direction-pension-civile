<?php

namespace App\Models;

use App\Enums\PensionMandatStatusEnum;
use App\Enums\PensionMandatTypeEnum;
use App\Enums\TypeDemandeEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PensionMandat extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'beneficiaire_id',
        'type',
        'status',
        'date_debut',
        'date_fin',
        'allowed_demande_types',
        'allowed_pension_codes',
        'notes',
        'validated_by',
        'validated_at',
        'refusal_reason',
        'demande_creation_compte_id',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'allowed_demande_types' => 'array',
        'allowed_pension_codes' => 'array',
        'validated_at' => 'datetime',
        'status' => PensionMandatStatusEnum::class,
    ];

    public function mandataire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function beneficiaire(): BelongsTo
    {
        return $this->belongsTo(Beneficiaire::class);
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function demandeCreationCompte(): BelongsTo
    {
        return $this->belongsTo(DemandeCreationCompte::class, 'demande_creation_compte_id');
    }

    public function isFromDemandeCreationCompte(): bool
    {
        return $this->demande_creation_compte_id !== null;
    }

    public function typeLabel(): string
    {
        return PensionMandatTypeEnum::displayLabel((string) ($this->type ?? ''));
    }

    public function isCurrentlyActive(): bool
    {
        if (! $this->status?->allowsAccess()) {
            return false;
        }

        $today = Carbon::today();

        if ($this->date_debut && $this->date_debut->gt($today)) {
            return false;
        }

        if ($this->date_fin && $this->date_fin->lt($today)) {
            return false;
        }

        return true;
    }

    public function allowsDemandeType(string $type): bool
    {
        $allowed = $this->allowed_demande_types;

        if ($allowed === null || $allowed === []) {
            return true;
        }

        return in_array($type, $allowed, true);
    }

    public function allowsPensionCode(string $code): bool
    {
        $allowed = $this->allowed_pension_codes;

        if ($allowed === null || $allowed === []) {
            return true;
        }

        $normalized = AffiliationPension::normalizeCode($code);

        foreach ($allowed as $item) {
            if (AffiliationPension::normalizeCode((string) $item) === $normalized) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    public function allowedDemandeTypeLabels(): array
    {
        $allowed = $this->allowed_demande_types;

        if ($allowed === null || $allowed === []) {
            return ['Tous les types de demande autorisés'];
        }

        return array_values(array_filter(array_map(
            fn (string $type) => TypeDemandeEnum::tryFrom($type)?->label() ?? $type,
            $allowed,
        )));
    }

    /**
     * @return list<string>
     */
    public function allowedPensionCodeLabels(): array
    {
        $allowed = $this->allowed_pension_codes;

        if ($allowed === null || $allowed === []) {
            return ['Tous les codes pension du bénéficiaire'];
        }

        return array_map(fn ($code) => (string) $code, $allowed);
    }

    public function scopeActive($query)
    {
        return $query->where('status', PensionMandatStatusEnum::ACTIF->value);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('mandat_document')
            ->singleFile()
            ->useDisk(config('uploads.disks.private', 'private'));
    }
}

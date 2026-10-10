<?php

namespace App\Models;

use App\Enums\PensionMandatStatusEnum;
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

    public function scopeActive($query)
    {
        return $query->where('status', PensionMandatStatusEnum::ACTIF->value);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('mandat_document')->singleFile();
    }
}

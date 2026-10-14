<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Beneficiaire extends Model
{
    /**
     * Clés reconnues dans {@see $informations_complementaires} (identifiants et coordonnées).
     *
     * @var list<string>
     */
    public const COMPLEMENT_FIELDS = [
        'nif',
        'ninu',
        'cin',
        'telephone',
        'email',
        'adresse',
        'sexe',
        'lieu_naissance',
        'nationalite',
    ];

    protected $fillable = [
        'firstname',
        'lastname',
        'nif',
        'date_naissance',
        'is_mineur',
        'informations_complementaires',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'is_mineur' => 'boolean',
        'informations_complementaires' => 'array',
    ];

    public function affiliations(): HasMany
    {
        return $this->hasMany(AffiliationPension::class);
    }

    public function primaryAffiliation(): ?AffiliationPension
    {
        return $this->affiliations()->where('is_primary', true)->first()
            ?? $this->affiliations()->first();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_beneficiaire_links')
            ->withPivot(['relation', 'valid_from', 'valid_until'])
            ->withTimestamps();
    }

    public function mandats(): HasMany
    {
        return $this->hasMany(PensionMandat::class);
    }

    public function displayName(): string
    {
        return trim(implode(' ', array_filter([$this->firstname, $this->lastname])));
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, string>
     */
    public static function sanitizeInformationsComplementaires(array $input): array
    {
        $out = [];

        foreach (self::COMPLEMENT_FIELDS as $key) {
            if (! array_key_exists($key, $input)) {
                continue;
            }

            $val = trim((string) $input[$key]);

            if ($val !== '') {
                $out[$key] = $val;
            }
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public function mergeInformationsComplementaires(array $input): self
    {
        $merged = array_merge(
            $this->informations_complementaires ?? [],
            self::sanitizeInformationsComplementaires($input),
        );

        $this->informations_complementaires = $merged;

        if (isset($merged['nif'])) {
            $this->nif = $merged['nif'];
        }

        return $this;
    }

    public function complement(string $key): ?string
    {
        $value = $this->informations_complementaires[$key] ?? null;

        if (is_string($value) && $value !== '') {
            return $value;
        }

        if ($key === 'nif' && filled($this->nif)) {
            return $this->nif;
        }

        return null;
    }

    public static function nifExists(string $nif): bool
    {
        $digits = User::normalizeDigits($nif);

        if ($digits === '') {
            return false;
        }

        return static::query()
            ->whereRaw(User::digitsComparisonSql('nif').' = ?', [$digits])
            ->exists();
    }
}

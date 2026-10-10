<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Beneficiaire extends Model
{
    protected $fillable = [
        'firstname',
        'lastname',
        'nif',
        'date_naissance',
        'is_mineur',
        'galipec_pensionnaire_id',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'is_mineur' => 'boolean',
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

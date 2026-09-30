<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnneeFiscale extends Model
{
    protected $table = 'annees_fiscales';
    
    protected $fillable = [
        'code',
        'libelle',
        'date_debut',
        'date_fin',
        'active',
        'ordre',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin'   => 'date',
        'active'     => 'boolean',
        'ordre'      => 'integer',
    ];

    public function formalites(): HasMany
    {
        return $this->hasMany(Formalite::class);
    }

    public static function active(): ?self
    {
        return static::query()->where('active', true)->first();
    }

    public static function forDate(\Carbon\Carbon $date): ?self
    {
        return static::query()
            ->where('date_debut', '<=', $date)
            ->where('date_fin', '>=', $date)
            ->first();
    }

    public static function ordered(): \Illuminate\Support\Collection
    {
        return static::query()
            ->orderByDesc('ordre')
            ->orderByDesc('date_debut')
            ->get();
    }

    public function displayName(): string
    {
        return $this->libelle ?: $this->code;
    }
}
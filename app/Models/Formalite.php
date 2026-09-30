<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Formalite extends Model
{
    protected $fillable = [
        'user_id',
        'annee_fiscale_id',
        'realisee_at',
        'realisee_par',
        'demande_id',
        'motif_key',
        'service_id',
        'commentaire',
    ];

    protected $casts = [
        'realisee_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function anneeFiscale(): BelongsTo
    {
        return $this->belongsTo(AnneeFiscale::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'realisee_par');
    }

    public function demande(): BelongsTo
    {
        return $this->belongsTo(Demande::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public static function forYear(int $userId, int $anneeFiscaleId): ?self
    {
        return static::query()
            ->where('user_id', $userId)
            ->where('annee_fiscale_id', $anneeFiscaleId)
            ->first();
    }
}
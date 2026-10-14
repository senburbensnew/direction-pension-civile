<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BeneficiaireRelationEvent extends Model
{
    protected $fillable = [
        'subject_type',
        'subject_id',
        'acteur_id',
        'evenement',
        'anciennes_valeurs',
        'nouvelles_valeurs',
        'commentaire',
    ];

    protected $casts = [
        'anciennes_valeurs' => 'array',
        'nouvelles_valeurs' => 'array',
    ];

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function acteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acteur_id');
    }

    public static function record(
        Model $subject,
        string $evenement,
        ?User $acteur = null,
        ?array $anciennes = null,
        ?array $nouvelles = null,
        ?string $commentaire = null,
    ): self {
        return static::query()->create([
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'acteur_id' => $acteur?->id,
            'evenement' => $evenement,
            'anciennes_valeurs' => $anciennes,
            'nouvelles_valeurs' => $nouvelles,
            'commentaire' => $commentaire,
        ]);
    }
}

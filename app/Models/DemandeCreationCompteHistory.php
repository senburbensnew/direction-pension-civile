<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemandeCreationCompteHistory extends Model
{
    public const EVENT_SOUMISE = 'SOUMISE';

    public const EVENT_COMPTE_CREE = 'COMPTE_PROVISOIRE_CREE';

    public const EVENT_ACCEPTEE = 'ACCEPTEE';

    public const EVENT_PIECES_SUPPRIMEES = 'PIECES_SUPPRIMEES';

    public const EVENT_REFUSEE = 'REFUSEE';

    public const EVENT_COMPTE_SUPPRIME = 'COMPTE_SUPPRIME';

    public const EVENT_COMPTE_ACTIVE = 'COMPTE_ACTIVE';

    public const EVENT_RDV_REALISE = 'RDV_REALISE';

    protected $fillable = [
        'demande_creation_compte_id',
        'event',
        'statut',
        'commentaire',
        'changed_by',
        'champs',
    ];

    protected $casts = [
        'champs' => 'array',
    ];

    public function demande(): BelongsTo
    {
        return $this->belongsTo(DemandeCreationCompte::class, 'demande_creation_compte_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function eventLabel(): string
    {
        return match ($this->event) {
            self::EVENT_SOUMISE => 'Demande soumise',
            self::EVENT_COMPTE_CREE => 'Compte provisoire créé',
            self::EVENT_ACCEPTEE => 'Dossier accepté',
            self::EVENT_PIECES_SUPPRIMEES => 'Pièces jointes supprimées',
            self::EVENT_REFUSEE => 'Dossier refusé',
            self::EVENT_COMPTE_SUPPRIME => 'Compte et données supprimés',
            self::EVENT_COMPTE_ACTIVE => 'Compte activé',
            self::EVENT_RDV_REALISE => 'Rendez-vous réalisé',
            default => $this->event,
        };
    }
}

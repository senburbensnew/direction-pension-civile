<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DemandeInteraction extends Model
{
    use HasFactory;

    const TYPE_TRANSFERT = 'TRANSFERT';
    const TYPE_AVIS      = 'AVIS';

    const STATUT_EN_ATTENTE = 'EN_ATTENTE';
    const STATUT_ACCEPTE    = 'ACCEPTE';
    const STATUT_REJETE     = 'REJETE';
    const STATUT_EN_COURS   = 'EN_COURS';
    const STATUT_TERMINE    = 'TERMINE';

    protected $fillable = [
        'demande_id',
        'type',
        'from_service_id',
        'to_service_id',
        'initiated_by',
        'commentaire',
        'statut',
        'reponse',
        'repondu_at',
        'repondu_by',
    ];

    protected $casts = [
        'repondu_at' => 'datetime',
    ];

    /* ── Relations ── */

    public function demande()
    {
        return $this->belongsTo(Demande::class);
    }

    public function fromService()
    {
        return $this->belongsTo(Service::class, 'from_service_id');
    }

    public function toService()
    {
        return $this->belongsTo(Service::class, 'to_service_id');
    }

    public function initiatedBy()
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function reponduBy()
    {
        return $this->belongsTo(User::class, 'repondu_by');
    }

    /* ── Helpers ── */

    public function isTransfert(): bool
    {
        return $this->type === self::TYPE_TRANSFERT;
    }

    public function isAvis(): bool
    {
        return $this->type === self::TYPE_AVIS;
    }

    public function isPending(): bool
    {
        return $this->statut === self::STATUT_EN_ATTENTE;
    }

    public function isAccepted(): bool
    {
        return $this->statut === self::STATUT_ACCEPTE;
    }

    public function isRefused(): bool
    {
        return $this->statut === self::STATUT_REJETE;
    }

    public function isTermine(): bool
    {
        return in_array($this->statut, [self::STATUT_TERMINE, self::STATUT_REJETE]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemandeMessage extends Model
{
    protected $fillable = [
        'demande_id',
        'sender_id',
        'body',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function demande(): BelongsTo
    {
        return $this->belongsTo(Demande::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function isFromService(): bool
    {
        return $this->sender?->hasAnyRole([
            'direction', 'secretariat', 'service_liquidation',
            'service_formalite', 'service_controle_placement',
            'service_comptabilite', 'service_assurance', 'administration', 'admin',
        ]) ?? false;
    }

    public function markAsRead(): void
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }
}

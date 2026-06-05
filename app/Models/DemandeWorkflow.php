<?php

namespace App\Models;

use App\Models\Demande;
use App\Models\User;
use App\Models\Status;
use App\Models\Service;
use Illuminate\Database\Eloquent\Model;

class DemandeWorkflow extends Model
{
    protected $fillable = [
        'demande_id',
        'from_service_id',
        'to_service_id',
        'status_id',
        'action_by_user_id',
        'commentaire',
        'reception_status',
        'reception_motif',
        'reception_at',
        'reception_by_user_id',
    ];

    protected $casts = [
        'action_at'    => 'datetime',
        'reception_at' => 'datetime',
    ];

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

    public function user()
    {
        return $this->belongsTo(User::class, 'action_by_user_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function receptionBy()
    {
        return $this->belongsTo(User::class, 'reception_by_user_id');
    }

    public function isPending(): bool
    {
        return $this->reception_status === 'pending';
    }

    public function isAccepted(): bool
    {
        return $this->reception_status === 'accepted';
    }

    public function isRefused(): bool
    {
        return $this->reception_status === 'refused';
    }
}
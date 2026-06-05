<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Affectation extends Model
{
    use HasFactory;

    protected $fillable = [
        'demande_id',
        'service_id',
        'statut',
        'avis',
        'affecte_par_user_id',
        'date_affectation',
        'date_reponse',
    ];

    protected $casts = [
        'date_affectation' => 'datetime',
        'date_reponse'     => 'datetime',
    ];

    public function demande()
    {
        return $this->belongsTo(Demande::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function affectePar()
    {
        return $this->belongsTo(User::class, 'affecte_par_user_id');
    }

    public function isPending(): bool
    {
        return $this->statut === 'EN_ATTENTE';
    }

    public function isTermine(): bool
    {
        return $this->statut === 'TERMINE';
    }
}

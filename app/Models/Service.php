<?php

namespace App\Models;

use App\Models\Demande;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;

    // Codes des services
    public const SECRETARIAT          = 'secretariat';
    public const LIQUIDATION          = 'service_liquidation';
    public const CONTROLE_PLACEMENT   = 'service_controle_placement';
    public const COMPTABILITE         = 'service_comptabilite';
    public const FORMALITE            = 'service_formalite';
    public const ASSURANCE            = 'service_assurance';

    protected $fillable = [
        'code',
        'nom',
        'description',
    ];

    public function demandes()
    {
        return $this->hasMany(Demande::class, 'current_service_id');
    }
}

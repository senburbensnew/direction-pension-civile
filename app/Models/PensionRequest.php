<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PensionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'certificat_carriere',
        'copie_moniteur',
        'acte_mariage',
        'acte_naissance',
        'acte_divorce',
        'matricule_fiscal_cin',
        'photos',
        'certificat_medical',
        'souche_cheque',
        'status_id',
        'created_by'
    ];

/*     protected $attributes = [
        'status_id' => Status::getStatusPending()->id 
    ]; */

    protected $casts = [
        'photos' => 'array' 
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

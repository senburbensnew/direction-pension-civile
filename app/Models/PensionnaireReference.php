<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PensionnaireReference extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pensionnaires_reference';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'PENSIONNAIRE_ID',
        'LOCALITE_ID',
        'LOC_DESCRIPTION',
        'NATURE_ID',
        'NAT_DESCRITION',
        'NIF',
        'NOM',
        'PRENOM',
        'SEXE',
        'DATE_NAISSANCE',
        'CHEQUE_ID',
        'MONTANT_PENSION',
        'MONTANT_AVAL',
        'CREATION_DATE',
        'MODE_PAIEMENT',
        'NO_COMPTE',
        'BANK_ID',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
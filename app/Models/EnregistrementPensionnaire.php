<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EnregistrementPensionnaire extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code_pensionnaire',
        'cin_pensionnaire',
        'nif_pensionnaire',
        'nom',
        'prenom',
        'date_naissance',
        'no_moniteur',
        'photo',
        'date_liquidation_pension',
        'date_pension',
        'statut_pension',
        'mode_paiement',
        'aval',
        'montant_pension',
        'personne_contact',
        'mandataire',
        'localite',
        'statut_matrimonial',
        'cat_pension',
        'type_pension',
        'plan_assurance',
        'dependants',
        'adresse',
        'telephone',
        'email',
        'declaration',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'date_liquidation_pension' => 'date',
        'date_pension' => 'date',
        'montant_pension' => 'decimal:2',
        'dependants' => 'array',
        'declaration' => 'boolean',
    ];

    /**
     * Accessor for dependants - ensure we always return an array
     */
    public function getDependantsAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * Mutator for dependants - convert array to JSON
     */
    public function setDependantsAttribute($value)
    {
        $this->attributes['dependants'] = json_encode($value);
    }

    /**
     * Add a dependant to the formalite
     */
    public function addDependant(array $dependantData)
    {
        $dependants = $this->dependants;
        $dependants[] = $dependantData;
        $this->dependants = $dependants;
    }

    /**
     * Remove a dependant by index
     */
    public function removeDependant($index)
    {
        $dependants = $this->dependants;
        if (isset($dependants[$index])) {
            unset($dependants[$index]);
            $this->dependants = array_values($dependants); // Reindex array
        }
    }

    /**
     * Get dependants count
     */
    public function getDependantsCountAttribute()
    {
        return count($this->dependants);
    }

    /**
     * Get dependants with assurance
     */
    public function getDependantsWithAssuranceAttribute()
    {
        return array_filter($this->dependants, function($dependant) {
            return isset($dependant['assurance']) && $dependant['assurance'] === 'Oui';
        });
    }
}

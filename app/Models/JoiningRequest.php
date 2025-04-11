<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JoiningRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'institution',
        'lastname',
        'firstname',
        'mother_lastname',
        'mother_firstname',
        'birth_place',
        'birth_date',
        'nif',
        'ninu',
        'gender_id',
        'civil_status_id',
        'spouse_lastname',
        'spouse_firstname',
        'dependents',
        'entry_date',
        'current_salary',
        'previous_jobs',
        'cotisant_signature',
        "created_at",
        "updated_at",
    ];
}

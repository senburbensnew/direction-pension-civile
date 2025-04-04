<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dependant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'relation',
        'birth_date',
        'gender_id',
    ];

    /**
     * Get the existence proof request that owns the dependant.
     */
    public function existenceProofRequest()
    {
        return $this->belongsTo(ExistenceProofRequest::class);
    }
}
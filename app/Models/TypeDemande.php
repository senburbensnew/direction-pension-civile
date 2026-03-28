<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeDemande extends Model
{
    protected $table = 'types_demandes';

    protected $fillable = [
        'code',
        'label',
        'description',
        'active'
    ];
}
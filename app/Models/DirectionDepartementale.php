<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DirectionDepartementale extends Model
{
    protected $fillable = ['abbr', 'nom', 'ville', 'color', 'order'];

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequiredCircuitService extends Model
{
    protected $fillable = ['service_id', 'type_demande'];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}

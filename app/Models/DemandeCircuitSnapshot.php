<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandeCircuitSnapshot extends Model
{
    protected $fillable = ['demande_id', 'snapshot'];

    protected $casts = ['snapshot' => 'array'];

    public function demande()
    {
        return $this->belongsTo(Demande::class);
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\IncludesTrashedInAdmin;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

class DemandeCircuitSnapshot extends Model
{
    use SoftDeletes;
    use IncludesTrashedInAdmin;

    protected $fillable = ['demande_id', 'snapshot'];

    protected $casts = ['snapshot' => 'array'];

    public function demande()
    {
        return $this->belongsTo(Demande::class);
    }
}

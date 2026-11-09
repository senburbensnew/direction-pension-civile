<?php

namespace App\Models;

use App\Models\Concerns\IncludesTrashedInAdmin;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

class FluxTransitionLog extends Model
{
    use SoftDeletes;
    use IncludesTrashedInAdmin;

    protected $fillable = ['user_id', 'action', 'flux_transition_id', 'before', 'after'];

    protected $casts = [
        'before' => 'array',
        'after'  => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

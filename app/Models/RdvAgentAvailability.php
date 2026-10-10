<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RdvAgentAvailability extends Model
{
    protected $fillable = [
        'user_id',
        'available_on',
        'weekday',
        'start_time',
        'end_time',
        'is_blocked',
    ];

    protected $casts = [
        'available_on' => 'date',
        'is_blocked' => 'boolean',
        'weekday' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

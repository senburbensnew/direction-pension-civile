<?php

namespace App\Models;

use App\Enums\UserSessionEndReasonEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSessionEndEvent extends Model
{
    protected $fillable = [
        'user_id',
        'initiated_by_user_id',
        'reason',
        'session_id',
        'ip_address',
        'user_agent',
        'country_code',
        'country_name',
        'region_name',
        'city',
        'ended_at',
    ];

    protected $casts = [
        'reason' => UserSessionEndReasonEnum::class,
        'ended_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by_user_id');
    }
}

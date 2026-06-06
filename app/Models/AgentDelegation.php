<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentDelegation extends Model
{
    protected $fillable = [
        'delegating_user_id',
        'delegate_user_id',
        'service_id',
        'start_date',
        'end_date',
        'active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'active'     => 'boolean',
    ];

    public function delegatingUser()
    {
        return $this->belongsTo(User::class, 'delegating_user_id');
    }

    public function delegateUser()
    {
        return $this->belongsTo(User::class, 'delegate_user_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true)
            ->where('start_date', '<=', now()->toDateString())
            ->where('end_date', '>=', now()->toDateString());
    }

    /**
     * Returns the effective service_id for a user:
     * if they have an active delegation covering a service, they act for that service.
     */
    public static function effectiveServiceId(int $userId, int $originalServiceId): int
    {
        // Check if this user is a delegate for the original service right now
        $delegation = static::active()
            ->where('delegate_user_id', $userId)
            ->where('service_id', $originalServiceId)
            ->first();

        return $delegation ? $delegation->service_id : $originalServiceId;
    }

    /**
     * Returns all service IDs the user can act on (own + delegated).
     */
    public static function actingServiceIds(int $userId, int $ownServiceId): array
    {
        $delegatedIds = static::active()
            ->where('delegate_user_id', $userId)
            ->pluck('service_id')
            ->all();

        return array_unique(array_merge([$ownServiceId], $delegatedIds));
    }
}

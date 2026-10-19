<?php

namespace App\Services\Admin;

use App\Enums\UserSessionEndReasonEnum;
use App\Models\UserSessionEndEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UserSessionEndService
{
    public function __construct(
        private IpGeolocationService $geolocation,
    ) {}

    public function isAvailable(): bool
    {
        return Schema::hasTable('user_session_end_events');
    }

    /**
     * @param  array{id: string, user_id: int|null, ip_address: string|null, user_agent: string|null}  $session
     */
    public function recordFromSession(
        array $session,
        UserSessionEndReasonEnum $reason,
        ?int $initiatedByUserId = null,
    ): void {
        $userId = $session['user_id'] ?? null;
        if (! $this->isAvailable() || ! is_int($userId)) {
            return;
        }

        $ip = is_string($session['ip_address'] ?? null) ? $session['ip_address'] : null;
        $location = $this->geolocation->resolve($ip);
        $userAgent = $session['user_agent'] ?? null;

        UserSessionEndEvent::query()->create([
            'user_id' => $userId,
            'initiated_by_user_id' => $initiatedByUserId,
            'reason' => $reason,
            'session_id' => $session['id'] ?? null,
            'ip_address' => $ip,
            'user_agent' => is_string($userAgent) ? Str::limit($userAgent, 512, '') : null,
            'country_code' => $location['country_code'] ?? null,
            'country_name' => $location['country_name'] ?? null,
            'region_name' => $location['region_name'] ?? null,
            'city' => $location['city'] ?? null,
            'ended_at' => now(),
        ]);
    }

    public function recordFromRequest(
        int $userId,
        UserSessionEndReasonEnum $reason,
        Request $request,
        ?int $initiatedByUserId = null,
        ?string $sessionId = null,
    ): void {
        if (! $this->isAvailable()) {
            return;
        }

        $userAgent = $request->userAgent();
        $ip = $request->ip();
        $location = $this->geolocation->resolve(is_string($ip) ? $ip : null);

        UserSessionEndEvent::query()->create([
            'user_id' => $userId,
            'initiated_by_user_id' => $initiatedByUserId,
            'reason' => $reason,
            'session_id' => $sessionId,
            'ip_address' => $ip,
            'user_agent' => is_string($userAgent) ? Str::limit($userAgent, 512, '') : null,
            'country_code' => $location['country_code'] ?? null,
            'country_name' => $location['country_name'] ?? null,
            'region_name' => $location['region_name'] ?? null,
            'city' => $location['city'] ?? null,
            'ended_at' => now(),
        ]);
    }

    public function locationLabelForEvent(UserSessionEndEvent $event): string
    {
        if ($event->city || $event->country_name || $event->region_name) {
            return $this->geolocation->formatLocation([
                'city' => $event->city,
                'region_name' => $event->region_name,
                'country_name' => $event->country_name,
                'country_code' => $event->country_code,
            ]);
        }

        return $this->geolocation->formatLocation($this->geolocation->resolve($event->ip_address));
    }

    /**
     * @return Collection<int, UserSessionEndEvent>
     */
    public function recent(int $limit = 12): Collection
    {
        if (! $this->isAvailable()) {
            return collect();
        }

        return UserSessionEndEvent::query()
            ->with(['user:id,name,email', 'initiatedBy:id,name'])
            ->orderByDesc('ended_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, UserSessionEndEvent>
     */
    public function forUser(int $userId, int $limit = 20): Collection
    {
        if (! $this->isAvailable()) {
            return collect();
        }

        return UserSessionEndEvent::query()
            ->with('initiatedBy:id,name')
            ->where('user_id', $userId)
            ->orderByDesc('ended_at')
            ->limit($limit)
            ->get();
    }
}

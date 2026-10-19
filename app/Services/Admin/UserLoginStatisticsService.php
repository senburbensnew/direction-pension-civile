<?php

namespace App\Services\Admin;

use App\Models\UserLoginEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UserLoginStatisticsService
{
    public function __construct(
        private ActiveSessionService $activeSessions,
        private IpGeolocationService $geolocation,
        private UserSessionEndService $sessionEnds,
    ) {}

    public function isAvailable(): bool
    {
        return Schema::hasTable('user_login_events');
    }

    public function recordFromRequest(int $userId, Request $request): void
    {
        if (! $this->isAvailable()) {
            return;
        }

        $userAgent = $request->userAgent();
        $ip = $request->ip();
        $location = $this->geolocation->resolve(is_string($ip) ? $ip : null);

        UserLoginEvent::query()->create([
            'user_id' => $userId,
            'ip_address' => $ip,
            'user_agent' => is_string($userAgent) ? Str::limit($userAgent, 512, '') : null,
            'country_code' => $location['country_code'] ?? null,
            'country_name' => $location['country_name'] ?? null,
            'region_name' => $location['region_name'] ?? null,
            'city' => $location['city'] ?? null,
            'logged_in_at' => now(),
        ]);
    }

    public function locationLabelForIp(?string $ip): string
    {
        return $this->geolocation->formatLocation($this->geolocation->resolve($ip));
    }

    public function locationLabelForLoginEvent(UserLoginEvent $event): string
    {
        if ($event->city || $event->country_name || $event->region_name) {
            return $this->geolocation->formatLocation([
                'city' => $event->city,
                'region_name' => $event->region_name,
                'country_name' => $event->country_name,
                'country_code' => $event->country_code,
            ]);
        }

        return $this->locationLabelForIp($event->ip_address);
    }

    /**
     * @return array{
     *     today: int,
     *     last_7_days: int,
     *     last_30_days: int,
     *     unique_users_30_days: int,
     *     total: int
     * }
     */
    public function summary(): array
    {
        if (! $this->isAvailable()) {
            return [
                'today' => 0,
                'last_7_days' => 0,
                'last_30_days' => 0,
                'unique_users_30_days' => 0,
                'total' => 0,
            ];
        }

        $today = now()->toDateString();
        $since7 = now()->subDays(6)->startOfDay();
        $since30 = now()->subDays(29)->startOfDay();

        return [
            'today' => UserLoginEvent::query()->whereDate('logged_in_at', $today)->count(),
            'last_7_days' => UserLoginEvent::query()->where('logged_in_at', '>=', $since7)->count(),
            'last_30_days' => UserLoginEvent::query()->where('logged_in_at', '>=', $since30)->count(),
            'unique_users_30_days' => (int) UserLoginEvent::query()
                ->where('logged_in_at', '>=', $since30)
                ->selectRaw('count(distinct user_id) as aggregate')
                ->value('aggregate'),
            'total' => UserLoginEvent::query()->count(),
        ];
    }

    /**
     * @return Collection<int, array{date: string, count: int}>
     */
    public function dailySeries(int $days = 14): Collection
    {
        if (! $this->isAvailable()) {
            return collect();
        }

        $start = now()->copy()->subDays($days - 1)->startOfDay();
        $rows = UserLoginEvent::query()
            ->selectRaw('date(logged_in_at) as day, count(*) as aggregate')
            ->where('logged_in_at', '>=', $start)
            ->groupBy('day')
            ->pluck('aggregate', 'day');

        return collect(range(0, $days - 1))
            ->map(function (int $offset) use ($rows) {
                $date = now()->copy()->subDays($offset)->toDateString();

                return [
                    'date' => $date,
                    'count' => (int) ($rows[$date] ?? 0),
                ];
            });
    }

    /**
     * @return array<string, int>
     */
    public function deviceBreakdown(int $days = 30): array
    {
        if (! $this->isAvailable()) {
            return [];
        }

        $since = now()->subDays($days - 1)->startOfDay();
        $agents = UserLoginEvent::query()
            ->where('logged_in_at', '>=', $since)
            ->pluck('user_agent');

        return $this->countByDeviceLabel($agents);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $sessions
     * @return array<string, int>
     */
    public function activeSessionDeviceBreakdown(Collection $sessions): array
    {
        $agents = $sessions->pluck('user_agent');

        return $this->countByDeviceLabel($agents);
    }

    /**
     * @return Collection<int, UserLoginEvent>
     */
    public function recent(int $limit = 12): Collection
    {
        if (! $this->isAvailable()) {
            return collect();
        }

        return UserLoginEvent::query()
            ->with('user:id,name,email')
            ->orderByDesc('logged_in_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, array{
     *     type: string,
     *     at: \Illuminate\Support\Carbon,
     *     ip_address: string|null,
     *     user_agent: string|null,
     *     location_label: string,
     *     detail_label: string|null,
     *     actor_name: string|null
     * }>
     */
    public function connectionTimelineForUser(int $userId, int $limit = 25): Collection
    {
        $entries = collect();

        if ($this->isAvailable()) {
            foreach (UserLoginEvent::query()->where('user_id', $userId)->orderByDesc('logged_in_at')->limit($limit)->get() as $login) {
                $entries->push([
                    'type' => 'login',
                    'at' => $login->logged_in_at,
                    'ip_address' => $login->ip_address,
                    'user_agent' => $login->user_agent,
                    'location_label' => $this->locationLabelForLoginEvent($login),
                    'detail_label' => __('messages.admin_sessions.trace_login'),
                    'actor_name' => null,
                ]);
            }
        }

        if ($this->sessionEnds->isAvailable()) {
            foreach ($this->sessionEnds->forUser($userId, $limit) as $end) {
                $entries->push([
                    'type' => 'logout',
                    'at' => $end->ended_at,
                    'ip_address' => $end->ip_address,
                    'user_agent' => $end->user_agent,
                    'location_label' => $this->sessionEnds->locationLabelForEvent($end),
                    'detail_label' => $end->reason->label(),
                    'actor_name' => $end->initiatedBy?->name,
                ]);
            }
        }

        return $entries
            ->sortByDesc(fn (array $row) => $row['at']->timestamp)
            ->take($limit)
            ->values();
    }

    /**
     * @param  Collection<int, string|null>  $userAgents
     * @return array<string, int>
     */
    protected function countByDeviceLabel(Collection $userAgents): array
    {
        $counts = [];

        foreach ($userAgents as $userAgent) {
            $label = $this->activeSessions->deviceLabel(is_string($userAgent) ? $userAgent : null);
            $counts[$label] = ($counts[$label] ?? 0) + 1;
        }

        arsort($counts);

        return $counts;
    }
}

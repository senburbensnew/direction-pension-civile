<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class IpGeolocationService
{
    /**
     * @return array{
     *     country_code: string|null,
     *     country_name: string|null,
     *     region_name: string|null,
     *     city: string|null
     * }|null
     */
    public function resolve(?string $ip): ?array
    {
        if (! config('geolocation.enabled', true)) {
            return null;
        }

        $ip = is_string($ip) ? trim($ip) : '';
        if ($ip === '') {
            return null;
        }

        if ($this->isLocalOrPrivate($ip)) {
            return [
                'country_code' => null,
                'country_name' => null,
                'region_name' => null,
                'city' => null,
                '_local' => true,
            ];
        }

        $cacheKey = 'ip_geolocation:'.hash('sha256', $ip);
        $ttl = max(60, (int) config('geolocation.cache_ttl_seconds', 604800));

        /** @var array<string, mixed>|null $cached */
        $cached = Cache::remember($cacheKey, $ttl, fn () => $this->fetchFromProvider($ip));

        if ($cached === null) {
            return null;
        }

        if (! empty($cached['_local'])) {
            return [
                'country_code' => null,
                'country_name' => null,
                'region_name' => null,
                'city' => null,
                '_local' => true,
            ];
        }

        return [
            'country_code' => isset($cached['country_code']) ? (string) $cached['country_code'] : null,
            'country_name' => isset($cached['country_name']) ? (string) $cached['country_name'] : null,
            'region_name' => isset($cached['region_name']) ? (string) $cached['region_name'] : null,
            'city' => isset($cached['city']) ? (string) $cached['city'] : null,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $location
     */
    public function formatLocation(?array $location): string
    {
        if ($location === null) {
            return '—';
        }

        if (! empty($location['_local'])) {
            return __('messages.admin_sessions.location_local');
        }

        $parts = array_filter([
            $location['city'] ?? null,
            $location['region_name'] ?? null,
            $location['country_name'] ?? null,
        ], fn ($part) => is_string($part) && trim($part) !== '');

        if ($parts === []) {
            return '—';
        }

        return implode(', ', $parts);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function fetchFromProvider(string $ip): ?array
    {
        $template = (string) config('geolocation.provider_url', 'http://ip-api.com/json/{ip}');
        $url = str_replace('{ip}', urlencode($ip), $template);
        $url .= str_contains($url, '?') ? '&' : '?';
        $url .= 'fields=status,message,country,countryCode,regionName,city';

        try {
            $response = Http::timeout(max(1, (int) config('geolocation.timeout_seconds', 3)))
                ->acceptJson()
                ->get($url);
        } catch (\Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();
        if (! is_array($data) || ($data['status'] ?? '') !== 'success') {
            return null;
        }

        return [
            'country_code' => isset($data['countryCode']) ? Str::upper((string) $data['countryCode']) : null,
            'country_name' => isset($data['country']) ? (string) $data['country'] : null,
            'region_name' => isset($data['regionName']) ? (string) $data['regionName'] : null,
            'city' => isset($data['city']) ? (string) $data['city'] : null,
        ];
    }

    protected function isLocalOrPrivate(string $ip): bool
    {
        if ($ip === '127.0.0.1' || $ip === '::1' || str_starts_with($ip, '127.')) {
            return true;
        }

        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }
}

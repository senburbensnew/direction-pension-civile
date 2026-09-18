<?php

namespace App\Services;

use App\Models\SiteVisit;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SiteVisitService
{
    public function record(Request $request): void
    {
        if (! $this->shouldRecord($request)) {
            return;
        }

        $today = now()->toDateString();
        $ipHash = $this->hashIp((string) $request->ip());

        $this->ensureDayRow($today);

        DB::table('site_visits')->where('visited_on', $today)->increment('hits');

        $inserted = DB::table('site_visit_ips')->insertOrIgnore([
            'visited_on' => $today,
            'ip_hash' => $ipHash,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($inserted) {
            DB::table('site_visits')->where('visited_on', $today)->increment('visitors');
        }

        Cache::forget('site_visits.summary');
    }

    /**
     * @return array{total_hits: int, today_hits: int, total_visitors: int, today_visitors: int}
     */
    public function summary(): array
    {
        return Cache::remember('site_visits.summary', 60, function () {
            $todayRow = SiteVisit::query()->whereDate('visited_on', now()->toDateString())->first();

            return [
                'total_hits' => (int) SiteVisit::query()->sum('hits'),
                'today_hits' => (int) ($todayRow?->hits ?? 0),
                'total_visitors' => (int) SiteVisit::query()->sum('visitors'),
                'today_visitors' => (int) ($todayRow?->visitors ?? 0),
            ];
        });
    }

    /**
     * Complete daily series (missing days counted as 0), newest first.
     *
     * @return Collection<int, array{date: string, hits: int, visitors: int}>
     */
    public function dailySeries(int $days = 14): Collection
    {
        $start = now()->copy()->subDays($days - 1)->startOfDay();
        $rows = SiteVisit::query()
            ->where('visited_on', '>=', $start->toDateString())
            ->get()
            ->keyBy(fn (SiteVisit $row) => $row->visited_on->toDateString());

        return collect(range(0, $days - 1))
            ->map(function (int $offset) use ($rows) {
                $date = now()->copy()->subDays($offset)->toDateString();
                $row = $rows->get($date);

                return [
                    'date' => $date,
                    'hits' => (int) ($row?->hits ?? 0),
                    'visitors' => (int) ($row?->visitors ?? 0),
                ];
            });
    }

    protected function ensureDayRow(string $today): void
    {
        if (DB::table('site_visits')->where('visited_on', $today)->exists()) {
            return;
        }

        try {
            DB::table('site_visits')->insert([
                'visited_on' => $today,
                'hits' => 0,
                'visitors' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (QueryException) {
            // Concurrent insert for the same day.
        }
    }

    public function shouldRecord(Request $request): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        if ($request->ajax() || $request->wantsJson() || $request->prefetch()) {
            return false;
        }

        $path = ltrim($request->path(), '/');

        if ($path !== '' && preg_match('/\.(css|js|map|png|jpe?g|gif|svg|webp|ico|woff2?|ttf|eot|pdf)$/i', $path)) {
            return false;
        }

        $excludedPrefixes = [
            'admin',
            'livewire',
            'horizon',
            'telescope',
            'pulse',
            '_ignition',
            '_debugbar',
            'sanctum',
            'broadcasting',
            'storage',
            'locale',
            'notifications',
        ];

        foreach ($excludedPrefixes as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                return false;
            }
        }

        $ua = strtolower((string) $request->userAgent());
        if ($ua === '' || preg_match('/bot|crawl|spider|slurp|facebookexternalhit|whatsapp|telegram|preview|lighthouse|pingdom|gtmetrix|headless/i', $ua)) {
            return false;
        }

        return true;
    }

    protected function hashIp(string $ip): string
    {
        return hash('sha256', $ip.'|'.(string) config('app.key'));
    }
}

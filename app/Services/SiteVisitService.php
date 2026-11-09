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

        $this->refreshSummaryCacheAfterRecord((bool) $inserted);
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

    protected function refreshSummaryCache(): void
    {
        $today = now()->toDateString();
        $todayRow = DB::table('site_visits')->where('visited_on', $today)->first();

        Cache::put('site_visits.summary', [
            'total_hits' => (int) DB::table('site_visits')->sum('hits'),
            'today_hits' => (int) ($todayRow?->hits ?? 0),
            'total_visitors' => (int) DB::table('site_visits')->sum('visitors'),
            'today_visitors' => (int) ($todayRow?->visitors ?? 0),
        ], 60);
    }

    protected function refreshSummaryCacheAfterRecord(bool $newVisitor): void
    {
        $cached = Cache::get('site_visits.summary');

        if (! is_array($cached)) {
            $this->refreshSummaryCache();

            return;
        }

        $cached['total_hits'] = (int) ($cached['total_hits'] ?? 0) + 1;
        $cached['today_hits'] = (int) ($cached['today_hits'] ?? 0) + 1;

        if ($newVisitor) {
            $cached['total_visitors'] = (int) ($cached['total_visitors'] ?? 0) + 1;
            $cached['today_visitors'] = (int) ($cached['today_visitors'] ?? 0) + 1;
        }

        Cache::put('site_visits.summary', $cached, 60);
    }

    protected function ensureDayRow(string $today): void
    {
        try {
            DB::table('site_visits')->insertOrIgnore([
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

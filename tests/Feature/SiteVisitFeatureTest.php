<?php

namespace Tests\Feature;

use App\Models\SiteVisit;
use App\Services\SiteVisitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteVisitFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function public_home_page_increments_hits_and_unique_visitor_once_per_session(): void
    {
        $this->withSession([]);

        $first = $this->get('/');
        $first->assertOk();
        $first->assertSee(__('messages.visits_total'));

        $today = SiteVisit::query()->whereDate('visited_on', now()->toDateString())->first();
        $this->assertNotNull($today);
        $this->assertSame(1, $today->hits);
        $this->assertSame(1, $today->visitors);

        $this->get('/');

        $today->refresh();
        $this->assertSame(2, $today->hits);
        $this->assertSame(1, $today->visitors);
    }

    /** @test */
    public function unique_visitors_are_counted_by_ip_per_day(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])->get('/');
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])->get('/');
        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.25'])->get('/');

        $today = SiteVisit::query()->whereDate('visited_on', now()->toDateString())->first();
        $this->assertNotNull($today);
        $this->assertSame(3, $today->hits);
        $this->assertSame(2, $today->visitors);
    }

    /** @test */
    public function admin_and_bot_requests_are_not_counted(): void
    {
        $this->get('/admin/dashboard');
        $this->withHeaders(['User-Agent' => 'Googlebot'])->get('/');

        $this->assertSame(0, SiteVisit::query()->sum('hits'));
    }

    /** @test */
    public function summary_aggregates_totals(): void
    {
        SiteVisit::create([
            'visited_on' => now()->toDateString(),
            'hits' => 5,
            'visitors' => 2,
        ]);
        SiteVisit::create([
            'visited_on' => now()->subDays(2)->toDateString(),
            'hits' => 3,
            'visitors' => 1,
        ]);

        $summary = app(SiteVisitService::class)->summary();

        $this->assertSame(8, $summary['total_hits']);
        $this->assertSame(5, $summary['today_hits']);
        $this->assertSame(3, $summary['total_visitors']);
        $this->assertSame(2, $summary['today_visitors']);
    }

    /** @test */
    public function daily_series_includes_today_and_empty_days(): void
    {
        SiteVisit::create([
            'visited_on' => now()->toDateString(),
            'hits' => 4,
            'visitors' => 2,
        ]);

        $daily = app(SiteVisitService::class)->dailySeries(7);

        $this->assertCount(7, $daily);
        $this->assertSame(now()->toDateString(), $daily->first()['date']);
        $this->assertSame(4, $daily->first()['hits']);
        $this->assertSame(0, $daily[1]['hits']);
    }
}

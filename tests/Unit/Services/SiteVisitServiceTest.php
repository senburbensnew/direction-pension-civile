<?php

namespace Tests\Unit\Services;

use App\Services\SiteVisitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SiteVisitServiceTest extends TestCase
{
    use RefreshDatabase;

    private SiteVisitService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SiteVisitService;
        Cache::flush();
    }

    /** @test */
    public function should_record_accepts_public_get_html_requests(): void
    {
        $request = Request::create('/contact', 'GET', [], [], [], [
            'HTTP_USER_AGENT' => 'Mozilla/5.0',
        ]);

        $this->assertTrue($this->service->shouldRecord($request));
    }

    /** @test */
    public function should_record_rejects_admin_ajax_and_bots(): void
    {
        $admin = Request::create('/admin/dashboard', 'GET');
        $this->assertFalse($this->service->shouldRecord($admin));

        $ajax = Request::create('/contact', 'GET', [], [], [], [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]);
        $this->assertFalse($this->service->shouldRecord($ajax));

        $bot = Request::create('/', 'GET', [], [], [], [
            'HTTP_USER_AGENT' => 'Googlebot/2.1',
        ]);
        $this->assertFalse($this->service->shouldRecord($bot));
    }

    /** @test */
    public function record_increments_hits_and_unique_visitors(): void
    {
        $first = Request::create('/', 'GET', server: [
            'REMOTE_ADDR' => '203.0.113.10',
            'HTTP_USER_AGENT' => 'Mozilla/5.0',
        ]);
        $second = Request::create('/', 'GET', server: [
            'REMOTE_ADDR' => '203.0.113.10',
            'HTTP_USER_AGENT' => 'Mozilla/5.0',
        ]);
        $otherIp = Request::create('/', 'GET', server: [
            'REMOTE_ADDR' => '203.0.113.11',
            'HTTP_USER_AGENT' => 'Mozilla/5.0',
        ]);

        $this->service->record($first);
        $this->service->record($second);
        $this->service->record($otherIp);

        $summary = $this->service->summary();

        $this->assertSame(3, $summary['today_hits']);
        $this->assertSame(2, $summary['today_visitors']);
    }
}

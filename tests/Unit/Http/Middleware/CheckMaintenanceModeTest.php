<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\CheckMaintenanceMode;
use Illuminate\Http\Request;
use Tests\Support\InteractsWithMiddleware;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CheckMaintenanceModeTest extends TestCase
{
    use InteractsWithMiddleware;
    use RefreshDatabase;

    /** @test */
    public function allows_request_when_maintenance_flag_is_off(): void
    {
        Cache::forget('is_maintenance_mode');

        $response = $this->runMiddleware(Request::create('/', 'GET'), app(CheckMaintenanceMode::class));

        $this->assertSame(200, $response->getStatusCode());
    }
}

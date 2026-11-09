<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\TrustHosts;
use Illuminate\Http\Request;
use Tests\Support\InteractsWithMiddleware;
use Tests\TestCase;

class TrustHostsTest extends TestCase
{
    use InteractsWithMiddleware;

    /** @test */
    public function hosts_returns_application_subdomain_pattern(): void
    {
        $hosts = app(TrustHosts::class)->hosts();

        $this->assertNotEmpty($hosts);
    }
}

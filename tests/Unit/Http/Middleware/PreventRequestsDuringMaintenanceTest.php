<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Http\Request;
use Tests\Support\InteractsWithMiddleware;
use Tests\TestCase;

class PreventRequestsDuringMaintenanceTest extends TestCase
{
    use InteractsWithMiddleware;

    /** @test */
    public function handle_delegates_to_next_middleware_on_anonymous_get(): void
    {
        $response = $this->runMiddleware(Request::create('/', 'GET'), app(PreventRequestsDuringMaintenance::class));

        $this->assertSame(200, $response->getStatusCode());
    }
}

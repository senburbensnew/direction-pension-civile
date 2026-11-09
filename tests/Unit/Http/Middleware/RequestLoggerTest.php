<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\RequestLogger;
use Illuminate\Http\Request;
use Tests\Support\InteractsWithMiddleware;
use Tests\TestCase;

class RequestLoggerTest extends TestCase
{
    use InteractsWithMiddleware;

    /** @test */
    public function handle_delegates_to_next_middleware_on_anonymous_get(): void
    {
        $response = $this->runMiddleware(Request::create('/', 'GET'), app(RequestLogger::class));

        $this->assertSame(200, $response->getStatusCode());
    }
}

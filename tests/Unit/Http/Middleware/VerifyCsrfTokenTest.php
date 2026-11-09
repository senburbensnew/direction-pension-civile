<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\VerifyCsrfToken;
use Tests\Support\InteractsWithMiddleware;
use Tests\TestCase;

class VerifyCsrfTokenTest extends TestCase
{
    use InteractsWithMiddleware;

    /** @test */
    public function get_request_passes_without_token(): void
    {
        $response = $this->runMiddleware(
            $this->httpRequest('/', 'GET'),
            app(VerifyCsrfToken::class)
        );

        $this->assertSame(200, $response->getStatusCode());
    }
}

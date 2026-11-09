<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\RequirePasswordConfirmation;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\Routing\UrlGenerator;
use Tests\Support\InteractsWithMiddleware;
use Tests\TestCase;

class RequirePasswordConfirmationTest extends TestCase
{
    use InteractsWithMiddleware;

    /** @test */
    public function unconfirmed_user_is_redirected(): void
    {
        $middleware = new RequirePasswordConfirmation(
            app(ResponseFactory::class),
            app(UrlGenerator::class),
        );

        $response = $middleware->handle(
            $this->httpRequest('/personal', 'GET'),
            $this->middlewareNext(),
        );

        $this->assertTrue($response->isRedirect());
    }
}

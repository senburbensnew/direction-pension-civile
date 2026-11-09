<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\Authenticate;
use Illuminate\Auth\AuthenticationException;
use Tests\Support\InteractsWithMiddleware;
use Tests\TestCase;

class AuthenticateTest extends TestCase
{
    use InteractsWithMiddleware;

    /** @test */
    public function unauthenticated_request_is_rejected(): void
    {
        $this->expectException(AuthenticationException::class);

        $middleware = new Authenticate(app('auth'));
        $request = $this->httpRequest('/personal', 'GET');

        $middleware->handle($request, $this->middlewareNext(), 'web');
    }
}

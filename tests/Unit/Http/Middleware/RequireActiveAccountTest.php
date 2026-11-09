<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\RequireActiveAccount;
use Illuminate\Http\Request;
use Tests\Support\InteractsWithMiddleware;
use Tests\TestCase;

class RequireActiveAccountTest extends TestCase
{
    use InteractsWithMiddleware;

    /** @test */
    public function guest_is_unauthorized(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $this->runMiddleware(Request::create('/personal', 'GET'), app(RequireActiveAccount::class));
    }
}

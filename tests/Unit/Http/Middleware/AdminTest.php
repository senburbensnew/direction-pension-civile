<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\Admin;
use Illuminate\Http\Request;
use Tests\Support\InteractsWithMiddleware;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use InteractsWithMiddleware;

    /** @test */
    public function guest_is_forbidden(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $this->runMiddleware(Request::create('/admin', 'GET'), app(Admin::class));
    }
}

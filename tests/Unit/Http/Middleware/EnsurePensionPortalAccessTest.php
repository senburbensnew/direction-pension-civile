<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\EnsurePensionPortalAccess;
use Illuminate\Http\Request;
use Tests\Support\InteractsWithMiddleware;
use Tests\TestCase;

class EnsurePensionPortalAccessTest extends TestCase
{
    use InteractsWithMiddleware;

    /** @test */
    public function guest_is_forbidden(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $this->runMiddleware(Request::create('/demandes/rencontre', 'GET'), app(EnsurePensionPortalAccess::class));
    }
}

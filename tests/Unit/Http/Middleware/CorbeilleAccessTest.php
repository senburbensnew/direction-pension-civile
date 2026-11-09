<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\CorbeilleAccess;
use Illuminate\Http\Request;
use Tests\Support\InteractsWithMiddleware;
use Tests\TestCase;

class CorbeilleAccessTest extends TestCase
{
    use InteractsWithMiddleware;

    /** @test */
    public function unauthenticated_user_is_rejected(): void
    {
        $this->expectException(\Error::class);

        $this->runMiddleware(Request::create('/corbeille', 'GET'), app(CorbeilleAccess::class));
    }
}

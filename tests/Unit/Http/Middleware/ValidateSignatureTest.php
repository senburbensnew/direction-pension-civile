<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\ValidateSignature;
use Illuminate\Http\Request;
use Tests\Support\InteractsWithMiddleware;
use Tests\TestCase;

class ValidateSignatureTest extends TestCase
{
    use InteractsWithMiddleware;

    /** @test */
    public function rejects_unsigned_urls(): void
    {
        $this->expectException(\Illuminate\Routing\Exceptions\InvalidSignatureException::class);

        app(ValidateSignature::class)->handle(
            Request::create('/signed', 'GET'),
            fn () => response('ok')
        );
    }
}

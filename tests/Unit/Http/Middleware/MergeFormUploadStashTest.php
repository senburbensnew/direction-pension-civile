<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\MergeFormUploadStash;
use Illuminate\Http\Request;
use Tests\Support\InteractsWithMiddleware;
use Tests\TestCase;

class MergeFormUploadStashTest extends TestCase
{
    use InteractsWithMiddleware;

    /** @test */
    public function handle_delegates_to_next_middleware_on_anonymous_get(): void
    {
        $response = $this->runMiddleware(Request::create('/', 'GET'), app(MergeFormUploadStash::class));

        $this->assertSame(200, $response->getStatusCode());
    }
}

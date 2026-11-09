<?php

namespace Tests\Unit\Listeners;

use App\Events\RequestCreated;
use App\Listeners\RequestCreatedListener;
use stdClass;
use Tests\TestCase;

class RequestCreatedListenerTest extends TestCase
{
    /** @test */
    public function handle_accepts_request_created_event(): void
    {
        $listener = new RequestCreatedListener;
        $payload = new stdClass;

        $listener->handle(new RequestCreated($payload));

        $this->assertTrue(true);
    }
}

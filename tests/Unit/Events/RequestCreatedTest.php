<?php

namespace Tests\Unit\Events;

use App\Events\RequestCreated;
use Illuminate\Broadcasting\PrivateChannel;
use stdClass;
use Tests\TestCase;

class RequestCreatedTest extends TestCase
{
    /** @test */
    public function it_stores_the_request_payload(): void
    {
        $payload = new stdClass;
        $payload->id = 42;

        $event = new RequestCreated($payload);

        $this->assertSame($payload, $event->request);
    }

    /** @test */
    public function broadcast_on_returns_private_channel(): void
    {
        $channels = (new RequestCreated(null))->broadcastOn();

        $this->assertCount(1, $channels);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
    }
}

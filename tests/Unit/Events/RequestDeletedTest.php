<?php

namespace Tests\Unit\Events;

use App\Events\RequestDeleted;
use Illuminate\Broadcasting\PrivateChannel;
use Tests\TestCase;

class RequestDeletedTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated_and_broadcasts_on_private_channel(): void
    {
        $event = new RequestDeleted;
        $channels = $event->broadcastOn();

        $this->assertInstanceOf(RequestDeleted::class, $event);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
    }
}

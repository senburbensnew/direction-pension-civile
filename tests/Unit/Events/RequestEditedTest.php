<?php

namespace Tests\Unit\Events;

use App\Events\RequestEdited;
use Illuminate\Broadcasting\PrivateChannel;
use Tests\TestCase;

class RequestEditedTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated_and_broadcasts_on_private_channel(): void
    {
        $event = new RequestEdited;
        $channels = $event->broadcastOn();

        $this->assertInstanceOf(RequestEdited::class, $event);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
    }
}

<?php

namespace Tests\Unit\Listeners;

use App\Events\RequestEdited;
use App\Listeners\RequestEditedListener;
use Tests\TestCase;

class RequestEditedListenerTest extends TestCase
{
    /** @test */
    public function handle_accepts_request_edited_event(): void
    {
        (new RequestEditedListener)->handle(new RequestEdited);

        $this->assertTrue(true);
    }
}

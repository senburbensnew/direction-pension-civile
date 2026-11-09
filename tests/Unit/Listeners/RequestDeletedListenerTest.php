<?php

namespace Tests\Unit\Listeners;

use App\Events\RequestDeleted;
use App\Listeners\RequestDeletedListener;
use Tests\TestCase;

class RequestDeletedListenerTest extends TestCase
{
    /** @test */
    public function handle_accepts_request_deleted_event(): void
    {
        (new RequestDeletedListener)->handle(new RequestDeleted);

        $this->assertTrue(true);
    }
}

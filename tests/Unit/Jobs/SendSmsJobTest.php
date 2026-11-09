<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SendSmsJob;
use App\Services\SmsService;
use Tests\TestCase;

class SendSmsJobTest extends TestCase
{
    /** @test */
    public function handle_delivers_message_via_sms_service(): void
    {
        $this->mock(SmsService::class, function ($mock): void {
            $mock->shouldReceive('deliver')
                ->once()
                ->with('+50938123456', 'Hello');
        });

        (new SendSmsJob('+50938123456', 'Hello'))->handle(app(SmsService::class));
    }
}

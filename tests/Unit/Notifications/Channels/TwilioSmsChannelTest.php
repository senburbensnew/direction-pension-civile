<?php

namespace Tests\Unit\Notifications\Channels;

use App\Notifications\Channels\TwilioSmsChannel;
use App\Services\SmsService;
use Illuminate\Notifications\Notification;
use Tests\TestCase;

class TwilioSmsChannelTest extends TestCase
{
    /** @test */
    public function send_delivers_when_notification_exposes_twilio_payload(): void
    {
        $this->mock(SmsService::class, function ($mock): void {
            $mock->shouldReceive('send')
                ->once()
                ->with('+50937000000', 'Code: 1234');
        });

        $notification = new class extends Notification
        {
            public function toTwilio(object $notifiable): array
            {
                return [
                    'to' => '+50937000000',
                    'message' => 'Code: 1234',
                ];
            }
        };

        $channel = new TwilioSmsChannel(app(SmsService::class));
        $channel->send(new \stdClass, $notification);
    }

    /** @test */
    public function send_is_no_op_when_notification_has_no_twilio_method(): void
    {
        $this->mock(SmsService::class, function ($mock): void {
            $mock->shouldNotReceive('send');
        });

        (new TwilioSmsChannel(app(SmsService::class)))->send(new \stdClass, new Notification);
    }
}

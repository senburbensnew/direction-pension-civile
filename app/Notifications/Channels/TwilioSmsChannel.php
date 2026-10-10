<?php

namespace App\Notifications\Channels;

use App\Services\SmsService;
use Illuminate\Notifications\Notification;

class TwilioSmsChannel
{
    public function __construct(private SmsService $sms)
    {
    }

    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toTwilio')) {
            return;
        }

        /** @var array{to?: string, message?: string} $payload */
        $payload = $notification->toTwilio($notifiable);

        $to = $payload['to'] ?? $this->resolvePhone($notifiable);
        $message = $payload['message'] ?? '';

        if ($to === null || $message === '') {
            return;
        }

        $this->sms->send($to, $message);
    }

    private function resolvePhone(object $notifiable): ?string
    {
        if (method_exists($notifiable, 'routeNotificationFor')) {
            $phone = $notifiable->routeNotificationFor('twilio');

            if (filled($phone)) {
                return (string) $phone;
            }

            $channel = config('notifications.sms_channel', self::class);
            $phone = $notifiable->routeNotificationFor($channel);

            if (filled($phone)) {
                return (string) $phone;
            }
        }

        if (method_exists($notifiable, 'routeNotificationForTwilio')) {
            $phone = $notifiable->routeNotificationForTwilio();

            return filled($phone) ? (string) $phone : null;
        }

        $phone = $notifiable->phone ?? null;

        return filled($phone) ? (string) $phone : null;
    }
}

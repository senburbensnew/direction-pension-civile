<?php

namespace App\Notifications\Concerns;

use App\Notifications\Channels\TwilioSmsChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Notifiable;

trait UsesStandardChannels
{
    /**
     * @return list<string|class-string>
     */
    protected function standardVia(object $notifiable, bool $database = true): array
    {
        $channels = [];

        if ($database && $this->notifiableSupportsDatabase($notifiable)) {
            $channels[] = 'database';
        }

        if ($this->notifiableReceivesMail($notifiable)) {
            $channels[] = 'mail';
        }

        if ($this->notifiableReceivesSms($notifiable)) {
            $channels[] = config('notifications.sms_channel', TwilioSmsChannel::class);
        }

        return array_values(array_unique($channels));
    }

    protected function notifiableSupportsDatabase(object $notifiable): bool
    {
        return $notifiable instanceof Model
            && in_array(Notifiable::class, class_uses_recursive($notifiable), true);
    }

    protected function notifiableReceivesMail(object $notifiable): bool
    {
        if ($notifiable instanceof AnonymousNotifiable) {
            return filled($notifiable->routeNotificationFor('mail'));
        }

        return filled($notifiable->email ?? null);
    }

    protected function notifiableReceivesSms(object $notifiable): bool
    {
        if (! config('notifications.sms_enabled')) {
            return false;
        }

        if (! method_exists($this, 'toTwilio')) {
            return false;
        }

        if ($notifiable instanceof AnonymousNotifiable) {
            $channel = config('notifications.sms_channel', TwilioSmsChannel::class);

            return filled($notifiable->routeNotificationFor('twilio'))
                || filled($notifiable->routeNotificationFor($channel));
        }

        if (method_exists($notifiable, 'routeNotificationForTwilio')) {
            return filled($notifiable->routeNotificationForTwilio());
        }

        return filled($notifiable->phone ?? null);
    }
}

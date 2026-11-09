<?php

namespace App\Support;

use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class DevDiagnostics
{
    public static function register(): void
    {
        if (! self::shouldLogMail()) {
            return;
        }

        Event::listen(MessageSending::class, function (MessageSending $event): void {
            self::logOutgoingMail($event);
        });
    }

    public static function shouldLogOtp(): bool
    {
        return app()->environment('local') && config('dev.log_otp', false);
    }

    public static function shouldLogMail(): bool
    {
        if (config('mail.default') === 'mail') {
            return false;
        }

        return app()->environment('local') && config('dev.log_mail', false);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public static function logOtp(string $purpose, string $code, array $context = []): void
    {
        if (! self::shouldLogOtp()) {
            return;
        }

        Log::channel('dev_otp')->info('[DEV OTP] '.$purpose, array_merge($context, ['code' => $code]));
    }

    private static function logOutgoingMail(MessageSending $event): void
    {
        $message = $event->message;
        $to = collect($message->getTo())
            ->map(fn ($address) => $address->getAddress())
            ->values()
            ->all();

        $text = $message->getTextBody();
        if ($text === null && $message->getHtmlBody() !== null) {
            $text = trim(strip_tags((string) $message->getHtmlBody()));
        }

        $body = Str::limit(trim(preg_replace('/\s+/u', ' ', $text ?? '') ?? ''), 4000);

        Log::channel('dev_mail')->info('[DEV MAIL]', [
            'to' => $to,
            'subject' => $message->getSubject() ?? '',
            'body' => $body,
        ]);
    }
}

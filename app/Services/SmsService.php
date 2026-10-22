<?php

namespace App\Services;

use App\Jobs\SendSmsJob;
use RuntimeException;
use Twilio\Rest\Client as TwilioClient;
use Vonage\Client;
use Vonage\Client\Credentials\Basic;
use Vonage\SMS\Message\SMS as VonageSmsMessage;

class SmsService
{
    public static function driver(): string
    {
        $driver = strtolower(trim((string) config('notifications.sms_driver', 'twilio')));

        return in_array($driver, ['twilio', 'vonage'], true) ? $driver : 'twilio';
    }

    public static function isConfigured(): bool
    {
        if (! config('notifications.sms_enabled')) {
            return false;
        }

        return self::isProviderConfigured();
    }

    public static function isProviderConfigured(): bool
    {
        return self::driver() === 'vonage'
            ? self::isVonageConfigured()
            : self::isTwilioConfigured();
    }

    /**
     * @return array{driver: string, driver_label: string, from: ?string}
     */
    public static function configurationSummary(): array
    {
        $driver = self::driver();

        return [
            'driver' => $driver,
            'driver_label' => $driver === 'vonage' ? 'Vonage (Nexmo)' : 'Twilio',
            'from' => $driver === 'vonage'
                ? self::vonageFrom()
                : (trim((string) config('services.twilio.from')) ?: null),
        ];
    }

    public function send(string $to, string $message): void
    {
        $to = preg_replace('/\s+/', '', trim($to)) ?? '';

        if ($to === '' || $message === '') {
            return;
        }

        if (! self::isConfigured()) {
            return;
        }

        SendSmsJob::dispatch($to, $message);
    }

    public function deliver(string $to, string $message): void
    {
        $to = preg_replace('/\s+/', '', trim($to)) ?? '';

        if ($to === '' || $message === '') {
            return;
        }

        $this->dispatchSend($to, $message);
    }

    /**
     * @throws RuntimeException
     */
    public function sendTest(string $to, string $message): void
    {
        $to = preg_replace('/\s+/', '', trim($to)) ?? '';

        if ($to === '' || $message === '') {
            throw new RuntimeException('Indiquez un numéro et un message.');
        }

        if (! self::isProviderConfigured()) {
            throw new RuntimeException('Le fournisseur SMS n’est pas configuré.');
        }

        $this->dispatchSend($to, $message);
    }

    private function dispatchSend(string $to, string $message): void
    {
        if (self::driver() === 'vonage') {
            $this->sendViaVonage($to, $message);

            return;
        }

        $this->sendViaTwilio($to, $message);
    }

    private function sendViaTwilio(string $to, string $message): void
    {
        $sid = config('services.twilio.account_sid');
        $token = config('services.twilio.auth_token');
        $from = config('services.twilio.from');

        $client = new TwilioClient($sid, $token);

        $client->messages->create(
            $to,
            [
                'from' => $from,
                'body' => $message,
            ]
        );
    }

    /**
     * @throws RuntimeException
     */
    private function sendViaVonage(string $to, string $message): void
    {
        $key = self::vonageKey();
        $secret = self::vonageSecret();
        $from = self::vonageFrom();

        $client = new Client(new Basic($key, $secret));
        $response = $client->sms()->send(new VonageSmsMessage($to, $from, $message));

        if (! $response->valid()) {
            throw new RuntimeException('Réponse Vonage invalide.');
        }

        $sent = $response->current();
        if ($sent->getStatus() !== 0) {
            $raw = $response->getAllMessagesRaw();
            $errorText = $raw['messages'][0]['error-text'] ?? null;

            throw new RuntimeException(
                filled($errorText)
                    ? (string) $errorText
                    : 'Échec Vonage (statut '.$sent->getStatus().').'
            );
        }
    }

    private static function isTwilioConfigured(): bool
    {
        $sid = trim((string) config('services.twilio.account_sid'));
        $token = trim((string) config('services.twilio.auth_token'));
        $from = trim((string) config('services.twilio.from'));

        if ($sid === '' || $token === '' || $from === '') {
            return false;
        }

        if (str_contains($sid, 'xxxx') || str_contains($token, 'xxxx') || str_contains($from, 'xxxx')) {
            return false;
        }

        return str_starts_with($sid, 'AC');
    }

    private static function isVonageConfigured(): bool
    {
        $key = self::vonageKey();
        $secret = self::vonageSecret();
        $from = self::vonageFrom();

        if ($key === '' || $secret === '' || $from === '') {
            return false;
        }

        foreach ([$key, $secret, $from] as $value) {
            if (str_contains($value, 'xxxx')) {
                return false;
            }
        }

        return true;
    }

    private static function vonageKey(): string
    {
        return trim((string) config('services.vonage.key'));
    }

    private static function vonageSecret(): string
    {
        return trim((string) config('services.vonage.secret'));
    }

    private static function vonageFrom(): string
    {
        return trim((string) config('services.vonage.sms_from'));
    }
}

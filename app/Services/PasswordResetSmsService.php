<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class PasswordResetSmsService
{
    private const CODE_TTL_MINUTES = 15;

    private const ISSUE_THROTTLE_SECONDS = 60;

    public function __construct(private SmsService $sms) {}

    /**
     * @return 'sent'|'throttled'|'unavailable'
     */
    public function sendResetCode(User $user): string
    {
        if (! config('notifications.sms_enabled')) {
            return 'unavailable';
        }

        $phone = $user->routeNotificationForTwilio();

        if (blank($phone)) {
            return 'unavailable';
        }

        $throttleKey = $this->issueThrottleKey($user);

        if (Cache::has($throttleKey)) {
            return 'throttled';
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put(
            $this->codeCacheKey($user),
            Hash::make($code),
            now()->addMinutes(self::CODE_TTL_MINUTES)
        );

        Cache::put($throttleKey, true, now()->addSeconds(self::ISSUE_THROTTLE_SECONDS));

        $this->sms->send(
            $phone,
            __('messages.forgot_password_sms_code_body', ['code' => $code])
        );

        return 'sent';
    }

    public function verifyCode(User $user, string $code): bool
    {
        $code = trim($code);
        $stored = Cache::get($this->codeCacheKey($user));

        if (! is_string($stored) || $code === '' || ! Hash::check($code, $stored)) {
            return false;
        }

        Cache::forget($this->codeCacheKey($user));

        return true;
    }

    private function codeCacheKey(User $user): string
    {
        return 'password_reset_sms:'.$user->id;
    }

    private function issueThrottleKey(User $user): string
    {
        return 'password_reset_sms_throttle:'.$user->id;
    }
}

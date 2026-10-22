<?php

namespace App\Services;

use App\Enums\LoginTwoFactorChannelEnum;
use App\Mail\LoginTwoFactorCodeMail;
use App\Models\User;
use App\Support\LoginTwoFactorSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class LoginTwoFactorService
{
    private const CODE_TTL_MINUTES = 10;

    private const ISSUE_THROTTLE_SECONDS = 60;

    public function __construct(private SmsService $sms) {}

    public function channelFor(User $user): ?LoginTwoFactorChannelEnum
    {
        if (! config('auth.two_factor.enabled', true)) {
            return null;
        }

        if (! $user->requiresLoginTwoFactor()) {
            return null;
        }

        if (filled($user->email)) {
            return LoginTwoFactorChannelEnum::Email;
        }

        if (filled($user->routeNotificationForTwilio()) && SmsService::isConfigured()) {
            return LoginTwoFactorChannelEnum::Sms;
        }

        return null;
    }

    public function requiresChallenge(User $user): bool
    {
        return $this->channelFor($user) !== null;
    }

    /**
     * @return 'sent'|'throttled'
     */
    public function sendChallenge(User $user, LoginTwoFactorChannelEnum $channel): string
    {
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

        if ($channel === LoginTwoFactorChannelEnum::Email) {
            Mail::to($user->email)->send(new LoginTwoFactorCodeMail($code));
        } else {
            $phone = $user->routeNotificationForTwilio();

            if (blank($phone)) {
                Cache::forget($this->codeCacheKey($user));

                return 'throttled';
            }

            $this->sms->send(
                (string) $phone,
                __('messages.login_two_factor_sms_code_body', ['code' => $code])
            );
        }

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

    public function beginChallenge(Request $request, User $user, bool $remember, string $loginIdentifier): LoginTwoFactorChannelEnum
    {
        $channel = $this->channelFor($user);

        if ($channel === null) {
            throw new \InvalidArgumentException('User does not require a login two-factor challenge.');
        }

        $request->session()->put(LoginTwoFactorSession::USER_ID, $user->id);
        $request->session()->put(LoginTwoFactorSession::REMEMBER, $remember);
        $request->session()->put(LoginTwoFactorSession::CHANNEL, $channel->value);
        $request->session()->put(LoginTwoFactorSession::LOGIN_IDENTIFIER, trim($loginIdentifier));

        $this->sendChallenge($user, $channel);

        return $channel;
    }

    public function pendingUserId(Request $request): ?int
    {
        $id = $request->session()->get(LoginTwoFactorSession::USER_ID);

        return is_int($id) ? $id : (is_numeric($id) ? (int) $id : null);
    }

    public function pendingChannel(Request $request): ?LoginTwoFactorChannelEnum
    {
        $value = $request->session()->get(LoginTwoFactorSession::CHANNEL);

        if (! is_string($value)) {
            return null;
        }

        return LoginTwoFactorChannelEnum::tryFrom($value);
    }

    public function rememberFromSession(Request $request): bool
    {
        return (bool) $request->session()->get(LoginTwoFactorSession::REMEMBER, false);
    }

    public function clearPending(Request $request): void
    {
        $request->session()->forget([
            LoginTwoFactorSession::USER_ID,
            LoginTwoFactorSession::REMEMBER,
            LoginTwoFactorSession::CHANNEL,
            LoginTwoFactorSession::LOGIN_IDENTIFIER,
        ]);
    }

    /**
     * @return array{kind: string, email?: string, phone?: string}|null
     */
    public function maskedDestination(User $user, LoginTwoFactorChannelEnum $channel): ?array
    {
        if ($channel === LoginTwoFactorChannelEnum::Email && filled($user->email)) {
            return [
                'kind' => 'email',
                'email' => User::maskEmailForDisplay($user->email),
            ];
        }

        $phone = $user->routeNotificationForTwilio();

        if ($channel === LoginTwoFactorChannelEnum::Sms && filled($phone)) {
            return [
                'kind' => 'sms',
                'phone' => User::maskPhoneForDisplay((string) $phone),
            ];
        }

        return null;
    }

    private function codeCacheKey(User $user): string
    {
        return 'login_two_factor:'.$user->id;
    }

    private function issueThrottleKey(User $user): string
    {
        return 'login_two_factor_throttle:'.$user->id;
    }
}

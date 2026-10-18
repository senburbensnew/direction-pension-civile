<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\Telephone;
use App\Services\PasswordResetSmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request, PasswordResetSmsService $smsReset): RedirectResponse
    {
        if (! $request->filled('login') && $request->filled('email')) {
            $request->merge(['login' => $request->input('email')]);
        }

        $request->validate([
            'login' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $value = trim((string) $value);
                    if ($value !== '' && str_starts_with($value, '+')) {
                        (new Telephone)->validate($attribute, $value, $fail);
                    }
                },
            ],
        ]);

        $login = trim((string) $request->input('login'));
        $user = User::findForLogin($login);

        if (! $user) {
            return back()
                ->withInput($request->only('login'))
                ->withErrors(['login' => __(Password::INVALID_USER)]);
        }

        $canEmail = filled($user->email);
        $canSms = $this->smsChannelAvailable($user);

        if (! $canEmail && ! $canSms) {
            return back()
                ->withInput($request->only('login'))
                ->withErrors(['login' => __(Password::INVALID_USER)]);
        }

        $emailSent = false;
        $smsSent = false;
        $throttled = false;

        if ($canEmail) {
            $emailStatus = Password::sendResetLink(['email' => $user->email]);

            if ($emailStatus === Password::RESET_LINK_SENT) {
                $emailSent = true;
            } elseif ($emailStatus === Password::RESET_THROTTLED) {
                $throttled = true;
            } elseif (! $canSms) {
                return back()
                    ->withInput($request->only('login'))
                    ->withErrors(['login' => __($emailStatus)]);
            }
        }

        if ($canSms) {
            $smsStatus = $smsReset->sendResetCode($user);

            if ($smsStatus === 'sent') {
                $smsSent = true;
            } elseif ($smsStatus === 'throttled') {
                $throttled = true;
            }
        }

        if ($emailSent || $smsSent) {
            $notice = $this->successNotice($emailSent, $smsSent, $user);

            if ($notice !== null) {
                return back()->with('password_reset_notice', $notice);
            }

            return back()->with('status', __(Password::RESET_LINK_SENT));
        }

        if ($throttled) {
            return back()
                ->withInput($request->only('login'))
                ->withErrors(['login' => __(Password::RESET_THROTTLED)]);
        }

        return back()
            ->withInput($request->only('login'))
            ->withErrors(['login' => __(Password::INVALID_USER)]);
    }

    private function smsChannelAvailable(User $user): bool
    {
        return config('notifications.sms_enabled')
            && filled($user->routeNotificationForTwilio());
    }

    /**
     * @return array{kind: string, email?: string, phone?: string}|null
     */
    private function successNotice(bool $emailSent, bool $smsSent, User $user): ?array
    {
        $maskedEmail = filled($user->email) ? User::maskEmailForDisplay($user->email) : null;
        $maskedPhone = filled($user->routeNotificationForTwilio())
            ? User::maskPhoneForDisplay($user->routeNotificationForTwilio())
            : null;

        if ($emailSent && $smsSent && $maskedEmail && $maskedPhone) {
            return [
                'kind' => 'both',
                'email' => $maskedEmail,
                'phone' => $maskedPhone,
            ];
        }

        if ($smsSent && $maskedPhone) {
            return [
                'kind' => 'sms',
                'phone' => $maskedPhone,
            ];
        }

        if ($emailSent && $maskedEmail) {
            return [
                'kind' => 'email',
                'email' => $maskedEmail,
            ];
        }

        return null;
    }
}

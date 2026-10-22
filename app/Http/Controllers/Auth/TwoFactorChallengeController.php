<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\Concerns\RedirectsAfterAuthentication;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\UserLoginStatisticsService;
use App\Services\LoginTwoFactorService;
use App\Support\LoginTwoFactorSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TwoFactorChallengeController extends Controller
{
    use RedirectsAfterAuthentication;

    public function create(Request $request, LoginTwoFactorService $twoFactor): View|RedirectResponse
    {
        $user = $this->resolvePendingUser($request, $twoFactor);

        if (! $user) {
            return redirect()->route('login');
        }

        $channel = $twoFactor->pendingChannel($request);

        if ($channel === null) {
            $twoFactor->clearPending($request);

            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge', [
            'destination' => $twoFactor->maskedDestination($user, $channel),
            'channel' => $channel,
        ]);
    }

    public function store(
        Request $request,
        LoginTwoFactorService $twoFactor,
        UserLoginStatisticsService $loginStats,
    ): RedirectResponse {
        $user = $this->resolvePendingUser($request, $twoFactor);

        if (! $user) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => ['required', 'string', 'regex:/^\d{6}$/'],
        ]);

        $this->ensureCodeAttemptIsNotRateLimited($request, $user);

        if (! $twoFactor->verifyCode($user, (string) $request->input('code'))) {
            RateLimiter::hit($this->codeThrottleKey($user));

            return back()
                ->withErrors(['code' => __('messages.login_two_factor_code_invalid')]);
        }

        RateLimiter::clear($this->codeThrottleKey($user));
        $this->clearLoginRateLimit($request);

        $remember = $twoFactor->rememberFromSession($request);
        $twoFactor->clearPending($request);

        Auth::login($user, $remember);

        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        $loginStats->recordFromRequest($user->id, $request);

        return $this->redirectAfterAuthentication($request);
    }

    public function resend(Request $request, LoginTwoFactorService $twoFactor): RedirectResponse
    {
        $user = $this->resolvePendingUser($request, $twoFactor);

        if (! $user) {
            return redirect()->route('login');
        }

        $channel = $twoFactor->pendingChannel($request);

        if ($channel === null) {
            $twoFactor->clearPending($request);

            return redirect()->route('login');
        }

        $status = $twoFactor->sendChallenge($user, $channel);

        if ($status === 'throttled') {
            return back()->with('warning', __('messages.login_two_factor_resend_throttled'));
        }

        return back()->with('status', __('messages.login_two_factor_code_resent'));
    }

    private function resolvePendingUser(Request $request, LoginTwoFactorService $twoFactor): ?User
    {
        $userId = $twoFactor->pendingUserId($request);

        if ($userId === null) {
            return null;
        }

        $user = User::query()->find($userId);

        if (! $user || ! $user->allowsLogin()) {
            $twoFactor->clearPending($request);

            return null;
        }

        if ($twoFactor->channelFor($user) !== $twoFactor->pendingChannel($request)) {
            $twoFactor->clearPending($request);

            return null;
        }

        return $user;
    }

    private function ensureCodeAttemptIsNotRateLimited(Request $request, User $user): void
    {
        if (! RateLimiter::tooManyAttempts($this->codeThrottleKey($user), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->codeThrottleKey($user));

        abort(429, trans('auth.throttle', [
            'seconds' => $seconds,
            'minutes' => ceil($seconds / 60),
        ]));
    }

    private function codeThrottleKey(User $user): string
    {
        return Str::transliterate('login-two-factor|'.$user->id);
    }

    private function clearLoginRateLimit(Request $request): void
    {
        $login = $request->session()->get(LoginTwoFactorSession::LOGIN_IDENTIFIER);

        if (! is_string($login) || trim($login) === '') {
            return;
        }

        RateLimiter::clear(Str::transliterate(Str::lower(trim($login)).'|'.$request->ip()));
    }
}

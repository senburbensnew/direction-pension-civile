<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserSessionEndReasonEnum;
use App\Http\Controllers\Auth\Concerns\RedirectsAfterAuthentication;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Admin\UserLoginStatisticsService;
use App\Services\Admin\UserSessionEndService;
use App\Services\LoginTwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    use RedirectsAfterAuthentication;

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(
        LoginRequest $request,
        UserLoginStatisticsService $loginStats,
        LoginTwoFactorService $twoFactor,
    ): RedirectResponse {
        $user = $request->validatedUser();

        if ($twoFactor->requiresChallenge($user)) {
            $twoFactor->beginChallenge(
                $request,
                $user,
                $request->boolean('remember'),
                (string) $request->input('login'),
            );

            return redirect()->route('login.two-factor');
        }

        $request->authenticate();

        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        $loginStats->recordFromRequest($user->id, $request);

        return $this->redirectAfterAuthentication($request);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request, UserSessionEndService $sessionEnds): RedirectResponse
    {
        $userId = $request->user()?->id;
        $sessionId = $request->session()->getId();

        if (is_int($userId)) {
            $sessionEnds->recordFromRequest(
                $userId,
                UserSessionEndReasonEnum::VoluntaryLogout,
                $request,
                sessionId: is_string($sessionId) ? $sessionId : null,
            );
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

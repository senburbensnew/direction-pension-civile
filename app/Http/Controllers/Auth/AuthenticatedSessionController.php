<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use App\Enums\UserSessionEndReasonEnum;
use App\Services\Admin\UserLoginStatisticsService;
use App\Services\Admin\UserSessionEndService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
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
    public function store(LoginRequest $request, UserLoginStatisticsService $loginStats): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        if ($request->user()) {
            $loginStats->recordFromRequest($request->user()->id, $request);
        }

        if ($request->user()?->mustChangePassword()) {
            return redirect()->route('password.required');
        }

        if ($request->user()?->hasRole('admin')) {
            return redirect()->route('admin.dashboard.index');
        }

        return redirect()->to(RouteServiceProvider::HOME);
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

        return redirect(RouteServiceProvider::HOME);
    }
}

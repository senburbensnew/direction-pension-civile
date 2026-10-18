<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\ActiveSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActiveSessionController extends Controller
{
    public function index(Request $request, ActiveSessionService $sessions): View
    {
        $authenticatedOnly = ! $request->boolean('include_guests');
        $search = $request->string('q')->trim()->toString();
        $userId = $request->filled('user_id') ? (int) $request->input('user_id') : null;

        $activeSessions = $sessions->list(
            search: $search !== '' ? $search : null,
            userId: $userId,
            authenticatedOnly: $authenticatedOnly,
        );

        $uniqueUserCount = $activeSessions
            ->pluck('user_id')
            ->filter()
            ->unique()
            ->count();

        return view('admin.sessions.index', [
            'sessions' => $activeSessions,
            'driver' => $sessions->driver(),
            'isManageable' => $sessions->isManageable(),
            'lifetimeMinutes' => $sessions->lifetimeMinutes(),
            'currentSessionId' => $request->session()->getId(),
            'stats' => [
                'session_count' => $activeSessions->count(),
                'user_count' => $uniqueUserCount,
            ],
            'users' => User::query()
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
        ]);
    }

    public function destroy(Request $request, string $session, ActiveSessionService $sessions): RedirectResponse
    {
        if ($session === $request->session()->getId()) {
            return $this->redirectAfterAction($request)
                ->with('error', __('messages.admin_sessions.cannot_revoke_current'));
        }

        if (! $sessions->destroy($session)) {
            return $this->redirectAfterAction($request)
                ->with('error', __('messages.admin_sessions.revoke_failed'));
        }

        return $this->redirectAfterAction($request)
            ->with('success', __('messages.admin_sessions.revoke_success'));
    }

    public function destroyUser(Request $request, User $user, ActiveSessionService $sessions): RedirectResponse
    {
        $exceptCurrent = $user->is($request->user())
            ? $request->session()->getId()
            : null;

        $removed = $sessions->destroyForUser($user->id, $exceptCurrent);

        if ($removed === 0) {
            return $this->redirectAfterAction($request, [
                'user_id' => $user->id,
            ])->with('error', __('messages.admin_sessions.user_revoke_none'));
        }

        return $this->redirectAfterAction($request)
            ->with('success', __('messages.admin_sessions.user_revoke_success', ['count' => $removed]));
    }

    protected function redirectAfterAction(Request $request, array $sessionIndexQuery = []): RedirectResponse
    {
        $intended = $request->input('redirect_to');
        if (is_string($intended) && str_starts_with($intended, url('/admin'))) {
            return redirect()->to($intended);
        }

        if ($request->headers->get('referer') && str_contains((string) $request->headers->get('referer'), '/admin/')) {
            return redirect()->back();
        }

        return redirect()->route('admin.sessions.index', $sessionIndexQuery);
    }
}

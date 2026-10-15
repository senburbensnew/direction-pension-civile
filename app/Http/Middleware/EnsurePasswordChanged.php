<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    /** @var list<string> */
    private const ALLOWED_ROUTE_NAMES = [
        'password.required',
        'password.required.update',
        'logout',
        'locale',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->mustChangePassword()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if ($routeName !== null && in_array($routeName, self::ALLOWED_ROUTE_NAMES, true)) {
            return $next($request);
        }

        return redirect()
            ->route('password.required')
            ->with('warning', __('guest.password_required.notice'));
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\Routing\UrlGenerator;

class RequirePasswordConfirmation extends RequirePassword
{
    public function __construct(ResponseFactory $responseFactory, UrlGenerator $urlGenerator)
    {
        parent::__construct(
            $responseFactory,
            $urlGenerator,
            (int) config('auth.password_timeout', 900)
        );
    }

    public function handle($request, Closure $next, $redirectToRoute = null, $passwordTimeoutSeconds = null)
    {
        if ($this->shouldConfirmPassword($request, $passwordTimeoutSeconds)) {
            if ($request->expectsJson()) {
                return $this->responseFactory->json([
                    'message' => __('guest.confirm_password.json_required'),
                ], 423);
            }

            $request->session()->flash('password_reauth_required', true);

            return $this->responseFactory->redirectGuest(
                $this->urlGenerator->route($redirectToRoute ?: 'password.confirm')
            );
        }

        return $next($request);
    }
}

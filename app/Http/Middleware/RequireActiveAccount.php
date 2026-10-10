<?php

namespace App\Http\Middleware;

use App\Enums\AccountStatusEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Accès réservé aux comptes permanents (account_status = actif).
 * Les comptes provisoires sont redirigés vers l'espace limité.
 */
class RequireActiveAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if ($user->resolvedAccountStatus() === AccountStatusEnum::ACTIF) {
            return $next($request);
        }

        return redirect()
            ->route('compte.provisoire')
            ->with(
                'warning',
                'Votre compte est encore en attente de validation. Cette fonctionnalité est réservée aux comptes actifs.'
            );
    }
}

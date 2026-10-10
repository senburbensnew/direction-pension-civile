<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Accès portail pensionné : titulaire, mandataire ou représentant d'un bénéficiaire.
 */
class EnsurePensionPortalAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->hasPensionPortalAccess()) {
            abort(403, 'Accès réservé aux usagers du portail pension.');
        }

        return $next($request);
    }
}

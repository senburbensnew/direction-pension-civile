<?php

namespace App\Http\Middleware;

use App\Services\RencontreMotifService;
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

        $motifs = app(RencontreMotifService::class);

        if (! $user?->hasPensionPortalAccess()
            && ! $motifs->userHasPendingDemandeCreationCompte($user)) {
            abort(403, 'Accès réservé aux usagers du portail pension.');
        }

        return $next($request);
    }
}

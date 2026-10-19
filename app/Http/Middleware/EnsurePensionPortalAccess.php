<?php

namespace App\Http\Middleware;

use App\Services\RencontreMotifService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Accès portail pension : compte actif, titulaire, mandataire, représentant ou demande en cours.
 */
class EnsurePensionPortalAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $motifs = app(RencontreMotifService::class);

        if (! $motifs->userCanBookPortalRencontre($user)) {
            abort(403, 'Accès réservé aux pensionnés, mandataires et représentants légaux.');
        }

        return $next($request);
    }
}

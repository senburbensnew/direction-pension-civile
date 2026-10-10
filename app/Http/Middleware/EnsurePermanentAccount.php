<?php

namespace App\Http\Middleware;

use App\Enums\AccountStatusEnum;
use App\Models\User;
use App\Services\RencontreMotifService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restreint les comptes non actifs (provisoire, bloqué, désactivé).
 *
 * Compte provisoire — autorisé :
 * - connexion (LoginRequest + allowsLogin)
 * - consultation des demandes (espace personal)
 * - compléments / pièces sur ses demandes en cours
 * - notifications
 * - prise de rendez-vous (pensionné)
 *
 * Compte provisoire — refusé :
 * - démarches métier (virements, attestations, etc.)
 * - profil / modification d'identité
 * - corbeille agent, PDF officiels annotés, etc.
 */
class EnsurePermanentAccount
{
    /** @var list<string> */
    private const PROVISIONNEL_ROUTES = [
        'compte.provisoire',
        'compte.demande.show',
        'compte.demande.update',
        'personal.dashboard',
        'personal.requests-dashboard',
        'personal.request.show',
        'personal.request.authenticated-user-request.show',
        'demande.repondre-complement',
        'demandedocument.store',
        'demandedocument.destroy',
        'notifications.index',
        'notifications.markAsRead',
        'notifications.open',
        'notifications.markAllAsRead',
        'notifications.destroy',
        'locale',
    ];

    /** @var list<string> */
    private const PROVISIONNEL_PENSIONNE_ROUTES = [
        'demandes.rencontre.create',
        'demandes.rencontre.store',
        'demandes.rencontre.physique',
        'demandes.rencontre.annuler',
        'demandes.rencontre.visio',
        'demandes.rencontre.visio.status',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if ($user->resolvedAccountStatus() === AccountStatusEnum::ACTIF) {
            return $next($request);
        }

        if ($request->route()?->getName() === 'compte.provisoire') {
            return $next($request);
        }

        if ($user->resolvedAccountStatus() === AccountStatusEnum::PROVISOIRE
            && $this->allowsProvisionnelAccess($request, $user)) {
            return $next($request);
        }

        return redirect()
            ->route('compte.provisoire')
            ->with(
                'warning',
                $this->warningMessage($user)
            );
    }

    private function warningMessage(User $user): string
    {
        if ($user->resolvedAccountStatus() === AccountStatusEnum::BLOQUE) {
            return 'Votre demande de compte a été refusée. Votre accès est limité.';
        }

        if ($user->resolvedAccountStatus() === AccountStatusEnum::DESACTIVE) {
            return 'Votre compte est désactivé. Veuillez contacter la Direction.';
        }

        $canRdv = $user->hasPensionPortalAccess()
            || app(RencontreMotifService::class)->userHasPendingDemandeCreationCompte($user);

        return 'Votre compte est en attente de validation. Seules la consultation de vos demandes'
            .($canRdv ? ', les notifications et la prise de rendez-vous' : ' et les notifications')
            .' sont disponibles pour le moment.';
    }

    private function allowsProvisionnelAccess(Request $request, User $user): bool
    {
        $routeName = $request->route()?->getName();

        if ($routeName === null) {
            return false;
        }

        if (in_array($routeName, self::PROVISIONNEL_ROUTES, true)) {
            return true;
        }

        if (! in_array($routeName, self::PROVISIONNEL_PENSIONNE_ROUTES, true)) {
            return false;
        }

        return $user->hasPensionPortalAccess()
            || app(RencontreMotifService::class)->userHasPendingDemandeCreationCompte($user);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        /*
        |--------------------------------------------------------------------------
        | Routes toujours accessibles
        |--------------------------------------------------------------------------
        |
        | Même en mode maintenance, ces routes restent accessibles :
        | - administration
        | - authentification
        | - déconnexion
        | - assets Vite
        |
        */

        if ($this->shouldBypassMaintenance($request)) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Vérification du mode maintenance
        |--------------------------------------------------------------------------
        */

        $isMaintenanceMode = Cache::remember(
            'is_maintenance_mode',
            60,
            function () {
                return DB::table('parameters')
                    ->where('name', 'is_maintenance_mode')
                    ->value('value') === 'true';
            }
        );

        if ($isMaintenanceMode) {
            return response()->view('maintenance', [], 503);
        }

        return $next($request);
    }

    /**
     * Détermine si la requête doit être exclue
     * du mode maintenance.
     */
    private function shouldBypassMaintenance(Request $request): bool
    {
        return
            // Administration
            $request->is('admin')
            || $request->is('admin/*')

            // Authentification
            || $request->is('login')
            || $request->is('logout')
            || $request->is('password/*')

            // Assets Vite
            || $request->is('build/*')

            // Assets publics éventuels
            || $request->is('images/*')
            || $request->is('storage/*')

            // Favicon
            || $request->is('favicon.ico');
    }
}
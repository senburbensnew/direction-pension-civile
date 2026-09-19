<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorbeilleAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        if (! auth()->user()->hasAnyRole([
            'secretariat',
            'direction',
            'directeur',
            'assistant_directeur',
            'service_liquidation',
            'service_accueil_formalites',
            'service_controle_placement',
            'service_comptabilite',
            'service_assurance',
            'administration',
            'admin',
        ])) {
            abort(403);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountFullyActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isProvisionnel()) {
            return redirect()
                ->route('demandes.rencontre.create')
                ->with('error', 'Votre compte est provisoire : vous pouvez uniquement prendre rendez-vous jusqu’à la validation.');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorbeilleAccess
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->user()->hasAnyRole(RolesAndPermissionsSeeder::corbeilleAccessRoles())) {
            abort(403);
        }

        return $next($request);
    }
}

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
        // Admin routes and login are always accessible
        if ($request->is('admin/*') || $request->is('login') || $request->is('logout')) {
            return $next($request);
        }

        $isMaintenanceMode = Cache::remember('is_maintenance_mode', 60, function () {
            return DB::table('parameters')
                ->where('name', 'is_maintenance_mode')
                ->value('value') === 'true';
        });

        if ($isMaintenanceMode) {
            return response()->view('maintenance', [], 503);
        }

        return $next($request);
    }
}

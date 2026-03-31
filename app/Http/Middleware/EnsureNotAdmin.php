<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureNotAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()?->hasRole('admin')) {
            return redirect()->route('admin.dashboard.index')
                ->with('error', 'Les administrateurs n\'ont pas accès à cette section.');
        }

        return $next($request);
    }
}

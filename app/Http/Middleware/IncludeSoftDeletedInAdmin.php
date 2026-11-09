<?php

namespace App\Http\Middleware;

use App\Support\Eloquent\IncludeSoftDeleted;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IncludeSoftDeletedInAdmin
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        IncludeSoftDeleted::enable();

        try {
            return $next($request);
        } finally {
            IncludeSoftDeleted::disable();
        }
    }
}

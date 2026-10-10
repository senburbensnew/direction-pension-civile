<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequestLogger
{
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);

        $response = $next($request);

        $duration = round(
            (microtime(true) - $start) * 1000,
            2
        );

        Log::info('HTTP Request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'route' => $request->route()?->getName(),
            'status' => $response->getStatusCode(),
            'ip' => $request->ip(),
            'user_id' => $request->user()?->id,
            'duration_ms' => $duration,
        ]);

        return $response;
    }
}
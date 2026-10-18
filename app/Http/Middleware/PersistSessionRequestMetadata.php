<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Stocke IP et user-agent dans la session (nécessaire avec SESSION_DRIVER=file
 * pour l'écran admin « Sessions actives »).
 */
class PersistSessionRequestMetadata
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->hasSession()) {
            $session = $request->session();

            $ip = $request->ip();
            if (is_string($ip) && $ip !== '') {
                $session->put('_ip_address', $ip);
            }

            $userAgent = $request->userAgent();
            if (is_string($userAgent) && $userAgent !== '') {
                $session->put('_user_agent', Str::limit($userAgent, 512, ''));
            }
        }

        return $next($request);
    }
}

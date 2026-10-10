<?php

namespace App\Http\Middleware;

use App\Services\Pension\ActivePensionContext;
use App\Services\Pension\BeneficiaireProvisioningService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePensionContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->canAccessFullServices()) {
            app(BeneficiaireProvisioningService::class)->ensureForUser($user);
            ActivePensionContext::for($user->fresh())->ensureDefaults();
        }

        return $next($request);
    }
}

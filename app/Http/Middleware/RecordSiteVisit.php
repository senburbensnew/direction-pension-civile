<?php

namespace App\Http\Middleware;

use App\Services\SiteVisitService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RecordSiteVisit
{
    public function __construct(private SiteVisitService $visits)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $this->visits->record($request);
        } catch (Throwable $e) {
            report($e);
        }

        return $next($request);
    }
}

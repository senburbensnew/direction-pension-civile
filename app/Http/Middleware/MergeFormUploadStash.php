<?php

namespace App\Http\Middleware;

use App\Services\Form\FormUploadStashService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MergeFormUploadStash
{
    public function __construct(
        private FormUploadStashService $stash
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('POST')) {
            $this->stash->mergeIntoRequest($request);
        }

        /** @var Response $response */
        $response = $next($request);

        if (
            $request->isMethod('POST')
            && $request->hasSession()
            && !$request->session()->has('errors')
        ) {
            $this->stash->clearForRequest($request);
        }

        return $response;
    }
}

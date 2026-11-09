<?php

namespace Tests\Support;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

trait InteractsWithMiddleware
{
    /**
     * @return Closure(Request): Response
     */
    protected function middlewareNext(string $content = 'ok', int $status = 200): Closure
    {
        return static function (Request $request) use ($content, $status): Response {
            return response($content, $status);
        };
    }

    protected function httpRequest(string $uri = '/', string $method = 'GET'): Request
    {
        $request = Request::create($uri, $method);

        if ($this->app->bound('session.store')) {
            $session = $this->app->make('session.store');
            $session->start();
            $request->setLaravelSession($session);
        }

        return $request;
    }

    protected function runMiddleware(Request $request, object $middleware, mixed $nextResponse = 'next'): Response
    {
        $next = static function (Request $passed) use ($nextResponse): Response {
            if ($nextResponse instanceof Response) {
                return $nextResponse;
            }

            return response((string) $nextResponse, 200);
        };

        /** @var Response $response */
        $response = $middleware->handle($request, $next);

        return $response;
    }
}

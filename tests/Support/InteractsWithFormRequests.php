<?php

namespace Tests\Support;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

trait InteractsWithFormRequests
{
    /**
     * @param  class-string<FormRequest>  $class
     */
    protected function makeFormRequest(string $class, string $uri = '/', string $method = 'POST', array $payload = []): FormRequest
    {
        $base = Request::create($uri, $method, $payload);
        /** @var FormRequest $request */
        $request = $class::createFrom($base);
        $request->setContainer($this->app);
        $request->setRedirector($this->app->make('redirect'));

        return $request;
    }
}

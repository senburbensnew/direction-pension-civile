<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\App;
use Tests\Support\InteractsWithMiddleware;
use Tests\TestCase;

class SetLocaleTest extends TestCase
{
    use InteractsWithMiddleware;

    /** @test */
    public function handle_sets_supported_locale_from_cookie(): void
    {
        $request = $this->httpRequest();
        $this->app['session.store']->forget('locale');
        $request->cookies->set('locale', 'ht');

        $this->runMiddleware($request, app(SetLocale::class));

        $this->assertSame('ht', App::getLocale());
    }

    /** @test */
    public function handle_falls_back_to_default_when_locale_is_unsupported(): void
    {
        $request = $this->httpRequest();
        $this->app['session.store']->forget('locale');
        $request->cookies->set('locale', 'en');

        $this->runMiddleware($request, app(SetLocale::class));

        $this->assertSame('fr', App::getLocale());
        $this->assertSame('fr', $this->app['session.store']->get('locale'));
    }
}

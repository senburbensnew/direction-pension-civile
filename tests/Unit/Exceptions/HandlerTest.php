<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\Handler;
use App\Services\Form\FormUploadStashService;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class HandlerTest extends TestCase
{
    private Handler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = app(Handler::class);
    }

    /** @test */
    public function sensitive_password_fields_are_not_flashed(): void
    {
        $property = new \ReflectionProperty(Handler::class, 'dontFlash');
        $property->setAccessible(true);
        /** @var list<string> $dontFlash */
        $dontFlash = $property->getValue($this->handler);

        $this->assertContains('password', $dontFlash);
        $this->assertContains('current_password', $dontFlash);
        $this->assertContains('password_confirmation', $dontFlash);
    }

    /** @test */
    public function post_too_large_renders_back_with_french_error(): void
    {
        $request = Request::create('/contact', 'POST');
        $request->setLaravelSession($this->app['session.store']);

        $response = $this->handler->render($request, new PostTooLargeException);

        $this->assertTrue($response->isRedirect());
        $this->assertStringContainsString(
            '20 Mo',
            session('errors')?->first('system_error') ?? ''
        );
    }

    /** @test */
    public function render_applies_locale_from_cookie(): void
    {
        $request = Request::create('/', 'GET');
        $request->cookies->set('locale', 'ht');
        $request->setLaravelSession($this->app['session.store']);

        $this->handler->render($request, new NotFoundHttpException);

        $this->assertSame('ht', app()->getLocale());
        $this->assertSame('ht', session('locale'));
    }

    /** @test */
    public function render_ignores_unsupported_locale_cookie(): void
    {
        $request = Request::create('/', 'GET');
        $request->cookies->set('locale', 'en');
        $request->setLaravelSession($this->app['session.store']);

        $this->handler->render($request, new NotFoundHttpException);

        $this->assertSame('fr', app()->getLocale());
        $this->assertSame('fr', session('locale'));
    }

    /** @test */
    public function validation_exception_stashes_uploads_on_post(): void
    {
        $this->mock(FormUploadStashService::class, function ($mock): void {
            $mock->shouldReceive('stashFromRequest')->once();
        });

        $request = Request::create('/demande', 'POST');
        $request->setLaravelSession($this->app['session.store']);

        $exception = ValidationException::withMessages(['field' => 'invalid']);

        $this->handler->render($request, $exception);
    }
}

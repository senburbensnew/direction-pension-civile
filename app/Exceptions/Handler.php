<?php

namespace App\Exceptions;

use App\Services\Form\FormUploadStashService;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Validation\ValidationException;
use Throwable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (PostTooLargeException $e, $request) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'system_error' => 'La taille totale des fichiers dépasse la limite autorisée de 20 Mo'
                ]);
        });

        $this->renderable(function (ValidationException $e, $request) {
            if ($request->hasSession() && $request->isMethod('POST')) {
                app(FormUploadStashService::class)->stashFromRequest($request);
            }

            return null;
        });
    }

    public function render($request, Throwable $exception)
    {
        $locale = $request->cookie('locale', config('app.locale'));
        App::setLocale($locale);

        // Optional: Sync session if available (only for web requests)
        if ($request->hasSession()) {
            Session::put('locale', $locale);
        }

        return parent::render($request, $exception);
    }
}

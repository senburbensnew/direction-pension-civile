<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RequiredPasswordChangeController;
use App\Http\Controllers\Auth\ResetPasswordWithCodeController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| AUTHENTICATION ROUTES
|--------------------------------------------------------------------------
| Included from routes/web.php (middleware web).
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::controller(AuthenticatedSessionController::class)->group(function () {
        Route::get('login', 'create')->name('login');
        Route::post('login', 'store');
    });

    Route::prefix('login')->name('login.')->controller(TwoFactorChallengeController::class)->group(function () {
        Route::get('/two-factor', 'create')->name('two-factor');
        Route::post('/two-factor', 'store')->middleware('throttle:6,1')->name('two-factor.store');
        Route::post('/two-factor/resend', 'resend')->middleware('throttle:3,1')->name('two-factor.resend');
    });

    Route::controller(PasswordResetLinkController::class)->group(function () {
        Route::get('forgot-password', 'create')->name('password.request');
        Route::post('forgot-password', 'store')->name('password.email');
    });

    Route::controller(NewPasswordController::class)->group(function () {
        Route::get('reset-password/{token}', 'create')->name('password.reset');
        Route::post('reset-password', 'store')->name('password.store');
    });

    Route::controller(ResetPasswordWithCodeController::class)->group(function () {
        Route::get('reset-password-code', 'create')->name('password.reset.code');
        Route::post('reset-password-code', 'store')->name('password.reset.code.store');
    });
});

Route::middleware('auth')->group(function () {
    Route::controller(RequiredPasswordChangeController::class)->group(function () {
        Route::get('password/required', 'create')->name('password.required');
        Route::put('password/required', 'update')->name('password.required.update');
    });

    Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::controller(ConfirmablePasswordController::class)->group(function () {
        Route::get('confirm-password', 'show')->name('password.confirm');
        Route::post('confirm-password', 'store');
    });

    Route::put('password', [PasswordController::class, 'update'])
        ->middleware('password.confirm')
        ->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

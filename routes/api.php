<?php

use App\Http\Controllers\Api\CurrentUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Loaded with middleware api + prefix /api (RouteServiceProvider).
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->controller(CurrentUserController::class)->group(function () {
    Route::get('/user', 'show');
});

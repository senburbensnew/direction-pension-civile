<?php

namespace App\Http\Controllers\Auth\Concerns;

use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait RedirectsAfterAuthentication
{
    protected function redirectAfterAuthentication(Request $request): RedirectResponse
    {
        if ($request->user()?->mustChangePassword()) {
            return redirect()->route('password.required');
        }

        if ($request->user()?->hasRole('admin')) {
            return redirect()->route('admin.dashboard.index');
        }

        return redirect()->to(RouteServiceProvider::HOME);
    }
}

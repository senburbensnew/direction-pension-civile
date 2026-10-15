<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RequiredPasswordChangeController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->user()?->mustChangePassword()) {
            return redirect()->to(RouteServiceProvider::HOME);
        }

        return view('auth.required-password-change');
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user?->mustChangePassword()) {
            return redirect()->to(RouteServiceProvider::HOME);
        }

        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
        ]);

        return redirect()
            ->to(RouteServiceProvider::HOME)
            ->with('status', 'password-updated');
    }
}

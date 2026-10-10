<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\Telephone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        if (! $request->filled('login') && $request->filled('email')) {
            $request->merge(['login' => $request->input('email')]);
        }

        $request->validate([
            'login' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $value = trim((string) $value);
                    if ($value !== '' && str_starts_with($value, '+')) {
                        (new Telephone)->validate($attribute, $value, $fail);
                    }
                },
            ],
        ]);

        $user = User::findForLogin((string) $request->input('login'));

        if (! $user || blank($user->email)) {
            return back()
                ->withInput($request->only('login'))
                ->withErrors(['login' => __(Password::INVALID_USER)]);
        }

        $status = Password::sendResetLink(['email' => $user->email]);

        return $status == Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withInput($request->only('login'))
                ->withErrors(['login' => __($status)]);
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\Telephone;
use App\Services\PasswordResetSmsService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class ResetPasswordWithCodeController extends Controller
{
    public function create(): View
    {
        return view('auth.reset-password-code');
    }

    public function store(Request $request, PasswordResetSmsService $smsReset): RedirectResponse
    {
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
            'code' => ['required', 'string', 'digits:6'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::findForLogin(trim((string) $request->input('login')));

        if (! $user || ! $smsReset->verifyCode($user, (string) $request->input('code'))) {
            return back()
                ->withInput($request->only('login'))
                ->withErrors(['code' => __('messages.password_reset_code_invalid')]);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        return redirect()
            ->route('login')
            ->with('status', __('passwords.reset'));
    }
}

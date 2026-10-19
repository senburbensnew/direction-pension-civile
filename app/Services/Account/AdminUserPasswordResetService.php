<?php

namespace App\Services\Account;

use App\Enums\UserSessionEndReasonEnum;
use App\Mail\AdminUserTemporaryPasswordMail;
use App\Models\User;
use App\Services\Admin\ActiveSessionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminUserPasswordResetService
{
    public function resetAndNotify(User $user, User $admin): void
    {
        if ($user->id === $admin->id) {
            throw ValidationException::withMessages([
                'user' => 'Utilisez votre profil pour changer votre propre mot de passe.',
            ]);
        }

        if (blank($user->email)) {
            throw ValidationException::withMessages([
                'email' => 'Cet utilisateur n’a pas d’adresse e-mail : impossible d’envoyer le mot de passe.',
            ]);
        }

        $plainPassword = Str::password(12, symbols: true);

        DB::transaction(function () use ($user, $plainPassword, $admin): void {
            $user->update([
                'password' => $plainPassword,
                'must_change_password' => true,
            ]);

            app(ActiveSessionService::class)->destroyForUser(
                $user->id,
                endReason: UserSessionEndReasonEnum::PasswordReset,
                initiatedByUserId: $admin->id,
            );
        });

        Mail::to($user->email)->send(
            new AdminUserTemporaryPasswordMail($user->fresh(), $plainPassword, $admin)
        );
    }
}

<?php

namespace App\Http\Controllers\Concerns;

use App\Enums\TypeDemandeEnum;
use App\Services\Pension\PensionCodeResolver;
use Illuminate\Validation\ValidationException;

trait AuthorizesPensionDemandeType
{
    protected function authorizePensionDemandeType(TypeDemandeEnum|string $type): void
    {
        $user = auth()->user();

        if (! $user) {
            abort(403);
        }

        $typeValue = $type instanceof TypeDemandeEnum ? $type->value : $type;

        if (! $user->pensionContext()->userCanActOnDemandeType($typeValue)) {
            abort(403, 'Vous n’êtes pas autorisé à déposer cette demande pour le bénéficiaire sélectionné.');
        }
    }

    protected function guardPensionSubmit(object $request, TypeDemandeEnum $type): void
    {
        if ($request->action !== 'submit') {
            return;
        }

        $typeValue = $type->value;
        $user = auth()->user();

        if (! $user) {
            abort(403);
        }

        $context = $user->pensionContext();

        if (! $context->userCanActOnDemandeType($typeValue)) {
            abort(403, 'Vous n’êtes pas autorisé à déposer cette demande pour le bénéficiaire sélectionné.');
        }

        $code = PensionCodeResolver::fromRequest($request);

        if ($code === null) {
            $code = $context->displayPensionCode();
        }

        if ($code !== null && ! $context->userCanUsePensionCodeForDemande($code, $typeValue)) {
            throw ValidationException::withMessages([
                'code_pension' => 'Ce code pension n’est pas autorisé pour le bénéficiaire ou le mandat actif.',
            ]);
        }
    }
}

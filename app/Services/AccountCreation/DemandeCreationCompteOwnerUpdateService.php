<?php

namespace App\Services\AccountCreation;

use App\Models\DemandeCreationCompte;
use App\Models\DemandeCreationCompteHistory;
use App\Models\User;
use App\Services\Pension\BeneficiaireProvisioningService;
use App\Services\Pension\PensionMandatService;
use App\Rules\CodePension;
use App\Rules\Nif;
use App\Rules\Ninu;
use App\Rules\Telephone;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DemandeCreationCompteOwnerUpdateService
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(DemandeCreationCompte $demande, User $owner, array $validated): DemandeCreationCompte
    {
        $this->assertOwnerCanEdit($demande, $owner);

        $pensionCode = filled($validated['pension_code'] ?? null)
            ? strtoupper(trim((string) $validated['pension_code']))
            : null;

        $email = filled($validated['email'] ?? null)
            ? mb_strtolower(trim((string) $validated['email']))
            : null;

        if ($email !== '' && $email !== null) {
            $exists = User::query()
                ->where('id', '!=', $owner->id)
                ->whereRaw('LOWER(email) = ?', [$email])
                ->exists();
            if ($exists) {
                throw ValidationException::withMessages([
                    'email' => 'Cette adresse e-mail est déjà utilisée.',
                ]);
            }
        }

        DB::transaction(function () use ($demande, $owner, $validated, $pensionCode, $email) {
            $demande->update([
                'firstname' => trim((string) ($validated['firstname'] ?? $demande->firstname ?? '')) ?: $demande->firstname,
                'lastname' => trim((string) ($validated['lastname'] ?? $demande->lastname ?? '')) ?: $demande->lastname,
                'email' => $email,
                'telephone' => $validated['telephone'],
                'adresse' => $validated['adresse'],
                'nif' => filled($validated['nif'] ?? null) ? $validated['nif'] : null,
                'ninu' => filled($validated['ninu'] ?? null) ? trim((string) $validated['ninu']) : null,
                'pension_code' => $pensionCode,
            ]);

            if ($demande->is_mineur) {
                $owner->fill([
                    'email' => $email,
                    'phone' => $demande->telephone,
                ]);
            } else {
                $owner->fill([
                    'firstname' => $demande->firstname,
                    'lastname' => $demande->lastname,
                    'name' => trim(($demande->firstname ?? '').' '.($demande->lastname ?? '')) ?: $owner->name,
                    'email' => $email,
                    'phone' => $demande->telephone,
                    'nif' => $demande->nif,
                    'ninu' => $demande->ninu,
                ]);
            }
            $owner->save();

            $demande->recordHistory(
                DemandeCreationCompteHistory::EVENT_MODIFIEE,
                'Informations mises à jour par le demandeur avant validation.',
                $owner,
                DemandeCreationCompte::STATUS_EN_ATTENTE,
                ['champs' => array_keys($validated)]
            );

            $demande = $demande->fresh();

            if ($demande->is_mandataire) {
                app(PensionMandatService::class)->syncBeneficiaireFromDemandeCreationCompte($demande);
            } else {
                app(BeneficiaireProvisioningService::class)->provisionFromDemandeCreation(
                    $owner->fresh(),
                    $demande,
                    is_array($demande->ocr_documents) ? $demande->ocr_documents : [],
                    (bool) $demande->is_mineur
                );
            }
        });

        return $demande->fresh();
    }

    public function assertOwnerCanEdit(DemandeCreationCompte $demande, User $owner): void
    {
        if ((int) $demande->user_id !== (int) $owner->id) {
            abort(403);
        }

        if (! $demande->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'Cette demande ne peut plus être modifiée.',
            ]);
        }
    }

    /** @return array<string, mixed> */
    public static function validationRules(): array
    {
        return [
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'telephone' => ['required', 'string', new Telephone],
            'adresse' => 'required|string|max:500',
            'nif' => ['nullable', 'string', new Nif],
            'ninu' => ['nullable', 'string', new Ninu],
            'pension_code' => ['nullable', 'string', new CodePension],
        ];
    }
}

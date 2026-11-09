<?php

namespace App\Http\Requests\DemandeRencontre;

use App\Enums\RencontreStatutEnum;
use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Rules\Telephone;
use App\Services\RencontreMotifService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreDemandeRencontreRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        abort_unless($user, 401);

        abort_unless(
            app(RencontreMotifService::class)->userCanBookPortalRencontre($user),
            403,
            'Accès réservé aux pensionnés, mandataires et représentants légaux.'
        );

        $hasActive = Demande::query()
            ->where('created_by', $user->id)
            ->where('type', TypeDemandeEnum::DEMANDE_RENCONTRE->value)
            ->whereIn('data->rdv_statut', [
                RencontreStatutEnum::DEMANDE->value,
                RencontreStatutEnum::EN_COURS->value,
                RencontreStatutEnum::ATTRIBUE->value,
                RencontreStatutEnum::ACTIF->value,
                RencontreStatutEnum::REPORTE->value,
            ])
            ->exists();

        if ($hasActive) {
            throw ValidationException::withMessages([
                'date_souhaitee' => 'Un rendez-vous est déjà en cours. Annulez-le avant d’en prendre un autre.',
            ]);
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $user = $this->user();
        $motifKey = (string) $this->input('motif', '');
        $motifsService = app(RencontreMotifService::class);

        return array_merge([
            'motif' => $motifsService->motifValidationRuleForUser($user),

            'modalite' => [
                'required',
                'in:physique,visio',
            ],

            'date_souhaitee' => [
                'required',
                'date',
                'after_or_equal:today',
            ],

            'heure_souhaitee' => [
                'required',
                'date_format:H:i',
            ],

            'lieu_rdv' => [
                'required_if:modalite,physique',
                'nullable',
                'string',
                'max:255',
            ],

            'telephone' => [
                'nullable',
                new Telephone,
            ],

            'message' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'confirmation_lu_accepte' => [
                'required',
                'accepted',
            ],
        ], $motifsService->documentValidationRules($motifKey, $user));
    }
}

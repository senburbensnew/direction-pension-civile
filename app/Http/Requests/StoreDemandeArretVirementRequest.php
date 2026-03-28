<?php

namespace App\Http\Requests;

use App\Rules\Telephone;
use Illuminate\Foundation\Http\FormRequest;

class StoreDemandeArretVirementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title'         => 'nullable|string|max:255',
            'action'        => 'required|in:draft,submit',
            'demande_id'    => 'sometimes|nullable|exists:demandes,id',

            'date'          => ['nullable', 'required_if:action,submit', 'date'],
            'code'          => ['nullable', 'required_if:action,submit', 'string', 'max:50'],
            'nom'           => ['nullable', 'required_if:action,submit', 'string', 'max:100'],
            'prenom'        => ['nullable', 'required_if:action,submit', 'string', 'max:100'],
            'telephone'     => ['nullable', 'required_if:action,submit', new Telephone()],
            'courriel'      => ['nullable', 'email', 'max:150'],
            'mois_non_recu' => ['nullable', 'string', 'max:100'],
            'motifs'        => ['nullable', 'array'],
            'motifs.*'      => ['string', 'max:100'],
            'nouveau_numero'=> ['nullable', 'string', 'max:50'],
            'nom_du_compte' => ['nullable', 'string', 'max:150'],
            'cheques'       => ['nullable', 'string', 'max:1000'],
            'informations'  => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'required'              => 'Le champ :attribute est obligatoire.',
            'date.date'             => 'La date n\'est pas valide.',
            'courriel.email'        => 'Le courriel doit être valide.',
            'motifs.array'          => 'Les motifs doivent être une liste valide.',
        ];
    }

    public function attributes(): array
    {
        return [
            'date'          => 'date',
            'code'          => 'code',
            'nom'           => 'nom',
            'prenom'        => 'prénom',
            'telephone'     => 'téléphone',
            'courriel'      => 'courriel',
            'mois_non_recu' => 'mois non reçus',
            'motifs'        => 'motifs identifiés',
            'nouveau_numero'=> 'nouveau numéro de compte',
            'nom_du_compte' => 'nom du compte',
            'cheques'       => 'réclamation de chèques',
            'informations'  => 'informations transmises',
        ];
    }
}

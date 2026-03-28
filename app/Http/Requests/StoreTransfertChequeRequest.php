<?php

namespace App\Http\Requests;

use App\Rules\Nif;
use App\Rules\Telephone;
use App\Rules\CodePension;
use App\Rules\AnneeFiscale;
use App\Models\PensionCategory;
use Illuminate\Foundation\Http\FormRequest;

class StoreTransfertChequeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $validPensionCategories = PensionCategory::pluck('id')->toArray();

        return [
            'title'      => 'nullable|string|max:255',
            'action'     => 'required|in:draft,submit',
            'demande_id' => 'sometimes|nullable|exists:demandes,id',

            'annee_fiscale'        => ['nullable', 'required_if:action,submit', new AnneeFiscale()],
            'mois_debut'           => ['nullable', 'required_if:action,submit', 'string', 'size:7'],
            'date_demande'         => ['nullable', 'required_if:action,submit', 'date'],
            'categorie_pension_id' => ['nullable', 'required_if:action,submit', 'in:' . implode(',', $validPensionCategories)],
            'code_pension'         => ['nullable', 'required_if:action,submit', new CodePension()],
            'montant'              => ['nullable', 'numeric'],
            'nom'                  => ['nullable', 'required_if:action,submit', 'string', 'max:255'],
            'prenom'               => ['nullable', 'required_if:action,submit', 'string', 'max:255'],
            'nom_jeune_fille'      => ['nullable', 'string', 'max:255'],
            'nif'                  => ['nullable', 'required_if:action,submit', new Nif()],
            'ninu'                 => ['nullable', 'string', 'max:255'],
            'adresse'              => ['nullable', 'required_if:action,submit', 'string', 'max:255'],
            'telephone'            => ['nullable', 'required_if:action,submit', new Telephone()],
            'email'                => ['nullable', 'email', 'max:255'],
            'de'                   => ['nullable', 'required_if:action,submit', 'date'],
            'a'                    => ['nullable', 'required_if:action,submit', 'date', 'after_or_equal:de'],
            'raison_transfert'     => ['nullable', 'required_if:action,submit', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'required'         => 'Le champ :attribute est obligatoire.',
            'numeric'          => 'Le :attribute doit être un nombre valide.',
            'date'             => 'Le champ :attribute doit être une date valide.',
            'email'            => 'Veuillez fournir une adresse email valide.',
            'in'               => 'La valeur sélectionnée pour :attribute est invalide.',
            'after_or_equal'   => 'La date de fin doit être postérieure ou égale à la date de début.',
        ];
    }

    public function attributes(): array
    {
        return [
            'annee_fiscale'        => 'année fiscale',
            'mois_debut'           => 'mois de début',
            'date_demande'         => 'date de la demande',
            'categorie_pension_id' => 'régime de pension',
            'code_pension'         => 'code du pensionné',
            'montant'              => 'montant d\'allocation',
            'nom'                  => 'nom',
            'prenom'               => 'prénom',
            'nom_jeune_fille'      => 'nom de jeune fille',
            'nif'                  => 'NIF',
            'ninu'                 => 'NINU',
            'adresse'              => 'adresse',
            'telephone'            => 'téléphone',
            'email'                => 'courriel',
            'de'                   => 'début de période',
            'a'                    => 'fin de période',
            'raison_transfert'     => 'motif du transfert',
        ];
    }
}

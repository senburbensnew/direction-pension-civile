<?php

namespace App\Http\Requests;

use App\Rules\Nif;
use App\Rules\Telephone;
use Illuminate\Foundation\Http\FormRequest;

class StoreDemandeMiseAJourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $changementOui = $this->input('changement') === 'oui';
        $changements = (array) $this->input('changements', []);
        $marie = in_array($this->input('situation_matrimoniale'), ['marie', 'veuf'], true);

        $file = 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120';

        return [
            'nom' => 'required|string|max:120',
            'prenom' => 'required|string|max:120',
            'numero_pension' => 'required|string|max:50',
            'matricule' => 'nullable|string|max:50',
            'nif' => ['required', 'string', new Nif],
            'cinu' => 'nullable|string|max:40',
            'date_naissance' => 'required|date|before:today',
            'lieu_naissance' => 'required|string|max:120',

            'adresse' => 'required|string|max:255',
            'departement_commune' => 'required|string|max:120',
            'telephone' => ['required', 'string', 'max:20', new Telephone],
            'telephone_secondaire' => ['nullable', 'string', 'max:20', new Telephone],
            'email' => 'required|email|max:255',

            'situation_matrimoniale' => 'required|in:celibataire,marie,divorce,veuf,autre',
            'conjoint_nom' => ($marie ? 'required' : 'nullable').'|string|max:160',
            'conjoint_telephone' => ['nullable', 'string', 'max:20', new Telephone],

            'numero_compte' => 'required|string|max:80',
            'institution_financiere' => 'required|string|max:160',
            'lieu_paiement' => 'required|string|max:160',
            'situation_pension' => 'required|in:reguliere,difficulte,changement_bancaire,autre',

            'contact_nom' => 'required|string|max:160',
            'contact_lien' => 'required|string|max:80',
            'contact_telephone' => ['required', 'string', 'max:20', new Telephone],
            'contact_adresse' => 'required|string|max:255',

            'changement' => 'required|in:oui,non',
            'changements' => ($changementOui ? 'required' : 'nullable').'|array|min:1',
            'changements.*' => 'in:adresse,telephone,courriel,etat_civil,situation_familiale,coordonnees_bancaires,autre',
            'changement_autre' => 'nullable|required_if:changements.*,autre|string|max:200',

            'piece_identite' => ($changementOui ? 'required' : 'nullable').'|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            'acte_etat_civil' => (array_intersect($changements, ['etat_civil', 'situation_familiale']) ? 'required' : 'nullable').'|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            'justificatif_domicile' => (in_array('adresse', $changements, true) ? 'required' : 'nullable').'|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            'document_bancaire' => (in_array('coordonnees_bancaires', $changements, true) || $this->input('situation_pension') === 'changement_bancaire'
                ? 'required'
                : 'nullable').'|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            'autre_justificatif' => $file,

            'certification' => 'accepted',
            'declaration_nom' => 'required|string|max:160',
            'declaration_date' => 'required|date',
            'signature' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'certification.accepted' => 'Vous devez certifier l’exactitude des informations fournies.',
            'signature.required' => 'La signature est obligatoire.',
            'changements.required' => 'Précisez la nature du changement.',
            'piece_identite.required' => 'Joignez une copie de pièce d’identité.',
            'acte_etat_civil.required' => 'Joignez un acte ou extrait d’état civil.',
            'justificatif_domicile.required' => 'Joignez un justificatif de domicile.',
            'document_bancaire.required' => 'Joignez un document bancaire.',
        ];
    }
}

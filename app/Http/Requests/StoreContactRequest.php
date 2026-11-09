<?php

namespace App\Http\Requests;

use App\Models\Contact;
use App\Models\ContactSubject;
use App\Rules\Telephone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $subjects = ContactSubject::active()->ordered()->get();
        $customSlugs = $subjects->where('allows_custom', true)->pluck('slug')->values()->all();

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'telephone' => ['required', 'string', 'max:20', new Telephone],
            'subject' => ['required', Rule::in($subjects->pluck('slug'))],
            'custom_subject' => [
                'nullable',
                'string',
                'max:150',
                Rule::requiredIf(in_array($this->input('subject'), $customSlugs, true)),
            ],
            'message' => [
                'required',
                'string',
                'min:10',
                'max:'.Contact::MESSAGE_MAX_LENGTH,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Veuillez saisir votre prénom.',
            'last_name.required' => 'Veuillez saisir votre nom.',
            'email.required' => 'Veuillez saisir votre adresse e-mail.',
            'email.email' => 'Veuillez saisir une adresse e-mail valide.',
            'telephone.required' => 'Veuillez saisir votre numéro de téléphone.',
            'subject.required' => 'Veuillez sélectionner un sujet.',
            'subject.in' => 'Le sujet sélectionné est invalide.',
            'custom_subject.required' => 'Veuillez préciser votre sujet.',
            'message.required' => 'Veuillez saisir votre message.',
            'message.min' => 'Votre message doit contenir au moins 10 caractères.',
            'message.max' => 'Votre message ne peut pas dépasser :max caractères.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validatedForStorage(): array
    {
        $validated = $this->validated();
        $customSlugs = ContactSubject::active()->ordered()->get()
            ->where('allows_custom', true)
            ->pluck('slug')
            ->values()
            ->all();

        if (in_array($validated['subject'], $customSlugs, true)) {
            $validated['subject'] = trim($validated['custom_subject']);
        }

        unset($validated['custom_subject']);

        return $validated;
    }
}

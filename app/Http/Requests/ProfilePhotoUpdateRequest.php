<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfilePhotoUpdateRequest extends FormRequest
{
    /**
     * Autoriser l'utilisateur connecté
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Règles de validation
     */
    public function rules(): array
    {
        return [
            'profile_photo' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg',
                'max:2048', // 2MB
            ],
        ];
    }

    /**
     * Messages personnalisés (optionnel mais recommandé)
     */
    public function messages(): array
    {
        return [
            'profile_photo.required' => 'Veuillez sélectionner une image.',
            'profile_photo.image' => 'Le fichier doit être une image.',
            'profile_photo.mimes' => 'Formats autorisés : JPEG, JPG, PNG.',
            'profile_photo.max' => 'La taille maximale autorisée est de 2MB.',
        ];
    }
}
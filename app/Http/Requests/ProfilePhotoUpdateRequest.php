<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class ProfilePhotoUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'profile_photo' => [
                'bail',
                'required',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (! $value instanceof UploadedFile) {
                        $fail('Veuillez sélectionner une image.');

                        return;
                    }

                    if (! $value->isValid()) {
                        $fail(match ($value->getError()) {
                            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Le fichier est trop volumineux pour le serveur (max. 5 Mo).',
                            UPLOAD_ERR_PARTIAL => 'Le téléversement a été interrompu. Réessayez.',
                            UPLOAD_ERR_NO_FILE => 'Veuillez sélectionner une image.',
                            UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire introuvable sur le serveur.',
                            UPLOAD_ERR_CANT_WRITE => 'Impossible d’écrire le fichier sur le serveur.',
                            UPLOAD_ERR_EXTENSION => 'Une extension PHP a bloqué le téléversement.',
                            default => 'Le téléversement a échoué. Réessayez avec un JPEG ou PNG (max. 5 Mo).',
                        });
                    }
                },
                'image',
                'mimes:jpeg,png,jpg',
                'max:5120',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'profile_photo.required' => 'Veuillez sélectionner une image.',
            'profile_photo.image' => 'Le fichier doit être une image.',
            'profile_photo.mimes' => 'Formats autorisés : JPEG, JPG, PNG.',
            'profile_photo.max' => 'La taille maximale autorisée est de 5 Mo.',
            'profile_photo.uploaded' => 'Le téléversement a échoué. Vérifiez que le fichier fait au plus 5 Mo (JPEG ou PNG).',
        ];
    }
}

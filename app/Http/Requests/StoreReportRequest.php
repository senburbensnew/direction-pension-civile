<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    public function authorize()
    {
        // use policy or middleware; keep true if middleware checks role
        return auth()->check();
    }

    public function rules()
    {
        $max = 20 * 1024; // 20 MB en KB

        // Si c'est une mise à jour (PUT/PATCH) et qu'il y a un report existant, le fichier est nullable
        $fileRule = $this->isMethod('post') ? 'required|file|mimes:pdf|max:' . $max
                                        : 'nullable|file|mimes:pdf|max:' . $max;

        return [
            'title' => 'required|string|max:255',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'description' => 'nullable|string',
            'file' => $fileRule,
            'status' => 'nullable|in:draft,published'
        ];
    }
}

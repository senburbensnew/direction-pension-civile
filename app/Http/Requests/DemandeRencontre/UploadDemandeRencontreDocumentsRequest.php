<?php

namespace App\Http\Requests\DemandeRencontre;

use App\Models\Demande;
use App\Services\RencontreMotifService;
use Illuminate\Foundation\Http\FormRequest;

class UploadDemandeRencontreDocumentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Demande $demande */
        $demande = $this->route('demande');

        return app(RencontreMotifService::class)
            ->postSubmissionDocumentValidationRules($demande);
    }
}

<?php

namespace App\Services\Pension;

use App\Models\AffiliationPension;
use Illuminate\Http\Request;

class PensionCodeResolver
{
    /**
     * Extrait un code pension normalisable depuis une requête de demande.
     */
    public static function fromRequest(Request $request): ?string
    {
        $candidates = [
            $request->input('code_pension'),
            $request->input('pension_code'),
            $request->input('numero_pension'),
            $request->input('pensioner_code'),
            data_get($request->all(), 'data.code_pension'),
        ];

        foreach ($candidates as $value) {
            if (filled($value)) {
                return AffiliationPension::normalizeCode((string) $value);
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): ?string
    {
        foreach (['code_pension', 'pension_code', 'numero_pension', 'pensioner_code'] as $key) {
            if (filled($validated[$key] ?? null)) {
                return AffiliationPension::normalizeCode((string) $validated[$key]);
            }
        }

        return null;
    }
}

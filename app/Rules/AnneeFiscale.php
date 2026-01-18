<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AnneeFiscale implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Vérifie le format général YYYY/YYYY
        if (!preg_match('/^(\d{4})\/(\d{4})$/', $value, $matches)) {
            $fail("Le champ :attribute doit être au format année fiscale (ex : 2024/2025).");
            return;
        }

        $anneeDebut = (int) $matches[1];
        $anneeFin   = (int) $matches[2];

        // Vérifie que l'année de fin = année de début + 1
        if ($anneeFin !== $anneeDebut + 1) {
            $fail("L'année fiscale doit être composée de deux années consécutives (ex : 2024/2025).");
        }
    }
}

<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Telephone implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Supprimer les espaces
        $value = str_replace(' ', '', $value);

        // Regex : +509XXXXXXXX (obligatoire)
        $pattern = '/^\+509\d{8}$/';

        if (!preg_match($pattern, $value)) {
            $fail("Le champ :attribute doit être un numéro de téléphone valide au format +509XXXXXXXX (ex: +50938123456).");
        }
    }
}
<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CodePension implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        /*
         * Deux formats acceptés (casse ignorée) :
         *  - 7- suivi de 5 caractères alphanumériques (ex. 7-JM183, 7-l0366, 7-Ll080)
         *  - 8- suivi de 5 chiffres (ex. 8-18698, 8-00500)
         */
        if (! preg_match('/^(7-[A-Z0-9]{5}|8-\d{5})$/i', (string) $value)) {
            $fail(
                "Le champ :attribute doit être au format "
                . "7-XXXXX (5 caractères alphanumériques, ex. 7-JM183) "
                . "ou 8-XXXXX (5 chiffres, ex. 8-34321)."
            );
        }
    }
}
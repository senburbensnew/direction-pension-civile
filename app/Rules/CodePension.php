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
        if (!preg_match('/^[78]-\d{5}$/', $value)) {
            $fail("Le champ :attribute doit être au format 7-XXXXX ou 8-XXXXX (ex: 8-34321).");
        }
    }
}
<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Ninu implements ValidationRule
{
    /**
     * NINU = exactement 10 chiffres, sans tiret ni séparateur.
     */
    private const REGEX = '/^\d{10}$/';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! preg_match(self::REGEX, (string) $value)) {
            $fail('Le :attribute doit être un NINU valide (10 chiffres, sans tiret).');
        }
    }
}
<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Nif implements ValidationRule
{
    private const REGEX = '/^\d{3}-\d{3}-\d{3}-\d{1}$/';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!preg_match(self::REGEX, $value)) {
            $fail('Le :attribute doit être un NIF valide (format 000-000-000-0).');
        }
    }
}
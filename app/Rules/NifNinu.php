<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NifNinu implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $nifRegex  = '/^\d{3}-\d{3}-\d{3}-\d{1}$/';
        $ninuRegex = '/^\d{3}-\d{3}-\d{3}-\d{1}$/';

        if (!preg_match($nifRegex, $value) && !preg_match($ninuRegex, $value)) {
            $fail('Le :attribute doit être un NIF ou un NINU valide (ex: 123-456-789-0).');
        }
    }
}

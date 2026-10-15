<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CodePension implements ValidationRule
{
    /** @see validate() */
    public const PATTERN = '/^(7-[A-Z0-9]{5}|8-\d{5})$/i';

    public static function isValid(mixed $value): bool
    {
        if (blank($value)) {
            return false;
        }

        return (bool) preg_match(self::PATTERN, trim((string) $value));
    }

    public static function formatMessage(): string
    {
        return __('messages.pension_code_invalid_format');
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value)) {
            return;
        }

        if (! self::isValid($value)) {
            $fail(self::formatMessage());
        }
    }
}
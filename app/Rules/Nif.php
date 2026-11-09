<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Nif implements ValidationRule
{
    /** @see validate() */
    public const PATTERN = '/^\d{3}-\d{3}-\d{3}-\d{1}$/';

    public const HTML_PATTERN = '\d{3}-\d{3}-\d{3}-\d{1}';

    public static function isValid(mixed $value): bool
    {
        if (blank($value)) {
            return false;
        }

        return (bool) preg_match(self::PATTERN, (string) $value);
    }

    public static function formatMessage(): string
    {
        return __('messages.nif_invalid_format');
    }

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
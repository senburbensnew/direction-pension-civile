<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Telephone implements ValidationRule
{
    /** @see validate() — E.164-style with leading + */
    public const PATTERN = '/^\+[1-9]\d{7,14}$/';

    public static function normalize(mixed $value): string
    {
        return preg_replace('/\s+/', '', (string) $value) ?? '';
    }

    public static function isValid(mixed $value): bool
    {
        if (blank($value)) {
            return false;
        }

        return (bool) preg_match(self::PATTERN, self::normalize($value));
    }

    public static function formatMessage(): string
    {
        return __('messages.telephone_invalid_format');
    }

    /**
     * Format attendu : +[indicatif pays][numéro] (ex. +50938123456).
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail(self::formatMessage());

            return;
        }

        if (! self::isValid($value)) {
            $fail(self::formatMessage());
        }
    }
}

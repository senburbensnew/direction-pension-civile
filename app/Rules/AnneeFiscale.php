<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AnneeFiscale implements ValidationRule
{
    /** @see validate() */
    public const PATTERN = '/^(\d{4})\/(\d{4})$/';

    public static function isValid(mixed $value): bool
    {
        if (blank($value)) {
            return false;
        }

        if (! preg_match(self::PATTERN, (string) $value, $matches)) {
            return false;
        }

        $anneeDebut = (int) $matches[1];
        $anneeFin = (int) $matches[2];

        return $anneeFin === $anneeDebut + 1;
    }

    public static function invalidFormatMessage(): string
    {
        return __('messages.annee_fiscale_invalid_format');
    }

    public static function notConsecutiveMessage(): string
    {
        return __('messages.annee_fiscale_not_consecutive');
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value)) {
            return;
        }

        if (! preg_match(self::PATTERN, (string) $value, $matches)) {
            $fail(self::invalidFormatMessage());

            return;
        }

        $anneeDebut = (int) $matches[1];
        $anneeFin = (int) $matches[2];

        if ($anneeFin !== $anneeDebut + 1) {
            $fail(self::notConsecutiveMessage());
        }
    }
}

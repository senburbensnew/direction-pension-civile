<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Base64Image implements ValidationRule
{
    /** @see validate() */
    public const DATA_URI_PATTERN = '/^data:image\/(png|jpeg|jpg|gif|svg\+xml);base64,/';

    /** ~2 Mo une fois décodé (base64 gonfle la taille d’environ 33 %). */
    public const MAX_DATA_URI_LENGTH = 2_800_000;

    public static function invalidFormatMessage(): string
    {
        return __('messages.base64_image_invalid_format');
    }

    public static function invalidDataMessage(): string
    {
        return __('messages.base64_image_invalid_data');
    }

    public static function tooLargeMessage(): string
    {
        return __('messages.base64_image_too_large');
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value)) {
            return;
        }

        if (! is_string($value) || ! preg_match(self::DATA_URI_PATTERN, $value)) {
            $fail(self::invalidFormatMessage());

            return;
        }

        $base64 = explode(',', $value)[1] ?? '';
        $binary = base64_decode($base64, true);

        if ($binary === false) {
            $fail(self::invalidDataMessage());

            return;
        }

        if (strlen($value) > self::MAX_DATA_URI_LENGTH) {
            $fail(self::tooLargeMessage());
        }
    }
}

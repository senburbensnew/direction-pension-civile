<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Base64Image implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Check if it's a valid base64 string
        if (!is_string($value) || !preg_match('/^data:image\/(png|jpeg|jpg|gif|svg\+xml);base64,/', $value)) {
            $fail('The :attribute must be a valid base64 encoded image (PNG, JPEG, JPG, GIF, or SVG).');
        }

        // Get the actual base64 content
        $base64 = explode(',', $value)[1] ?? '';
        $binary = base64_decode($base64, true);
        
        // Validate base64 decoding
        if ($binary === false) {
            $fail('The :attribute contains invalid base64 data.');
        }

        // Check size (2MB = 2,097,152 bytes)
        // Base64 increases size by ~33%, so we check the string length
        if (strlen($value) > 2800000) { // ~2MB when decoded
            $fail('The :attribute must not be larger than 2MB.');
        }
    }
}
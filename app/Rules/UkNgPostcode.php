<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UkNgPostcode implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $compact = strtoupper(preg_replace('/\s+/', '', (string) $value));

        if (! preg_match('/^NG(?:1[0-6]|[1-9])\d?[A-Z]{2}$/', $compact)) {
            $fail('Enter a valid Nottingham area postcode (NG1 to NG16).');
        }
    }
}

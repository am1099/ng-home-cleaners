<?php

namespace App\Rules;

use App\Support\UkContactNormalizer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UkPhoneNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = (string) $value;

        if (str_contains($value, '@')) {
            $fail('Enter a phone number, not an email address.');

            return;
        }

        $digits = UkContactNormalizer::normalizePhone($value);

        if (! preg_match('/^0(1\d{8,9}|7\d{9})$/', $digits)) {
            $fail('Enter a valid UK phone number.');
        }
    }
}

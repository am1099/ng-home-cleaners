<?php

namespace App\Rules;

use App\Support\UkContactNormalizer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotAPhoneNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $digits = UkContactNormalizer::normalizePhone((string) $value);

        if (preg_match('/^0(1\d{8,9}|7\d{9})$/', $digits)) {
            $fail('Enter an email address, not a phone number.');
        }
    }
}

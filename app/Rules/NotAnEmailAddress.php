<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotAnEmailAddress implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filter_var((string) $value, FILTER_VALIDATE_EMAIL)) {
            $fail('Enter a phone number, not an email address.');
        }
    }
}

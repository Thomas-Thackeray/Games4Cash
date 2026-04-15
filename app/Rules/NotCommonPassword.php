<?php

namespace App\Rules;

use App\Models\BlacklistedPassword;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotCommonPassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (BlacklistedPassword::isBlacklisted((string) $value)) {
            $fail('This password is too common. Please choose a more unique password.');
        }
    }
}

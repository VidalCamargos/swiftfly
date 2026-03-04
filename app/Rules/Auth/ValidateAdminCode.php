<?php

namespace App\Rules\Auth;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateAdminCode implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== env('ADMIN_CODE')) {
            $fail('validation.custom.auth.invalid-admin-code')->translate();
        }
    }
}

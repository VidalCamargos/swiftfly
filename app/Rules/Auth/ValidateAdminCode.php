<?php

namespace App\Rules\Auth;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateAdminCode implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('app.admin_code')) {
            $fail('validation.custom.auth.invalid_admin_code')->translate();
        }
    }
}

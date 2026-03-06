<?php

namespace App\Http\Requests\Auth;

use App\Rules\Auth\ValidateAdminCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Override;

class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'max:255', 'min:3'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->max(255)->letters()->numbers()->symbols(),
            ],
            'admin_code' => ['sometimes', 'string', new ValidateAdminCode()],
        ];
    }

    #[Override]
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        if ($this->has('admin_code')) {
            $validated['is_admin'] = true;
        }

        return data_get($validated, $key, $default);
    }
}

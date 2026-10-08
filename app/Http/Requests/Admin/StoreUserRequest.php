<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email:rfc,strict', 'max:191', Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'string', Rule::exists('roles', 'name')],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}

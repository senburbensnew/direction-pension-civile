<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ValidatesAdminUserForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    use ValidatesAdminUserForm;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(
            $this->adminUserAttributeRules(),
            [
                'password' => [
                    'required',
                    'string',
                    'max:72',
                    'confirmed',
                    Password::defaults(),
                ],
            ],
        );
    }
}

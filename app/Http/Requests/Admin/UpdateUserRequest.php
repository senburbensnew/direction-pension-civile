<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ValidatesAdminUserForm;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    use ValidatesAdminUserForm;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var User $user */
        $user = $this->route('user');

        return array_merge(
            $this->adminUserAttributeRules($user),
            [
                'password' => [
                    'nullable',
                    'string',
                    'max:72',
                    'confirmed',
                    Password::defaults(),
                ],
            ],
        );
    }
}

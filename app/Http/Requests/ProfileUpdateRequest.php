<?php

namespace App\Http\Requests;

use App\Rules\Nif;
use App\Rules\Ninu;
use App\Models\User;
use App\Rules\Telephone;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */

    public function rules(): array
    {
        $user = $this->user();

        $rules = [
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],

            'nif'   => ['nullable', new Nif()],
            'phone' => ['nullable', new Telephone()],
        ];

        if (! $user->hasRole('institution')) {
            $rules['ninu'] = ['nullable', new Ninu()];
        }

        return $rules;
    }
}

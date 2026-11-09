<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Models\User;
use App\Rules\Nif;
use App\Rules\Ninu;
use App\Rules\Telephone;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

trait ValidatesAdminUserForm
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('name')) {
            return;
        }

        $composed = $this->composeDisplayName(
            $this->input('firstname'),
            $this->input('lastname'),
        );

        if ($composed !== '') {
            $this->merge(['name' => $composed]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $roles = array_values(array_unique(array_filter($this->input('roles', []))));
            $serviceId = $this->input('service_id');

            if ($roles === []) {
                return;
            }

            $requiresService = RolesAndPermissionsSeeder::rolesRequiringService();

            foreach ($roles as $role) {
                if (in_array($role, $requiresService, true) && blank($serviceId)) {
                    $validator->errors()->add(
                        'service_id',
                        'Ce rôle exige un service de rattachement.',
                    );

                    return;
                }
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function normalizedValidated(): array
    {
        $validated = $this->validated();

        if (isset($validated['phone']) && is_string($validated['phone'])) {
            $validated['phone'] = Telephone::normalize($validated['phone']);
        }

        foreach (['nif', 'ninu', 'civil_status_id', 'birth_date', 'address'] as $field) {
            if (blank($validated[$field] ?? null)) {
                $validated[$field] = null;
            }
        }

        return $validated;
    }

    /**
     * @return array<string, mixed>
     */
    protected function adminUserAttributeRules(?User $user = null): array
    {
        $categoryCodes = array_keys(config('account_categories.categories', []));
        $userId = $user?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'username' => [
                'nullable',
                'string',
                'min:3',
                'max:50',
                'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'phone' => [
                'required',
                'string',
                'max:20',
                new Telephone,
                function (string $attribute, mixed $value, \Closure $fail) use ($userId): void {
                    if (blank($value)) {
                        return;
                    }

                    $existing = User::findByTelephone((string) $value);
                    if ($existing !== null && $existing->id !== $userId) {
                        $fail('Ce numéro de téléphone est déjà utilisé.');
                    }
                },
            ],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'gender_id' => ['required', 'integer', 'exists:genders,id'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'civil_status_id' => ['nullable', 'integer', 'exists:civil_statuses,id'],
            'address' => ['nullable', 'string', 'max:500'],
            'primary_category' => ['nullable', 'string', Rule::in($categoryCodes)],
            'institution_id' => [
                Rule::requiredIf(fn () => $this->input('primary_category') === 'institution'),
                'nullable',
                'integer',
                Rule::exists('institutions', 'id'),
                Rule::unique('users', 'institution_id')->ignore($userId),
            ],
            'nif' => [
                'nullable',
                'string',
                'max:20',
                new Nif,
                function (string $attribute, mixed $value, \Closure $fail) use ($userId): void {
                    if (blank($value)) {
                        return;
                    }

                    $existing = User::findByNif((string) $value);
                    if ($existing !== null && $existing->id !== $userId) {
                        $fail('Ce NIF est déjà utilisé.');
                    }
                },
            ],
            'ninu' => [
                'nullable',
                'string',
                'max:20',
                new Ninu,
                function (string $attribute, mixed $value, \Closure $fail) use ($userId): void {
                    if (blank($value)) {
                        return;
                    }

                    $existing = User::findByNinu((string) $value);
                    if ($existing !== null && $existing->id !== $userId) {
                        $fail('Ce NINU est déjà utilisé.');
                    }
                },
            ],
        ];
    }

    private function composeDisplayName(mixed $firstname, mixed $lastname): string
    {
        return trim(implode(' ', array_filter([
            is_string($firstname) ? trim($firstname) : '',
            is_string($lastname) ? trim($lastname) : '',
        ], fn (string $part) => $part !== '')));
    }
}

<?php

namespace App\Services\Account;

use App\Models\User;

class ProfileFieldCompletionService
{
    /** @var list<string> */
    public const ALWAYS_EDITABLE = [
        'civil_status_id',
        'address',
        'phone',
    ];

    /** @var list<string> */
    private const PERSONAL_ATTRIBUTES = [
        'firstname',
        'lastname',
        'name',
        'birth_date',
        'gender_id',
        'civil_status_id',
        'address',
        'username',
    ];

    /** @var list<string> */
    private const CONTACT_ATTRIBUTES = [
        'email',
        'nif',
        'ninu',
        'phone',
    ];

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public function mergeCompletableFields(User $user, array $validated): array
    {
        $data = [];

        foreach (self::PERSONAL_ATTRIBUTES as $attribute) {
            if (! $this->canEdit($user, $attribute)) {
                continue;
            }

            if (! array_key_exists($attribute, $validated)) {
                continue;
            }

            $normalized = $this->normalizeValue($attribute, $validated[$attribute]);

            if ($this->shouldSkipEmptySubmission($attribute, $normalized)) {
                continue;
            }

            $data[$attribute] = $normalized;
        }

        foreach (self::CONTACT_ATTRIBUTES as $attribute) {
            if ($user->hasRole('institution') && $attribute === 'ninu') {
                continue;
            }

            if (! $this->canEdit($user, $attribute)) {
                continue;
            }

            if (! array_key_exists($attribute, $validated)) {
                continue;
            }

            $data[$attribute] = $this->normalizeValue($attribute, $validated[$attribute]);
        }

        if (array_key_exists('firstname', $data) || array_key_exists('lastname', $data)) {
            $firstname = (string) ($data['firstname'] ?? $user->firstname ?? '');
            $lastname = (string) ($data['lastname'] ?? $user->lastname ?? '');
            $composed = trim($firstname.' '.$lastname);

            if ($composed !== '') {
                $data['name'] = $composed;
            }
        }

        return $data;
    }

    public function canEdit(User $user, string $attribute): bool
    {
        if (in_array($attribute, self::ALWAYS_EDITABLE, true)) {
            return true;
        }

        return $this->isCompletable($user, $attribute);
    }

    public function isCompletable(User $user, string $attribute): bool
    {
        return match ($attribute) {
            'gender_id' => blank($user->gender_id),
            'birth_date' => blank($user->birth_date),
            default => blank($user->{$attribute}),
        };
    }

    private function normalizeValue(string $attribute, mixed $value): mixed
    {
        if ($attribute === 'civil_status_id' && blank($value)) {
            return null;
        }

        return $value;
    }

    private function shouldSkipEmptySubmission(string $attribute, mixed $value): bool
    {
        if ($attribute === 'gender_id') {
            return blank($value);
        }

        if ($attribute === 'civil_status_id') {
            return ! in_array($attribute, self::ALWAYS_EDITABLE, true) && blank($value);
        }

        return false;
    }
}

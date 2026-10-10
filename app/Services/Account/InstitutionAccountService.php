<?php

namespace App\Services\Account;

use App\Models\Institution;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InstitutionAccountService
{
    public function __construct(
        private readonly UserCategoryRoleSyncService $categorySync,
    ) {
    }

    /**
     * @param  array<string, mixed>  $institutionAttributes
     * @param  array<string, mixed>  $userAttributes  password en clair accepté
     */
    public function createWithLogin(array $institutionAttributes, array $userAttributes): User
    {
        return DB::transaction(function () use ($institutionAttributes, $userAttributes) {
            $institution = Institution::query()->create([
                'raison_sociale' => $institutionAttributes['raison_sociale'],
                'sigle' => $institutionAttributes['sigle'] ?? null,
                'nif' => $institutionAttributes['nif'] ?? null,
                'code' => $institutionAttributes['code'] ?? null,
                'statut' => $institutionAttributes['statut'] ?? 'actif',
                'metadata' => $institutionAttributes['metadata'] ?? null,
            ]);

            if (isset($userAttributes['password']) && ! str_starts_with((string) $userAttributes['password'], '$2y$')) {
                $userAttributes['password'] = Hash::make((string) $userAttributes['password']);
            }

            $userAttributes['institution_id'] = $institution->id;
            $userAttributes['name'] ??= $institution->raison_sociale;

            /** @var User $user */
            $user = User::query()->create($userAttributes);

            $this->categorySync->assignCategory($user, 'institution', primary: true);

            return $user->fresh(['institution']);
        });
    }

    public function ensureInstitutionLink(User $user, Institution $institution): void
    {
        if ($user->institution_id !== null && $user->institution_id !== $institution->id) {
            throw new \InvalidArgumentException('Un autre compte est déjà rattaché à cette institution.');
        }

        if (User::query()->where('institution_id', $institution->id)->where('id', '!=', $user->id)->exists()) {
            throw new \InvalidArgumentException('Cette institution possède déjà un compte login.');
        }

        $user->forceFill(['institution_id' => $institution->id])->save();
        $this->categorySync->assignCategory($user, 'institution', primary: true);
    }
}

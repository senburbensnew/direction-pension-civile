<?php

namespace App\Services\Account;

use App\Models\User;
use App\Models\UserCategory;
use App\Models\UserCategoryAssignment;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UserCategoryRoleSyncService
{
    public function assignCategory(
        User $user,
        string $code,
        bool $primary = false,
        bool $syncRoles = true,
    ): UserCategoryAssignment {
        if (! UserCategory::isConfiguredCode($code)) {
            throw new InvalidArgumentException("Catégorie de compte invalide : {$code}");
        }

        $definition = config("account_categories.categories.{$code}", []);

        UserCategory::query()->updateOrCreate(
            ['code' => $code],
            [
                'label' => $definition['label'] ?? $code,
                'description' => $definition['description'] ?? null,
                'is_system' => $definition['is_system'] ?? true,
                'capabilities' => $definition['capabilities'] ?? null,
            ]
        );

        return DB::transaction(function () use ($user, $code, $primary, $syncRoles) {
            if ($primary) {
                UserCategoryAssignment::query()
                    ->where('user_id', $user->id)
                    ->update(['is_primary' => false]);
            }

            /** @var UserCategoryAssignment $assignment */
            $assignment = UserCategoryAssignment::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'user_category_code' => $code,
                ],
                [
                    'is_primary' => $primary,
                ]
            );

            if ($syncRoles) {
                $this->syncDefaultRolesForCategory($user, $code);
            }

            return $assignment;
        });
    }

    public function syncSingleCategory(User $user, string $code, bool $syncRoles = true): void
    {
        if (! UserCategory::isConfiguredCode($code)) {
            throw new InvalidArgumentException("Catégorie de compte invalide : {$code}");
        }

        DB::transaction(function () use ($user, $code, $syncRoles) {
            UserCategoryAssignment::query()
                ->where('user_id', $user->id)
                ->where('user_category_code', '!=', $code)
                ->delete();

            $this->assignCategory($user, $code, primary: true, syncRoles: $syncRoles);
        });
    }

    /**
     * @param  list<string>  $codes
     */
    public function syncCategories(User $user, array $codes, ?string $primaryCode = null): void
    {
        $codes = array_values(array_unique(array_filter(
            $codes,
            fn (string $code) => UserCategory::isConfiguredCode($code)
        )));

        if ($codes === []) {
            return;
        }

        if (count($codes) === 1) {
            $this->syncSingleCategory($user, $codes[0]);

            return;
        }

        $primaryCode ??= $codes[0];

        if (! UserCategory::isConfiguredCode($primaryCode)) {
            $primaryCode = $codes[0];
        }

        DB::transaction(function () use ($user, $codes, $primaryCode) {
            UserCategoryAssignment::query()
                ->where('user_id', $user->id)
                ->whereNotIn('user_category_code', $codes)
                ->delete();

            foreach ($codes as $code) {
                $this->assignCategory($user, $code, $code === $primaryCode, true);
            }
        });
    }

    public function syncDefaultRolesForCategory(User $user, string $code): void
    {
        $roles = config("account_categories.categories.{$code}.default_roles", []);

        if (! is_array($roles) || $roles === []) {
            return;
        }

        $user->assignRole(...$roles);
    }
}

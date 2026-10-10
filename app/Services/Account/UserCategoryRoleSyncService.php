<?php

namespace App\Services\Account;

use App\Models\User;
use App\Models\UserCategory;
use App\Models\UserCategoryAssignment;
use Illuminate\Support\Facades\DB;

class UserCategoryRoleSyncService
{
    public function assignCategory(
        User $user,
        string $code,
        bool $primary = false,
        bool $syncRoles = true,
    ): UserCategoryAssignment {
        UserCategory::query()->firstOrCreate(
            ['code' => $code],
            [
                'label' => config("account_categories.categories.{$code}.label", $code),
                'is_system' => true,
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

    /**
     * @param  list<string>  $codes
     */
    public function syncCategories(User $user, array $codes, ?string $primaryCode = null): void
    {
        $codes = array_values(array_unique(array_filter($codes)));

        if ($codes === []) {
            return;
        }

        $primaryCode ??= $codes[0];

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

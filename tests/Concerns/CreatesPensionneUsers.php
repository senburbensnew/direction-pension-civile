<?php

namespace Tests\Concerns;

use App\Models\User;
use App\Services\Pension\BeneficiaireProvisioningService;
use Spatie\Permission\Models\Role;

trait CreatesPensionneUsers
{
    protected function createPensionneUser(array $attributes = [], ?string $pensionCode = '8-34321'): User
    {
        Role::findOrCreate('pensionne', 'web');

        $user = User::factory()->create($attributes);
        $user->assignRole('pensionne');

        if ($pensionCode !== null) {
            app(BeneficiaireProvisioningService::class)->provisionAdultSelf($user, null, $pensionCode);
        }

        return $user->fresh();
    }
}

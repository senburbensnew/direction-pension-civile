<?php

namespace Database\Seeders;

use App\Enums\AccountStatusEnum;
use App\Models\Gender;
use App\Models\User;
use App\Services\Account\UserCategoryRoleSyncService;
use Illuminate\Database\Seeder;

class DefaultUserSeeder extends Seeder
{
    public function run(): void
    {
        $gender = Gender::firstOrCreate(['name' => 'Masculin']);

        $adminUser = User::updateOrCreate(
            ['email' => 'pierrerubens.milorme@mef.gouv.ht'],
            [
                'name' => 'Pierre Rubens MILORME',
                'username' => 'pierrerubens.milorme',
                'phone' => '+50931686393',
                'lastname' => 'MILORME',
                'firstname' => 'Pierre Rubens',
                'email' => 'pierrerubens.milorme@mef.gouv.ht',
                'password' => bcrypt(config('app.default_admin_password', 'password123')),
                'must_change_password' => 1,
                'nif' => '004-521-636-4',
                'ninu' => '1148519577',
                'gender_id' => $gender->id,
                'account_status' => AccountStatusEnum::ACTIF,
            ]
        );

        $adminUser->assignRole('admin');

        app(UserCategoryRoleSyncService::class)
            ->assignCategory($adminUser, 'externe', primary: true);

        $feminineGender = Gender::firstOrCreate(['name' => 'Féminin']);

        $carlyAdmin = User::updateOrCreate(
            ['email' => 'carly.baja@mef.gouv.ht'],
            [
                'name' => 'Carly BAJA',
                'username' => 'carly.baja',
                'lastname' => 'BAJA',
                'firstname' => 'Carly',
                'email' => 'carly.baja@mef.gouv.ht',
                'password' => bcrypt(config('app.default_admin_password', 'password123')),
                'must_change_password' => 1,
                'nif' => fake()->unique()->numerify('###-###-###-#'),
                'gender_id' => $feminineGender->id,
                'account_status' => AccountStatusEnum::ACTIF,
            ]
        );

        $carlyAdmin->assignRole('admin');

        app(UserCategoryRoleSyncService::class)
            ->assignCategory($carlyAdmin, 'externe', primary: true);
    }
}

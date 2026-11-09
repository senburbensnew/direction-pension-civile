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
        $defaultPassword = bcrypt(config('app.default_admin_password', 'password123'));

        $gender = Gender::firstOrCreate(['name' => 'Masculin']);

        $adminUser = User::updateOrCreate(
            ['username' => 'pierrerubens.milorme'],
            [
                'name' => 'Pierre Rubens MILORME',
                'username' => 'pierrerubens.milorme',
                'phone' => '+50931686393',
                'lastname' => 'MILORME',
                'firstname' => 'Pierre Rubens',
                'email' => 'pierrerubens.milorme@mef.gouv.ht',
                'password' => $defaultPassword,
                'must_change_password' => true,
                'nif' => '004-521-636-4',
                'ninu' => '1148519577',
                'gender_id' => $gender->id,
                'birth_date' => '1990-07-31',
                'address' => '110 Rue Nossirel Lherisson, Jacmel, Haiti',
                'account_status' => AccountStatusEnum::ACTIF,
            ]
        );

        $adminUser->assignRole('admin');

        app(UserCategoryRoleSyncService::class)
            ->assignCategory($adminUser, 'externe', primary: true);

        $carlyAdmin = User::updateOrCreate(
            ['username' => 'carly.baja'],
            [
                'name' => 'Carly Baja',
                'username' => 'carly.baja',
                'lastname' => 'Baja',
                'firstname' => 'Carly',
                'email' => 'carly.baja@mef.gouv.ht',
                'phone' => '39340593',
                'password' => $defaultPassword,
                'must_change_password' => true,
                'gender_id' => $gender->id,
                'account_status' => AccountStatusEnum::ACTIF,
            ]
        );

        $carlyAdmin->assignRole('admin');

        app(UserCategoryRoleSyncService::class)
            ->assignCategory($carlyAdmin, 'externe', primary: true);
    }
}

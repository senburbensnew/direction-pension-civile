<?php

namespace Database\Seeders;

use App\Enums\AccountStatusEnum;
use Illuminate\Database\Seeder;
use App\Models\Gender;
use App\Models\User;

class DefaultUserSeeder extends Seeder
{
    public function run(): void
    {
        $gender = Gender::firstOrCreate(['name' => 'Masculin']);

        $adminUser = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Pierre Rubens MILORME',
                'username' => 'admin',
                'lastname' => 'MILORME',
                'firstname' => 'Pierre Rubens',
                'email' => 'admin@example.com',
                'password' => bcrypt(config('app.default_admin_password', 'password123')),
                'nif' => '123-456-789-0',
                'gender_id' => $gender->id,
                'account_status' => AccountStatusEnum::ACTIF,
            ]
        );

        $adminUser->assignRole('admin');

        app(\App\Services\Account\UserCategoryRoleSyncService::class)
            ->assignCategory($adminUser, 'externe', primary: true);
    }
}

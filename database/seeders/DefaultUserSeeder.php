<?php

namespace Database\Seeders;

use App\Enums\UserTypeEnum;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserType;

class DefaultUserSeeder extends Seeder
{
    public function run(): void
    {
        $userType = UserType::firstOrCreate([
            'name' => UserTypeEnum::FONCTIONNAIRE->value,
        ]);

        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Pierre Rubens MILORME',
                'lastname' => 'MILORME',
                'firstname' => 'Pierre Rubens',
                'email' => 'admin@example.com',
                'password' => bcrypt(config('app.default_admin_password', 'password123')),
                'nif' => '123-456-789-0',
                'user_type_id' => $userType->id,
            ]
        );
    }
}
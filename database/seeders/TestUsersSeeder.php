<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserType;

class TestUsersSeeder extends Seeder
{
    public function run()
    {
        $defaultPassword = bcrypt('password123');

        $roleUsers = [
            'pensionnaire' => [
                'name' => 'Pensionnaire',
                'email' => 'pensionnaire@example.com',
            ],
            'fonctionnaire' => [
                'name' => 'Fonctionnaire',
                'email' => 'fonctionnaire@example.com',
            ],
            'institution' => [
                'name' => 'Institution',
                'email' => 'institution@example.com',
            ]
        ];

        foreach ($roleUsers as $roleName => $data) {
            $userType = UserType::firstOrCreate(['name' => $roleName]);

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => $defaultPassword,
                    'nif' => fake()->numerify('##########'),
                    'user_type_id' => $userType->id,
                ]
            );

            $user->syncRoles([$roleName]);
        }
    }
}

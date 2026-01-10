<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserType;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run()
    {
        $defaultPassword = bcrypt('password123');

        $roleUsers = [
            'pensionnaire' => [
                'name' => 'pensionnaire',
                'email' => 'pensionnaire@example.com',
            ],
            'fonctionnaire' => [
                'name' => 'fonctionnaire',
                'email' => 'fonctionnaire@example.com',
            ],
            'institution' => [
                'name' => 'institution',
                'email' => 'institution@example.com',
            ],
        ];

        // Users with ONE role
        foreach ($roleUsers as $roleName => $data) {
            $userType = UserType::firstOrCreate(['name' => $roleName]);

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => $defaultPassword,
                    'nif' => fake()->numerify('##########'),
                    'user_type_id' => $userType->id,
                ]
            );

            $user->syncRoles($roleName);
        }

        // User with TWO roles
        $fonctionnaireType = UserType::firstOrCreate(['name' => 'fonctionnaire']);

        $multiRoleUser = User::updateOrCreate(
            ['email' => 'dagrin@example.com'],
            [
                'name' => 'Secrétaire Dagrin',
                'password' => $defaultPassword,
                'nif' => fake()->numerify('##########'),
                'user_type_id' => $fonctionnaireType->id,
            ]
        );

        $multiRoleUser->syncRoles(['fonctionnaire', 'secretariat']);
    }
}

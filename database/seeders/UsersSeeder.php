<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserType;
use App\Models\Service;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
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
            ],
        ];

        foreach ($roleUsers as $roleName => $data) {
            $userType = UserType::firstOrCreate(['name' => $roleName]);

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => $defaultPassword,
                    'nif' => fake()->unique()->numerify('###-###-###-#'),
                    'user_type_id' => $userType->id,
                ]
            );

            $user->syncRoles([$roleName]);
        }

        $fonctionnaireType = UserType::firstOrCreate(['name' => 'fonctionnaire']);

        // Direction
        $directionUser = User::updateOrCreate(
            ['email' => 'direction@example.com'],
            [
                'name' => 'Direction',
                'password' => $defaultPassword,
                'nif' => fake()->unique()->numerify('###-###-###-#'),
                'user_type_id' => $fonctionnaireType->id,
                'service_id' => Service::where('code', 'direction')->value('id'),
            ]
        );
        $directionUser->syncRoles(['fonctionnaire', 'direction']);

        $directeurUser = User::updateOrCreate(
            ['email' => 'directeur@example.com'],
            [
                'name' => 'Directeur',
                'password' => $defaultPassword,
                'nif' => fake()->unique()->numerify('###-###-###-#'),
                'user_type_id' => $fonctionnaireType->id,
                'service_id' => Service::where('code', 'direction')->value('id'),
            ]
        );
        $directeurUser->syncRoles(['fonctionnaire', 'directeur']);

        $assistantDirecteurUser = User::updateOrCreate(
            ['email' => 'assistant.directeur@example.com'],
            [
                'name' => 'Assistant Directeur',
                'password' => $defaultPassword,
                'nif' => fake()->unique()->numerify('###-###-###-#'),
                'user_type_id' => $fonctionnaireType->id,
                'service_id' => Service::where('code', 'direction')->value('id'),
            ]
        );
        $assistantDirecteurUser->syncRoles(['fonctionnaire', 'assistant_directeur']);

        // Service liquidation
        $liquidationUser = User::updateOrCreate(
            ['email' => 'liquidation@example.com'],
            [
                'name' => 'Service liquidation',
                'password' => $defaultPassword,
                'nif' => fake()->unique()->numerify('###-###-###-#'),
                'user_type_id' => $fonctionnaireType->id,
                'service_id' => Service::where('code', 'service_liquidation')->value('id'),
            ]
        );
        $liquidationUser->syncRoles(['fonctionnaire', 'service_liquidation']);


        // Service contrôle placement
        $controlePlacementUser = User::updateOrCreate(
            ['email' => 'controle.placement@example.com'],
            [
                'name' => 'Service contrôle placement',
                'password' => $defaultPassword,
                'nif' => fake()->unique()->numerify('###-###-###-#'),
                'user_type_id' => $fonctionnaireType->id,
                'service_id' => Service::where('code', 'service_controle_placement')->value('id'),
            ]
        );
        $controlePlacementUser->syncRoles(['fonctionnaire', 'service_controle_placement']);


        // Service comptabilité
        $comptabiliteUser = User::updateOrCreate(
            ['email' => 'comptabilite@example.com'],
            [
                'name' => 'Service comptabilité',
                'password' => $defaultPassword,
                'nif' => fake()->unique()->numerify('###-###-###-#'),
                'user_type_id' => $fonctionnaireType->id,
                'service_id' => Service::where('code', 'service_comptabilite')->value('id'),
            ]
        );
        $comptabiliteUser->syncRoles(['fonctionnaire', 'service_comptabilite']);


        // Accueil et Formalités
        $formaliteUser = User::updateOrCreate(
            ['email' => 'formalite@example.com'],
            [
                'name' => 'Accueil et Formalités',
                'password' => $defaultPassword,
                'nif' => fake()->unique()->numerify('###-###-###-#'),
                'user_type_id' => $fonctionnaireType->id,
                'service_id' => Service::where('code', 'service_accueil_formalites')->value('id'),
            ]
        );
        $formaliteUser->syncRoles(['fonctionnaire', 'service_accueil_formalites']);


        // Service assurance
        $assuranceUser = User::updateOrCreate(
            ['email' => 'assurance@example.com'],
            [
                'name' => 'Service assurance',
                'password' => $defaultPassword,
                'nif' => fake()->unique()->numerify('###-###-###-#'),
                'user_type_id' => $fonctionnaireType->id,
                'service_id' => Service::where('code', 'service_assurance')->value('id'),
            ]
        );
        $assuranceUser->syncRoles(['fonctionnaire', 'service_assurance']);


        $multiRoleUser = User::updateOrCreate(
            ['email' => 'secretariat@example.com'],
            [
                'name' => 'Secrétariat',
                'password' => $defaultPassword,
                'nif' => fake()->unique()->numerify('###-###-###-#'),
                'user_type_id' => $fonctionnaireType->id,
                'service_id' => Service::where('code', 'secretariat')->value('id'),
            ]
        );
        $multiRoleUser->syncRoles(['fonctionnaire', 'secretariat']);

        $dagrinUser = User::updateOrCreate(
            ['email' => 'dagrin@example.com'],
            [
                'name' => 'Secrétaire Dagrin',
                'password' => $defaultPassword,
                'nif' => fake()->unique()->numerify('###-###-###-#'),
                'user_type_id' => $fonctionnaireType->id,
                'service_id' => Service::where('code', 'secretariat')->value('id'),
            ]
        );
        $dagrinUser->syncRoles(['secretariat']);

    }
}
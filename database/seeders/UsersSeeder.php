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
            'pensionne' => [
                'name' => 'Pensionne Test',
                'email' => 'pensionne@example.com',
                'username' => 'pensionne',
                'phone' => '+50938123456',
                'pension_code' => '8-10000',
            ],
            'fonctionnaire' => [
                'name' => 'Fonctionnaire Test',
                'email' => 'fonctionnaire@example.com',
                'username' => 'fonctionnaire',
            ],
            'institution' => [
                'name' => 'Institution Test',
                'email' => 'institution@example.com',
                'username' => 'institution',
            ],
        ];

        foreach ($roleUsers as $roleName => $data) {
            $userType = UserType::firstOrCreate(['name' => $roleName]);

            $payload = [
                'name' => $data['name'],
                'password' => $defaultPassword,
                'nif' => fake()->unique()->numerify('###-###-###-#'),
                'user_type_id' => $userType->id,
            ];
            foreach (['email', 'firstname', 'lastname', 'phone', 'pension_code', 'username'] as $field) {
                if (array_key_exists($field, $data)) {
                    $payload[$field] = $data[$field];
                }
            }

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                $payload
            );

            $user->syncRoles([$roleName]);
        }

        $fonctionnaireType = UserType::firstOrCreate(['name' => 'fonctionnaire']);

        // Direction
        // $directionUser = User::updateOrCreate(
        //     ['email' => 'direction@example.com'],
        //     [
        //         'name' => 'Direction',
        //         'email' => 'direction@example.com',
        //         'username' => 'direction',
        //         'password' => $defaultPassword,
        //         'nif' => fake()->unique()->numerify('###-###-###-#'),
        //         'user_type_id' => $fonctionnaireType->id,
        //         'service_id' => Service::where('code', 'direction')->value('id'),
        //     ]
        // );
        // $directionUser->syncRoles(['fonctionnaire', 'direction', User::ROLE_VALIDATEUR_RDV]);

        $directeurUser = User::updateOrCreate(
            ['email' => 'esther.mussac@dpc.gouv.ht'],
            [
                'name' => 'Esther MUSSAC',
                'email' => 'esther.mussac@dpc.gouv.ht',
                'username' => 'esther.mussac',
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
                'email' => 'assistant.directeur@example.com',
                'username' => 'assistant.directeur',
                'password' => $defaultPassword,
                'nif' => fake()->unique()->numerify('###-###-###-#'),
                'user_type_id' => $fonctionnaireType->id,
                'service_id' => Service::where('code', 'direction')->value('id'),
            ]
        );
        $assistantDirecteurUser->syncRoles(['fonctionnaire', 'assistant_directeur']);

        // Service liquidation
        // $liquidationUser = User::updateOrCreate(
        //     ['email' => 'liquidation@example.com'],
        //     [
        //         'name' => 'Service liquidation',
        //         'email' => 'liquidation@example.com',
        //         'username' => 'liquidation',
        //         'password' => $defaultPassword,
        //         'nif' => fake()->unique()->numerify('###-###-###-#'),
        //         'user_type_id' => $fonctionnaireType->id,
        //         'service_id' => Service::where('code', 'service_liquidation')->value('id'),
        //     ]
        // );
        // $liquidationUser->syncRoles(['fonctionnaire', 'service_liquidation']);


        // Service contrôle placement
        // $controlePlacementUser = User::updateOrCreate(
        //     ['email' => 'controle.placement@example.com'],
        //     [
        //         'name' => 'Service contrôle placement',
        //         'email' => 'controle.placement@example.com',
        //         'username' => 'controle.placement',
        //         'password' => $defaultPassword,
        //         'nif' => fake()->unique()->numerify('###-###-###-#'),
        //         'user_type_id' => $fonctionnaireType->id,
        //         'service_id' => Service::where('code', 'service_controle_placement')->value('id'),
        //     ]
        // );
        // $controlePlacementUser->syncRoles(['fonctionnaire', 'service_controle_placement']);


        // Service comptabilité
        // $comptabiliteUser = User::updateOrCreate(
        //     ['email' => 'comptabilite@example.com'],
        //     [
        //         'name' => 'Service comptabilité',
        //         'email' => 'comptabilite@example.com',
        //         'username' => 'comptabilite',
        //         'password' => $defaultPassword,
        //         'nif' => fake()->unique()->numerify('###-###-###-#'),
        //         'user_type_id' => $fonctionnaireType->id,
        //         'service_id' => Service::where('code', 'service_comptabilite')->value('id'),
        //     ]
        // );
        // $comptabiliteUser->syncRoles(['fonctionnaire', 'service_comptabilite']);


        // Accueil et Formalités
        // $formaliteUser = User::updateOrCreate(
        //     ['email' => 'formalite@example.com'],
        //     [
        //         'name' => 'Accueil et Formalités',
        //         'email' => 'formalite@example.com',
        //         'username' => 'formalite',
        //         'password' => $defaultPassword,
        //         'nif' => fake()->unique()->numerify('###-###-###-#'),
        //         'user_type_id' => $fonctionnaireType->id,
        //         'service_id' => Service::where('code', 'service_accueil_formalites')->value('id'),
        //     ]
        // );
        // $formaliteUser->syncRoles(['fonctionnaire', 'service_accueil_formalites']);

        $formalitesServiceId = Service::where('code', 'service_accueil_formalites')->value('id');
        foreach ([
            [
                'email' => 'marcellus.jhonjeef@dpc.gouv.ht',
                'username' => 'marcellus.jhonjeef',
                'name' => 'Marcellus Jhon Jeef',
                'firstname' => 'Jhon Jeef',
                'lastname' => 'Marcellus',
            ],
            [
                'email' => 'daudier.mariejosee@dpc.gouv.ht',
                'username' => 'daudier.mariejosee',
                'name' => 'Daudier Marie Josee',
                'firstname' => 'Marie Josee',
                'lastname' => 'Daudier',
            ],
            [
                'email' => 'alexandre.davidson@dpc.gouv.ht',
                'username' => 'alexandre.davidson',
                'name' => 'Alexandre Davidson',
                'firstname' => 'Davidson',
                'lastname' => 'Alexandre',
            ],
        ] as $agentData) {
            $agent = User::updateOrCreate(
                ['email' => $agentData['email']],
                [
                    'name' => $agentData['name'],
                    'email' => $agentData['email'],
                    'username' => $agentData['username'],
                    'firstname' => $agentData['firstname'],
                    'lastname' => $agentData['lastname'],
                    'password' => $defaultPassword,
                    'nif' => fake()->unique()->numerify('###-###-###-#'),
                    'user_type_id' => $fonctionnaireType->id,
                    'service_id' => $formalitesServiceId,
                    'is_active' => true,
                ]
            );
            $agent->syncRoles(['fonctionnaire', User::ROLE_AGENT_FORMALITES,User::ROLE_AGENT_RDV,]);
        }

        $suffrin = User::updateOrCreate(
            ['email' => 'christina.suffrin@dpc.gouv.ht'],
            [
                'name' => 'Christina Suffrin',
                'email' => 'christina.suffrin@dpc.gouv.ht',
                'username' => 'christina.suffrin',
                'firstname' => 'Christina',
                'lastname' => 'Suffrin',
                'password' => $defaultPassword,
                'nif' => fake()->unique()->numerify('###-###-###-#'),
                'user_type_id' => $fonctionnaireType->id,
                'service_id' => Service::where('code', 'service_accueil_formalites')->value('id'),
                'is_active' => true,
            ]
        );
        $suffrin->syncRoles(['fonctionnaire', 'responsable_service_accueil_formalites', User::ROLE_VALIDATEUR_RDV]);

        // Service assurance
        // $assuranceUser = User::updateOrCreate(
        //     ['email' => 'assurance@example.com'],
        //     [
        //         'name' => 'Service assurance',
        //         'email' => 'assurance@example.com',
        //         'username' => 'assurance',
        //         'password' => $defaultPassword,
        //         'nif' => fake()->unique()->numerify('###-###-###-#'),
        //         'user_type_id' => $fonctionnaireType->id,
        //         'service_id' => Service::where('code', 'service_assurance')->value('id'),
        //     ]
        // );
        // $assuranceUser->syncRoles(['fonctionnaire', 'service_assurance']);


        // $multiRoleUser = User::updateOrCreate(
        //     ['email' => 'secretariat@example.com'],
        //     [
        //         'name' => 'Secrétariat',
        //         'email' => 'secretariat@example.com',
        //         'username' => 'secretariat',
        //         'password' => $defaultPassword,
        //         'nif' => fake()->unique()->numerify('###-###-###-#'),
        //         'user_type_id' => $fonctionnaireType->id,
        //         'service_id' => Service::where('code', 'secretariat')->value('id'),
        //     ]
        // );
        // $multiRoleUser->syncRoles(['fonctionnaire', 'secretariat']);

        // $dagrinUser = User::updateOrCreate(
        //     ['email' => 'dagrin@example.com'],
        //     [
        //         'name' => 'Secrétaire Dagrin',
        //         'email' => 'dagrin@example.com',
        //         'username' => 'dagrin',
        //         'password' => $defaultPassword,
        //         'nif' => fake()->unique()->numerify('###-###-###-#'),
        //         'user_type_id' => $fonctionnaireType->id,
        //         'service_id' => Service::where('code', 'secretariat')->value('id'),
        //     ]
        // );
        // $dagrinUser->syncRoles(['secretariat']);
    }
}
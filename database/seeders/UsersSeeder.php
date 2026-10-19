<?php

namespace Database\Seeders;

use App\Enums\AccountStatusEnum;
use App\Models\Service;
use App\Models\User;
use App\Services\Account\UserCategoryRoleSyncService;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run()
    {
        $defaultPassword = bcrypt('password123');

        $directeurUser = User::updateOrCreate(
            ['email' => 'esther.mussac@dpc.gouv.ht'],
            [
                'name' => 'Esther MUSSAC',
                'email' => 'esther.mussac@dpc.gouv.ht',
                'username' => 'esther.mussac',
                'password' => $defaultPassword,
                'nif' => fake()->unique()->numerify('###-###-###-#'),
                'service_id' => Service::where('code', 'direction')->value('id'),
                'account_status' => AccountStatusEnum::ACTIF,
            ]
        );
        $directeurUser->syncRoles(['directeur']);
        $this->syncAccountCategories($directeurUser->fresh());

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
                    'service_id' => $formalitesServiceId,
                    'account_status' => AccountStatusEnum::ACTIF,
                ]
            );
            $agent->syncRoles([User::ROLE_AGENT_FORMALITES, User::ROLE_AGENT_RDV]);
            $this->syncAccountCategories($agent->fresh());
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
                'service_id' => Service::where('code', 'service_accueil_formalites')->value('id'),
                'account_status' => AccountStatusEnum::ACTIF,
            ]
        );
        $suffrin->syncRoles(['responsable_service_accueil_formalites', User::ROLE_VALIDATEUR_RDV]);
        $this->syncAccountCategories($suffrin->fresh());
    }

    private function syncAccountCategories(User $user, ?string $portalCategory = null): void
    {
        $code = $portalCategory ?? (filled($user->service_id) ? 'personnel_dpc' : null);

        if ($code === null) {
            return;
        }

        app(UserCategoryRoleSyncService::class)->syncSingleCategory($user, $code, syncRoles: false);
    }
}

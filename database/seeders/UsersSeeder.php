<?php

namespace Database\Seeders;

use App\Enums\AccountStatusEnum;
use App\Models\Service;
use App\Models\User;
use App\Services\Account\UserCategoryRoleSyncService;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = bcrypt('password123');
        $service = $this->serviceIdsFromTable();

        $this->seedPersonnel(
            [
                'email' => 'esther.mussac@dpc.gouv.ht',
                'username' => 'esther.mussac',
                'name' => 'Esther MUSSAC',
                'firstname' => 'Esther',
                'lastname' => 'Mussac',
                'password' => $defaultPassword,
                'must_change_password' => true,
                'nif' => '091-424-597-0',
                'service_id' => $service['direction'],
            ],
            ['directeur']
        );

        foreach ([
            [
                'email' => 'marcellus.jhonjeef@dpc.gouv.ht',
                'username' => 'marcellus.jhonjeef',
                'name' => 'Marcellus Jhon Jeef',
                'firstname' => 'Jhon Jeef',
                'lastname' => 'Marcellus',
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
                    'must_change_password' => true,
                    'nif' => fake()->unique()->numerify('###-###-###-#'),
                    'service_id' => $service['service_accueil_formalites'],
                    'account_status' => AccountStatusEnum::ACTIF,
                ]
            );
            $agent->syncRoles([User::ROLE_AGENT_FORMALITES, User::ROLE_AGENT_RDV]);
            $this->syncAccountCategories($agent->fresh());
        }

        $this->seedPersonnel(
            [
                'email' => 'daudier.mariejosee@dpc.gouv.ht',
                'username' => 'daudier.mariejosee',
                'name' => 'Daudier Marie Josee',
                'firstname' => 'Marie Josee',
                'lastname' => 'Daudier',
                'password' => $defaultPassword,
                'must_change_password' => true,
                'nif' => '342-032-160-1',
                'service_id' => $service['service_accueil_formalites'],
            ],
            [User::ROLE_AGENT_FORMALITES, User::ROLE_AGENT_RDV]
        );

        $suffrin = $this->seedPersonnel(
            [
                'email' => 'christina.suffrin@mef.gouv.ht',
                'username' => 'christina.suffrin.mef.gouv.ht',
                'name' => 'Christina Suffrin',
                'firstname' => 'Christina',
                'lastname' => 'Suffrin',
                'password' => $defaultPassword,
                'must_change_password' => true,
                'nif' => '003-712-768-5',
                'ninu' => '1003610792',
                'phone' => '3451-1971',
                'gender_id' => 2,
                'service_id' => $service['service_accueil_formalites'],
            ],
            ['responsable_service_accueil_formalites', User::ROLE_VALIDATEUR_RDV]
        );

        User::query()
            ->where('email', 'christina.suffrin@dpc.gouv.ht')
            ->whereKeyNot($suffrin->id)
            ->delete();

        foreach ($this->mefPersonnelRecords($defaultPassword, $service) as $record) {
            $this->seedPersonnel($record);
        }
    }

    /**
     * @return array<string, int> Clés = code ServiceSeeder, valeurs = services.id
     */
    private function serviceIdsFromTable(): array
    {
        $codes = [
            'direction',
            'secretariat',
            'reception',
            'service_controle_placement',
            'cellule_administration',
            'service_liquidation',
            'service_comptabilite',
            'service_accueil_formalites',
        ];

        $byCode = Service::query()->whereIn('code', $codes)->pluck('id', 'code');

        $ids = [];
        foreach ($codes as $code) {
            if (! $byCode->has($code)) {
                throw new \RuntimeException("Service « {$code} » introuvable. Exécutez ServiceSeeder avant UsersSeeder.");
            }
            $ids[$code] = (int) $byCode->get($code);
        }

        return $ids;
    }

    /**
     * @param  array<string, int>  $service
     * @return list<array<string, mixed>>
     */
    private function mefPersonnelRecords(string $defaultPassword, array $service): array
    {
        $base = ['password' => $defaultPassword, 'account_status' => AccountStatusEnum::ACTIF];

        return [
            array_merge($base, [
                'email' => 'kersaint.berlin@mef.gouv.ht',
                'username' => 'kersaint.berlin.mef.gouv.ht',
                'name' => 'Berlin Kersaint',
                'firstname' => 'Berlin',
                'lastname' => 'Kersaint',
                'must_change_password' => true,
                'nif' => '005-219-974-3',
                'ninu' => '1089349092',
                'phone' => '3414-0043',
                'gender_id' => 1,
                'service_id' => $service['service_liquidation'],
            ]),
            array_merge($base, [
                'email' => 'carline.beaubrun@mef.gouv.ht',
                'username' => 'carline.beaubrun.mef.gouv.ht',
                'name' => 'Carline Beaubrun',
                'firstname' => 'Carline',
                'lastname' => 'Beaubrun',
                'must_change_password' => true,
                'nif' => '008-446-917-2',
                'ninu' => '1007478473',
                'phone' => '4826-4575',
                'gender_id' => 2,
                'service_id' => $service['service_comptabilite'],
            ]),
            array_merge($base, [
                'email' => 'dachka.ridore@mef.gouv.ht',
                'username' => 'dachka.ridore.mef.gouv.ht',
                'name' => 'Dachka Ridore',
                'firstname' => 'Dachka',
                'lastname' => 'Ridore',
                'must_change_password' => true,
                'nif' => '006-065-985-2',
                'ninu' => '1001905726',
                'phone' => '4810-0576',
                'gender_id' => 2,
                'service_id' => $service['service_liquidation'],
            ]),
            array_merge($base, [
                'email' => 'darline.alexis@mef.gouv.ht',
                'username' => 'darline.alexis.mef.gouv.ht',
                'name' => 'Darline Alexis',
                'firstname' => 'Darline',
                'lastname' => 'Alexis',
                'must_change_password' => true,
                'nif' => '003-083-651-6',
                'ninu' => '1006934982',
                'phone' => '3730-9894',
                'gender_id' => 2,
                'service_id' => $service['cellule_administration'],
            ]),
            array_merge($base, [
                'email' => 'dorothy.bernagene@mef.gouv.ht',
                'username' => 'dorothy.bernagene.mef.gouv.ht',
                'name' => 'Dorothy Bernagene',
                'firstname' => 'Dorothy',
                'lastname' => 'Bernagene',
                'must_change_password' => true,
                'nif' => '004-373-675-0',
                'ninu' => '1037600195',
                'phone' => '4083-2190',
                'gender_id' => 2,
                'service_id' => $service['secretariat'],
            ]),
            array_merge($base, [
                'email' => 'elie.pierre.stcyr@mef.gouv.ht',
                'username' => 'elie.pierre.stcyr.mef.gouv.ht',
                'name' => 'Elie Pierre St Cyr',
                'firstname' => 'Elie Pierre',
                'lastname' => 'St Cyr',
                'must_change_password' => true,
                'nif' => '003-457-867-9',
                'ninu' => '1007478667',
                'phone' => '3711-9097',
                'gender_id' => 1,
                'service_id' => $service['direction'],
            ]),
            array_merge($base, [
                'email' => 'elsie.theodore@mef.gouv.ht',
                'username' => 'elsie.theodore.mef.gouv.ht',
                'name' => 'Elsie Theodore',
                'firstname' => 'Elsie',
                'lastname' => 'Theodore',
                'must_change_password' => true,
                'nif' => '003-086-399-3',
                'ninu' => '1007142853',
                'phone' => '37473253',
                'service_id' => $service['service_accueil_formalites'],
            ]),
            array_merge($base, [
                'email' => 'etienne.elorme@mef.gouv.ht',
                'username' => 'etienne.elorme.mef.gouv.ht',
                'name' => 'Etienne Elorme',
                'firstname' => 'Etienne',
                'lastname' => 'Elorme',
                'must_change_password' => true,
                'nif' => '007-411-631-7',
                'ninu' => '1363208707',
                'phone' => '4402-7520',
                'gender_id' => 1,
                'service_id' => $service['service_comptabilite'],
            ]),
            array_merge($base, [
                'email' => 'ginou.lange@mef.gouv.ht',
                'username' => 'ginou.lange.mef.gouv.ht',
                'name' => 'Ginou Lange',
                'firstname' => 'Ginou',
                'lastname' => 'Lange',
                'must_change_password' => true,
                'nif' => '004-171-254-9',
                'ninu' => '1001211303',
                'phone' => '4142-6253',
                'gender_id' => 2,
                'service_id' => $service['service_comptabilite'],
            ]),
            array_merge($base, [
                'email' => 'lauture.hyppolite@mef.gouv.ht',
                'username' => 'lauture.hyppolite.mef.gouv.ht',
                'name' => 'Hyppolite Lauture',
                'firstname' => 'Hyppolite',
                'lastname' => 'Lauture',
                'must_change_password' => true,
                'nif' => '007-544-506-2',
                'ninu' => '1010679279',
                'phone' => '3730-5409',
                'gender_id' => 1,
                'service_id' => $service['service_controle_placement'],
            ]),
            array_merge($base, [
                'email' => 'jeanmarie.dagrin@mef.gouv.ht',
                'username' => 'jeanmarie.dagrin.mef.gouv.ht',
                'name' => 'Jean Marie Dagrin',
                'firstname' => 'Jean Marie',
                'lastname' => 'Dagrin',
                'must_change_password' => true,
                'nif' => '005-588-086-1',
                'ninu' => '1006829340',
                'phone' => '3199-5595',
                'gender_id' => 1,
                'service_id' => $service['service_liquidation'],
            ]),
            array_merge($base, [
                'email' => 'jeanremy.estriplet@mef.gouv.ht',
                'username' => 'jeanremy.estriplet.mef.gouv.ht',
                'name' => 'Jean Remy Estriplet',
                'firstname' => 'Jean Remy',
                'lastname' => 'Estriplet',
                'must_change_password' => true,
                'nif' => '002-459-734-2',
                'ninu' => '1415146969',
                'phone' => '3110-4247',
                'gender_id' => 1,
                'service_id' => $service['service_comptabilite'],
            ]),
            array_merge($base, [
                'email' => 'jeanwilken.chery@mef.gouv.ht',
                'username' => 'jeanwilken.chery.mef.gouv.ht',
                'name' => 'Jean Wilken Chery',
                'firstname' => 'Jean Wilken',
                'lastname' => 'Chery',
                'must_change_password' => true,
                'nif' => '004-309-412-6',
                'ninu' => '1006721388',
                'phone' => '4389-0599',
                'gender_id' => 1,
                'service_id' => $service['service_comptabilite'],
            ]),
            array_merge($base, [
                'email' => 'jovialie.laurent@mef.gouv.ht',
                'username' => 'jovialie.laurent.mef.gouv.ht',
                'name' => 'Jovialie Laurent',
                'firstname' => 'Jovialie',
                'lastname' => 'Laurent',
                'must_change_password' => true,
                'nif' => '008-074-317-5',
                'ninu' => '1074355608',
                'phone' => '4738-5073',
                'gender_id' => 2,
                'service_id' => $service['service_comptabilite'],
            ]),
            array_merge($base, [
                'email' => 'kerby.joseph@mef.gouv.ht',
                'username' => 'kerby.joseph.mef.gouv.ht',
                'name' => 'Kerby Joseph',
                'firstname' => 'Kerby',
                'lastname' => 'Joseph',
                'must_change_password' => true,
                'nif' => '004-673-174-0',
                'ninu' => '1468230413',
                'phone' => '3408-4816',
                'gender_id' => 2,
                'service_id' => $service['service_accueil_formalites'],
            ]),
            array_merge($base, [
                'email' => 'louisdavidson.alexandre@mef.gouv.ht',
                'username' => 'louisdavidson.alexandre.mef.gouv.ht',
                'name' => 'Louis Davidson Alexandre',
                'firstname' => 'Louis Davidson',
                'lastname' => 'Alexandre',
                'must_change_password' => true,
                'nif' => '007-544-02308',
                'ninu' => '1475882064',
                'phone' => '42697110',
                'gender_id' => 1,
                'service_id' => $service['service_accueil_formalites'],
            ]),
            array_merge($base, [
                'email' => 'marie.joel.romain@mef.gouv.ht',
                'username' => 'marie.joel.romain.mef.gouv.ht',
                'name' => 'Marie Joel Romain',
                'firstname' => 'Marie Joel',
                'lastname' => 'Romain',
                'must_change_password' => true,
                'nif' => '003-088-533-3',
                'ninu' => '1383229895',
                'phone' => '+509 3647-5450',
                'gender_id' => 1,
                'service_id' => $service['cellule_administration'],
            ]),
            array_merge($base, [
                'email' => 'morose.maurice@mef.gouv.ht',
                'username' => 'morose.maurice.mef.gouv.ht',
                'name' => 'Maurice Morose',
                'firstname' => 'Maurice',
                'lastname' => 'Morose',
                'must_change_password' => true,
                'nif' => '005-218-599-6',
                'ninu' => '1013837405',
                'phone' => '3489-3495',
                'service_id' => $service['service_comptabilite'],
            ]),
            array_merge($base, [
                'email' => 'naomie.jose@mef.gouv.ht',
                'username' => 'naomie.jose.mef.gouv.ht',
                'name' => 'Naomie José',
                'firstname' => 'Naomie',
                'lastname' => 'José',
                'must_change_password' => true,
                'nif' => '006-039-719-5',
                'ninu' => '1010161396',
                'phone' => '3830-7312',
                'gender_id' => 2,
                'service_id' => $service['secretariat'],
            ]),
            array_merge($base, [
                'email' => 'neila.clervilon@mef.gouv.ht',
                'username' => 'neila.clervilon.mef.gouv.ht',
                'name' => 'Neila Clervilon',
                'firstname' => 'Neila',
                'lastname' => 'Clervilon',
                'must_change_password' => true,
                'nif' => '005-303-443-8',
                'ninu' => '1135548446',
                'phone' => '3326-1164',
                'gender_id' => 2,
                'service_id' => $service['service_comptabilite'],
            ]),
            array_merge($base, [
                'email' => 'neyssalora.charles@mef.gouv.ht',
                'username' => 'neyssalora.charles.mef.gouv.ht',
                'name' => 'Neyssa Lora Charles',
                'firstname' => 'Neyssa Lora',
                'lastname' => 'Charles',
                'must_change_password' => true,
                'nif' => '007-414-663-1',
                'ninu' => '1231830064',
                'phone' => '3688-0947',
                'gender_id' => 2,
                'service_id' => $service['service_controle_placement'],
            ]),
            array_merge($base, [
                'email' => 'patrick.alexandre@mef.gouv.ht',
                'username' => 'patrick.alexandre.mef.gouv.ht',
                'name' => 'Patrick Alexandre',
                'firstname' => 'Patrick',
                'lastname' => 'Alexandre',
                'must_change_password' => true,
                'nif' => '003-647-7030-1',
                'phone' => '3146-9599',
                'gender_id' => 1,
                'service_id' => $service['service_comptabilite'],
            ]),
            array_merge($base, [
                'email' => 'sarafina.chevalier@mef.gouv.ht',
                'username' => 'sarafina.chevalier.mef.gouv.ht',
                'name' => 'Sarafina Chevalier',
                'firstname' => 'Sarafina',
                'lastname' => 'Chevalier',
                'must_change_password' => true,
                'nif' => '007-423-389-9',
                'ninu' => '10062822754',
                'phone' => '4756-2252',
                'gender_id' => 2,
                'service_id' => $service['service_controle_placement'],
            ]),
            array_merge($base, [
                'email' => 'stephane.laine@mef.gouv.ht',
                'username' => 'stephane.laine.mef.gouv.ht',
                'name' => 'Stéphane LAINE',
                'firstname' => 'Stephane',
                'lastname' => 'LAINE',
                'must_change_password' => true,
                'nif' => '007-544-02300',
                'ninu' => '1103102235',
                'phone' => '+50944082129',
                'service_id' => $service['service_accueil_formalites'],
            ]),
            array_merge($base, [
                'email' => 'vainqueur.belfort@mef.gouv.ht',
                'username' => 'vainqueur.belfort.mef.gouv.ht',
                'name' => 'Vainqueur Belfort',
                'firstname' => 'Vainqueur',
                'lastname' => 'Belfort',
                'must_change_password' => true,
                'nif' => '002-639-929-7',
                'ninu' => '1103102237',
                'phone' => '4456-7769',
                'service_id' => $service['service_liquidation'],
            ]),
            array_merge($base, [
                'email' => 'vanessa.tranquille@mef.gouv.ht',
                'username' => 'vanessa.tranquille.mef.gouv.ht',
                'name' => 'Vanessa Tranquille',
                'firstname' => 'Vanessa',
                'lastname' => 'Tranquille',
                'must_change_password' => true,
                'nif' => '004-330-277-0',
                'ninu' => '10069355758',
                'phone' => '3200-3664',
                'gender_id' => 2,
                'service_id' => $service['service_liquidation'],
            ]),
            array_merge($base, [
                'email' => 'wideline.daniel@mef.gouv.ht',
                'username' => 'wideline.daniel.mef.gouv.ht',
                'name' => 'Wideline Daniel',
                'firstname' => 'Wideline',
                'lastname' => 'Daniel',
                'must_change_password' => true,
                'nif' => '008-949-286-4',
                'ninu' => '1014907994',
                'phone' => '4113-1657',
                'gender_id' => 2,
                'service_id' => $service['reception'],
            ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<string>|null  $roles
     */
    private function seedPersonnel(array $data, ?array $roles = null): User
    {
        $username = $data['username'];
        unset($data['username']);

        $user = User::updateOrCreate(
            ['username' => $username],
            array_merge(['username' => $username], $data)
        );

        if ($roles !== null) {
            $user->syncRoles($roles);
        }

        $this->syncAccountCategories($user->fresh());

        return $user;
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

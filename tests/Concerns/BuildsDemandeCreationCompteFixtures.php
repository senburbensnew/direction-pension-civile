<?php

namespace Tests\Concerns;

use App\Enums\AccountStatusEnum;
use App\Enums\RencontreStatutEnum;
use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\DemandeCreationCompte;
use App\Models\PensionnaireReference;
use App\Models\User;
use App\Services\Account\UserCategoryRoleSyncService;
use App\Services\Pension\BeneficiaireProvisioningService;
use App\Services\Pension\PensionMandatService;

trait BuildsDemandeCreationCompteFixtures
{
    protected function seedPensionReference(
        string $code = '8-34321',
        string $nom = 'PIERRE',
        string $prenom = 'JEAN'
    ): void {
        PensionnaireReference::query()->updateOrCreate(
            ['PENSIONNAIRE_ID' => strtoupper(trim($code))],
            ['NOM' => $nom, 'PRENOM' => $prenom]
        );
    }

    protected function makePendingTitulaireDemande(array $attrs = []): DemandeCreationCompte
    {
        $pensionCode = $attrs['pension_code'] ?? '8-34321';
        $this->seedPensionReference($pensionCode);

        $email = $attrs['email'] ?? ('jean.pierre.'.uniqid().'@example.com');
        $nif = $attrs['nif'] ?? ('123-456-789-'.random_int(0, 9));
        $telephone = $attrs['telephone'] ?? ('+509'.random_int(10000000, 99999999));

        $user = $this->createPensionneUser([
            'email' => $email,
            'username' => 'jean.pierre.'.uniqid(),
            'nif' => $nif,
            'account_status' => AccountStatusEnum::PROVISOIRE,
        ], $pensionCode);

        unset($attrs['email'], $attrs['nif'], $attrs['telephone'], $attrs['pension_code']);

        return DemandeCreationCompte::create(array_merge([
            'code' => 'DCC-'.uniqid(),
            'user_type' => 'pensionne',
            'firstname' => 'Jean',
            'lastname' => 'Pierre',
            'email' => $email,
            'nif' => $nif,
            'pension_code' => $pensionCode,
            'telephone' => $telephone,
            'adresse' => 'Port-au-Prince',
            'status' => DemandeCreationCompte::STATUS_EN_ATTENTE,
            'user_id' => $user->id,
            'is_mineur' => false,
            'is_mandataire' => false,
        ], $attrs));
    }

    protected function makePendingMandataireDemande(): DemandeCreationCompte
    {
        $this->seedPensionReference('8-34321');

        $user = User::factory()->create([
            'username' => 'mandataire.'.uniqid(),
            'email' => 'mandataire@example.com',
            'account_status' => AccountStatusEnum::PROVISOIRE,
        ]);
        app(UserCategoryRoleSyncService::class)->assignCategory($user, 'externe', primary: true);

        $demande = DemandeCreationCompte::create([
            'code' => 'DCC-MAND-'.uniqid(),
            'user_type' => 'externe',
            'pension_code' => '8-34321',
            'telephone' => '+50938222222',
            'adresse' => 'Port-au-Prince',
            'status' => DemandeCreationCompte::STATUS_EN_ATTENTE,
            'user_id' => $user->id,
            'is_mandataire' => true,
            'is_mineur' => false,
        ]);

        app(PensionMandatService::class)->registerFromDemandeCreationCompte($user, $demande);

        return $demande->fresh();
    }

    protected function makePendingMinorDemande(): DemandeCreationCompte
    {
        $this->seedPensionReference('8-34321');

        $user = User::factory()->create([
            'username' => 'mere.rep.'.uniqid(),
            'email' => 'mere.rep@example.com',
            'account_status' => AccountStatusEnum::PROVISOIRE,
            'nif' => '123-456-789-9',
        ]);
        app(UserCategoryRoleSyncService::class)->assignCategory($user, 'externe', primary: true);

        $demande = DemandeCreationCompte::create([
            'code' => 'DCC-MIN-'.uniqid(),
            'user_type' => 'externe',
            'firstname' => 'Enfant',
            'lastname' => 'Mineur',
            'email' => 'mere.rep@example.com',
            'nif' => '987-654-321-0',
            'pension_code' => '8-34321',
            'telephone' => '+50938111111',
            'adresse' => 'Port-au-Prince',
            'status' => DemandeCreationCompte::STATUS_EN_ATTENTE,
            'user_id' => $user->id,
            'is_mineur' => true,
            'is_mandataire' => false,
            'representant_lien' => 'mere',
        ]);

        app(BeneficiaireProvisioningService::class)->provisionFromDemandeCreation(
            $user,
            $demande,
            [],
            true,
        );

        return $demande->fresh();
    }

    protected function attachRealisedFormalitesRendezVous(
        DemandeCreationCompte $demande,
        ?string $date = null,
        ?string $heure = null
    ): Demande {
        return Demande::create([
            'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => $demande->user_id,
            'data' => [
                'motif_key' => 'demande_formalites_mandats',
                'rdv_statut' => RencontreStatutEnum::REALISE->value,
                'date_souhaitee' => $date ?? now()->subDay()->toDateString(),
                'heure_souhaitee' => $heure ?? '10:00',
            ],
        ]);
    }

    protected function attachFutureRealisedRendezVous(DemandeCreationCompte $demande): Demande
    {
        return Demande::create([
            'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => $demande->user_id,
            'data' => [
                'motif_key' => 'demande_formalites_mandats',
                'rdv_statut' => RencontreStatutEnum::REALISE->value,
                'date_souhaitee' => now()->addDay()->toDateString(),
                'heure_souhaitee' => '14:00',
            ],
        ]);
    }

    protected function attachActiveRendezVous(DemandeCreationCompte $demande): Demande
    {
        return Demande::create([
            'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => $demande->user_id,
            'data' => [
                'motif_key' => 'demande_formalites_mandats',
                'rdv_statut' => RencontreStatutEnum::ACTIF->value,
                'date_souhaitee' => now()->addWeek()->toDateString(),
                'heure_souhaitee' => '10:00',
            ],
        ]);
    }

    protected function makeFormalitesReviewer(): User
    {
        $user = User::factory()->create(['username' => 'formalites.'.uniqid()]);
        $user->assignRole(User::ROLE_AGENT_FORMALITES);

        return $user;
    }
}

<?php

namespace Tests\Unit\Services;

use App\Enums\AccountStatusEnum;
use App\Models\Beneficiaire;
use App\Models\PensionMandat;
use App\Models\User;
use App\Notifications\PensionMandatSoumisAgentNotification;
use App\Notifications\PensionMandatSoumisNotification;
use App\Services\Pension\PensionMandatNotifier;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PensionMandatNotifierTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function notify_submitted_notifies_mandataire_and_reviewers(): void
    {
        Notification::fake();
        $this->seed(RolesAndPermissionsSeeder::class);

        $reviewer = User::factory()->create(['account_status' => AccountStatusEnum::ACTIF]);
        $reviewer->assignRole(User::ROLE_AGENT_FORMALITES);

        $mandataire = User::factory()->create(['account_status' => AccountStatusEnum::ACTIF]);
        $beneficiaire = Beneficiaire::create(['firstname' => 'X', 'lastname' => 'Y', 'is_mineur' => false]);

        $mandat = PensionMandat::create([
            'user_id' => $mandataire->id,
            'beneficiaire_id' => $beneficiaire->id,
            'type' => 'procuration',
            'status' => \App\Enums\PensionMandatStatusEnum::EN_ATTENTE,
        ]);

        app(PensionMandatNotifier::class)->notifySubmitted($mandat);

        Notification::assertSentTo($mandataire, PensionMandatSoumisNotification::class);
        Notification::assertSentTo($reviewer, PensionMandatSoumisAgentNotification::class);
    }
}

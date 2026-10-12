<?php

namespace Tests\Feature;

use App\Enums\RencontreStatutEnum;
use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\Holiday;
use App\Models\RdvAgentAvailability;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkflowStep;
use App\Services\Account\UserCategoryRoleSyncService;
use App\Services\HaitiHolidayService;
use App\Services\RencontreAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class RencontreBookingRulesTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        $this->seedServices();
        Storage::fake('public');
        app(HaitiHolidayService::class)->generate(now()->year);
    }

    private function makePensionne(): User
    {
        $user = User::factory()->create();
        $user->assignRole('pensionne');
        app(UserCategoryRoleSyncService::class)
            ->assignCategory($user, 'pensionne', primary: true, syncRoles: false);

        return $user;
    }

    private function makeFormalitesAgent(): User
    {
        $agent = User::factory()->create([
            'service_id' => Service::where('code', Service::FORMALITE)->value('id'),
        ]);
        $agent->assignRole(User::ROLE_AGENT_RDV);

        return $agent;
    }

    public function test_saturday_is_not_bookable_by_default(): void
    {
        $availability = app(RencontreAvailabilityService::class);
        $saturday = now()->next('Saturday')->toDateString();

        $this->assertFalse($availability->isBookableDate($saturday));
    }

    public function test_saturday_is_bookable_when_weekend_parameter_enabled(): void
    {
        DB::table('parameters')->updateOrInsert(
            ['name' => 'rdv_allow_weekends'],
            [
                'name' => 'rdv_allow_weekends',
                'description' => 'test',
                'value' => 'true',
            ]
        );
        Cache::forget('rdv_allow_weekends');

        $availability = app(RencontreAvailabilityService::class);
        $saturday = now()->next('Saturday')->toDateString();

        $this->assertTrue($availability->isBookableDate($saturday));
    }

    public function test_time_outside_config_window_is_rejected_by_default(): void
    {
        $availability = app(RencontreAvailabilityService::class);
        $date = now()->next('Wednesday')->toDateString();

        $this->assertFalse($availability->isAllowedTime('09:00'));
        $this->assertTrue($availability->isAllowedTime('14:00'));
    }

    public function test_custom_booking_window_accepts_morning_slot_when_configured(): void
    {
        DB::table('parameters')->updateOrInsert(
            ['name' => 'rdv_booking_start'],
            [
                'name' => 'rdv_booking_start',
                'description' => 'test',
                'value' => '09:00',
            ]
        );
        DB::table('parameters')->updateOrInsert(
            ['name' => 'rdv_booking_end'],
            [
                'name' => 'rdv_booking_end',
                'description' => 'test',
                'value' => '24:00',
            ]
        );
        Cache::forget('rdv_booking_start');
        Cache::forget('rdv_booking_end');

        $availability = app(RencontreAvailabilityService::class);

        $this->assertTrue($availability->isAllowedTime('09:00'));
        $this->assertTrue($availability->isAllowedTime('23:45'));
        $this->assertFalse($availability->isAllowedTime('08:45'));
    }

    public function test_haitian_holiday_date_is_not_bookable(): void
    {
        $availability = app(RencontreAvailabilityService::class);

        $holiday = Holiday::haiti()->first();

        $this->assertNotNull($holiday);
        $this->assertFalse(
            $availability->isBookableDate($holiday->date->format('Y-m-d'))
        );
    }

    public function test_agent_without_availability_window_cannot_be_assigned_outside_global_hours(): void
    {
        $service = Service::where('code', Service::FORMALITE)->firstOrFail();
        $agent = $this->makeFormalitesAgent();

        RdvAgentAvailability::create([
            'user_id' => $agent->id,
            'weekday' => now()->next('Wednesday')->dayOfWeekIso,
            'start_time' => '14:00',
            'end_time' => '14:30',
        ]);

        $availability = app(RencontreAvailabilityService::class);
        $date = now()->next('Wednesday')->toDateString();

        $this->assertTrue(
            $availability->agentIsAvailableAt($agent, $date, '14:00')
        );

        $this->assertFalse(
            $availability->agentIsAvailableAt($agent, $date, '14:30')
        );
    }

    public function test_agent_cannot_save_positive_availability_on_haitian_holiday(): void
    {
        $agent = $this->makeFormalitesAgent();
        $holiday = Holiday::haiti()->firstOrFail();

        while ($holiday->date->isWeekend()) {
            app(HaitiHolidayService::class)->generate($holiday->date->year + 1);
            $holiday = Holiday::haiti()
                ->whereDate('date', '>', now())
                ->firstOrFail();
        }

        $this->actingAs($agent)
            ->from(route('rencontres.pilotage.disponibilites.edit'))
            ->post(route('rencontres.pilotage.disponibilites.update'), [
                'dates' => [[
                    'available_on' => $holiday->date->format('Y-m-d'),
                    'start_time' => '14:00',
                    'end_time' => '16:00',
                ]],
            ])
            ->assertRedirect(route('rencontres.pilotage.disponibilites.edit'))
            ->assertSessionHasErrors('dates.0.available_on');
    }

    public function test_store_rejects_booking_on_haitian_holiday(): void
    {
        $this->makeFormalitesAgent();
        $user = $this->makePensionne();
        $holiday = Holiday::haiti()->firstOrFail();

        while ($holiday->date->isWeekend()) {
            app(HaitiHolidayService::class)->generate($holiday->date->year + 1);
            $holiday = Holiday::haiti()
                ->whereDate('date', '>', now())
                ->firstOrFail();
        }

        $this->actingAs($user)
            ->from(route('demandes.rencontre.create'))
            ->post(route('demandes.rencontre.store'), [
                'motif' => 'demande_formalites_mandats',
                'modalite' => 'visio',
                'date_souhaitee' => $holiday->date->format('Y-m-d'),
                'heure_souhaitee' => '14:00',
                'doc_carte_pension' => UploadedFile::fake()->image('carte.jpg'),
                'doc_piece_identite' => UploadedFile::fake()->image('id.jpg'),
                'confirmation_lu_accepte' => '1',
            ])
            ->assertRedirect(route('demandes.rencontre.create'))
            ->assertSessionHasErrors('date_souhaitee');
    }

    public function test_rotation_pool_limits_eligible_agents_by_weekday(): void
    {
        $service = Service::where('code', Service::FORMALITE)->firstOrFail();
        $agentA = $this->makeFormalitesAgent();
        $agentB = $this->makeFormalitesAgent();
        $date = now()->next('Wednesday')->toDateString();
        $weekday = (int) \Carbon\Carbon::parse($date)->dayOfWeekIso;

        config([
            'rdv.rotation.enabled' => true,
            'rdv.rotation.weekday_agent_ids' => [
                $service->code => [
                    $weekday => [$agentA->id],
                ],
            ],
        ]);

        $availability = app(RencontreAvailabilityService::class);

        $this->assertTrue(
            $availability->agentInRotationPool($agentA, $service, $date)
        );
        $this->assertFalse(
            $availability->agentInRotationPool($agentB, $service, $date)
        );

        $pool = $availability->agentsForAssignment($service, $date);

        $this->assertTrue($pool->contains('id', $agentA->id));
        $this->assertFalse($pool->contains('id', $agentB->id));
    }

    public function test_find_available_agent_prefers_lower_weekly_load(): void
    {
        $this->seedStatuses();
        $service = Service::where('code', Service::FORMALITE)->firstOrFail();
        $agentA = $this->makeFormalitesAgent();
        $agentB = $this->makeFormalitesAgent();
        $date = now()->next('Wednesday')->toDateString();

        config([
            'rdv.rotation.enabled' => true,
            'rdv.rotation.use_weekly_load' => true,
        ]);

        $other = $this->makePensionne();

        $this->createRencontreDemandeHelper($other, $agentA, $date, '14:00');

        $availability = app(RencontreAvailabilityService::class);

        $picked = $availability->findAvailableAgent(
            $date,
            '14:15',
            $service
        );

        $this->assertNotNull($picked);
        $this->assertSame($agentB->id, $picked->id);
    }

    private function createRencontreDemandeHelper(
        User $owner,
        User $agent,
        string $date,
        string $time
    ): Demande {
        return Demande::create([
            'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => $owner->id,
            'current_step_id' => WorkflowStep::idForCode('SOUMISE'),
            'data' => [
                'motif_key' => 'demande_formalites_mandats',
                'date_souhaitee' => $date,
                'heure_souhaitee' => $time,
                'agent_id' => $agent->id,
                'rdv_statut' => RencontreStatutEnum::ACTIF->value,
                'modalite' => 'visio',
            ],
        ]);
    }

    private function seedStatuses(): void
    {
        $this->seed(\Database\Seeders\WorkflowStepSeeder::class);
    }
}

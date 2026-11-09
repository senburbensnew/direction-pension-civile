<?php

namespace Tests\Unit\Services;

use App\Enums\RencontreStatutEnum;
use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\User;
use App\Services\RencontreVisioService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RencontreVisioServiceTest extends TestCase
{
    use RefreshDatabase;

    private RencontreVisioService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RencontreVisioService;
    }

    /** @test */
    public function attach_to_generates_token_for_visio_demande(): void
    {
        $demande = Demande::create([
            'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => User::factory()->create()->id,
            'data' => ['modalite' => 'visio'],
        ]);

        $updated = $this->service->attachTo($demande);

        $this->assertNotNull($updated->visio_token);
        $this->assertStringContainsString($updated->visio_token, (string) ($updated->data['visio_url'] ?? ''));
    }

    /** @test */
    public function attach_to_skips_non_visio_modalities(): void
    {
        $demande = Demande::create([
            'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => User::factory()->create()->id,
            'data' => ['modalite' => 'presentiel'],
        ]);

        $this->service->attachTo($demande);

        $this->assertNull($demande->fresh()->visio_token);
    }

    /** @test */
    public function status_reflects_visio_window_relative_to_slot(): void
    {
        Carbon::setTestNow('2030-06-05 13:40:00');

        $demande = $this->visioDemande('2030-06-05', '14:00');
        $demande->update(['visio_token' => 'visio-test-token']);

        $this->assertSame('en_attente', $this->service->status($demande->fresh()));

        Carbon::setTestNow('2030-06-05 14:05:00');
        $demande = $demande->fresh();
        $this->assertSame('actif', $this->service->status($demande));
        $this->assertTrue($this->service->canJoinVisio($demande));

        Carbon::setTestNow('2030-06-05 14:20:00');
        $this->assertSame('termine', $this->service->status($demande->fresh()));

        Carbon::setTestNow();
    }

    /** @test */
    public function can_access_allows_demande_owner_during_active_visio(): void
    {
        Carbon::setTestNow('2030-06-05 14:05:00');

        $owner = User::factory()->create();
        $demande = $this->visioDemande('2030-06-05', '14:00', $owner->id);
        $demande->update([
            'visio_token' => 'abc123',
            'data' => array_merge($demande->data, [
                'rdv_statut' => RencontreStatutEnum::ACTIF->value,
            ]),
        ]);

        $demande = $demande->fresh();
        $stranger = User::factory()->create();

        $this->assertTrue($this->service->canAccess($owner, $demande));
        $this->assertFalse($this->service->canAccess($stranger, $demande));

        Carbon::setTestNow();
    }

    private function visioDemande(string $date, string $time, ?int $ownerId = null): Demande
    {
        return Demande::create([
            'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => $ownerId ?? User::factory()->create()->id,
            'data' => [
                'modalite' => 'visio',
                'date_souhaitee' => $date,
                'heure_souhaitee' => $time,
            ],
        ]);
    }
}

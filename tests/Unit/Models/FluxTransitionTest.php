<?php

namespace Tests\Unit\Models;

use App\Enums\TypeDemandeEnum;
use App\Models\FluxTransition;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class FluxTransitionTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedServices();
    }

    private function direction(): Service
    {
        return Service::where('code', Service::DIRECTION)->first();
    }

    private function liquidation(): Service
    {
        return Service::where('code', Service::LIQUIDATION)->first();
    }

    private function secretariat(): Service
    {
        return Service::where('code', Service::SECRETARIAT)->first();
    }

    private function createTransition(Service $from, Service $to, ?string $type = null): FluxTransition
    {
        return FluxTransition::create([
            'service_source_id'      => $from->id,
            'service_destination_id' => $to->id,
            'action'                 => 'transfer',
            'type_demande'           => $type,
            'ordre'                  => 1,
        ]);
    }

    // ─── allowed() ───────────────────────────────────────────────────────────

    /** @test */
    public function allowed_returns_true_for_existing_null_type_transition(): void
    {
        $this->createTransition($this->direction(), $this->liquidation());

        $this->assertTrue(FluxTransition::allowed(
            $this->direction()->id,
            $this->liquidation()->id
        ));
    }

    /** @test */
    public function allowed_returns_false_when_no_matching_transition(): void
    {
        $this->assertFalse(FluxTransition::allowed(
            $this->direction()->id,
            $this->liquidation()->id
        ));
    }

    /** @test */
    public function allowed_returns_true_for_specific_type_demande_transition(): void
    {
        $this->createTransition(
            $this->direction(),
            $this->liquidation(),
            TypeDemandeEnum::DEMANDE_PENSION->value
        );

        $this->assertTrue(FluxTransition::allowed(
            $this->direction()->id,
            $this->liquidation()->id,
            TypeDemandeEnum::DEMANDE_PENSION->value
        ));
    }

    /** @test */
    public function allowed_returns_false_for_wrong_type_demande(): void
    {
        $this->createTransition(
            $this->direction(),
            $this->liquidation(),
            TypeDemandeEnum::DEMANDE_PENSION->value
        );

        // Only DEMANDE_PENSION transition exists; DEMANDE_ATTESTATION should not match
        $this->assertFalse(FluxTransition::allowed(
            $this->direction()->id,
            $this->liquidation()->id,
            TypeDemandeEnum::DEMANDE_ATTESTATION->value
        ));
    }

    /** @test */
    public function generic_null_type_transition_matches_any_type_query(): void
    {
        // A transition with null type_demande acts as a wildcard
        $this->createTransition($this->direction(), $this->liquidation(), null);

        $this->assertTrue(FluxTransition::allowed(
            $this->direction()->id,
            $this->liquidation()->id,
            TypeDemandeEnum::DEMANDE_ATTESTATION->value
        ));
    }

    // ─── destinationsFor() ───────────────────────────────────────────────────

    /** @test */
    public function destinationsFor_returns_services_reachable_from_source(): void
    {
        $this->createTransition($this->direction(), $this->liquidation());
        $this->createTransition($this->direction(), $this->secretariat());

        $destinations = FluxTransition::destinationsFor($this->direction()->id);

        $this->assertCount(2, $destinations);
        $this->assertTrue($destinations->contains('code', Service::LIQUIDATION));
        $this->assertTrue($destinations->contains('code', Service::SECRETARIAT));
    }

    /** @test */
    public function destinationsFor_returns_empty_when_no_transitions(): void
    {
        $destinations = FluxTransition::destinationsFor($this->direction()->id);

        $this->assertCount(0, $destinations);
    }

    /** @test */
    public function destinationsFor_filters_by_type_demande(): void
    {
        $this->createTransition($this->direction(), $this->liquidation(), TypeDemandeEnum::DEMANDE_PENSION->value);
        $this->createTransition($this->direction(), $this->secretariat(), TypeDemandeEnum::DEMANDE_ATTESTATION->value);

        $destinations = FluxTransition::destinationsFor(
            $this->direction()->id,
            TypeDemandeEnum::DEMANDE_PENSION->value
        );

        $this->assertCount(1, $destinations);
        $this->assertEquals(Service::LIQUIDATION, $destinations->first()->code);
    }

    // ─── typeDemandeLabelAttribute ───────────────────────────────────────────

    /** @test */
    public function typeDemandeLabelAttribute_returns_label_when_type_set(): void
    {
        $transition = $this->createTransition(
            $this->direction(),
            $this->liquidation(),
            TypeDemandeEnum::DEMANDE_PENSION->value
        );

        $this->assertEquals(TypeDemandeEnum::DEMANDE_PENSION->label(), $transition->typeDemandeLabelAttribute());
    }

    /** @test */
    public function typeDemandeLabelAttribute_returns_null_when_type_not_set(): void
    {
        $transition = $this->createTransition($this->direction(), $this->liquidation(), null);

        $this->assertNull($transition->typeDemandeLabelAttribute());
    }
}

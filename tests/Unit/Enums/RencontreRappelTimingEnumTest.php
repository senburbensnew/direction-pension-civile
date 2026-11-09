<?php

namespace Tests\Unit\Enums;

use App\Enums\RencontreRappelTimingEnum;
use Tests\TestCase;

class RencontreRappelTimingEnumTest extends TestCase
{
    /** @test */
    public function lead_minutes_and_data_keys_match_each_case(): void
    {
        $this->assertSame(24 * 60, RencontreRappelTimingEnum::TwentyFourHours->leadMinutes());
        $this->assertSame('rappel_24h_envoye_at', RencontreRappelTimingEnum::TwentyFourHours->sentAtDataKey());

        $this->assertSame(30, RencontreRappelTimingEnum::ThirtyMinutes->leadMinutes());
        $this->assertSame('rappel_30m_envoye_at', RencontreRappelTimingEnum::ThirtyMinutes->sentAtDataKey());
    }

    /** @test */
    public function labels_are_non_empty_for_usager_and_agent(): void
    {
        foreach (RencontreRappelTimingEnum::cases() as $case) {
            $this->assertNotEmpty($case->labelForUsager(), $case->value);
            $this->assertNotEmpty($case->labelForAgent(), $case->value);
        }
    }

    /** @test */
    public function try_from_round_trips_stored_values(): void
    {
        foreach (RencontreRappelTimingEnum::cases() as $case) {
            $this->assertSame($case, RencontreRappelTimingEnum::tryFrom($case->value));
        }
    }
}

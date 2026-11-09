<?php

namespace Tests\Unit\Services;

use App\Models\Holiday;
use App\Services\RencontreAvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RencontreAvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    private RencontreAvailabilityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(RencontreAvailabilityService::class);
    }

    /** @test */
    public function it_exposes_default_booking_configuration(): void
    {
        $this->assertSame('14:00', RencontreAvailabilityService::DEFAULT_START_TIME);
        $this->assertGreaterThanOrEqual(1, $this->service->dailyCapacity());
        $this->assertGreaterThanOrEqual(1, $this->service->slotMinutes());
    }

    /** @test */
    public function is_bookable_date_rejects_holidays_and_past_dates(): void
    {
        Holiday::query()->create([
            'date' => '2030-01-01',
            'name' => 'Test',
            'country' => 'HT',
            'is_recurring' => false,
            'is_official' => true,
        ]);

        $this->assertFalse($this->service->isBookableDate('2030-01-01'));
        $this->assertFalse($this->service->isBookableDate('2000-01-05'));
    }

    /** @test */
    public function allowed_times_aligns_on_slot_minutes(): void
    {
        Carbon::setTestNow('2030-06-03 10:00:00');

        $times = $this->service->allowedTimes();

        $this->assertNotEmpty($times);
        $this->assertSame('14:00', $times[0]);
    }
}

<?php

namespace Tests\Unit\Services;

use App\Models\Holiday;
use App\Services\HaitiHolidayService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HaitiHolidayServiceTest extends TestCase
{
    use RefreshDatabase;

    private HaitiHolidayService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new HaitiHolidayService;
    }

    /** @test */
    public function calculate_includes_fixed_independence_day_and_mobile_easter_holidays(): void
    {
        $holidays = $this->service->calculate(2024);

        $this->assertGreaterThanOrEqual(16, $holidays->count());

        $names = $holidays->pluck('name')->all();
        $this->assertContains('Jour de l’An / Indépendance', $names);
        $this->assertContains('Vendredi saint', $names);
        $this->assertContains('Mardi gras', $names);

        $independence = $holidays->first(
            fn (array $row) => $row['name'] === 'Jour de l’An / Indépendance'
        );

        $this->assertSame('2024-01-01', $independence['date']->toDateString());
    }

    /** @test */
    public function generate_persists_holidays_for_year(): void
    {
        $this->service->generate(2025);

        $this->assertTrue(
            Holiday::haiti()->whereDate('date', '2025-01-01')->exists()
        );
    }

    /** @test */
    public function is_holiday_reflects_database_rows(): void
    {
        Holiday::query()->create([
            'date' => '2026-07-14',
            'name' => 'Test',
            'country' => 'HT',
            'is_recurring' => false,
            'is_official' => true,
        ]);

        $this->assertTrue($this->service->isHoliday(Carbon::parse('2026-07-14')));
        $this->assertFalse($this->service->isHoliday('2026-07-15'));
    }
}

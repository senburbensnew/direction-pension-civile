<?php

namespace Tests\Unit\Services\Account;

use App\Models\User;
use App\Services\Account\ProfileFieldCompletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class ProfileFieldCompletionServiceTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRequiredData;

    private ProfileFieldCompletionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        $this->service = new ProfileFieldCompletionService;
    }

    /** @test */
    public function always_editable_fields_remain_editable_when_already_filled(): void
    {
        $user = User::factory()->create([
            'phone' => '+50938123456',
            'address' => 'Port-au-Prince',
        ]);

        $this->assertTrue($this->service->canEdit($user, 'phone'));
        $this->assertTrue($this->service->canEdit($user, 'address'));
    }

    /** @test */
    public function locked_fields_are_not_merged_when_already_set(): void
    {
        $user = User::factory()->create([
            'firstname' => 'Jean',
            'email' => 'jean@example.com',
        ]);

        $merged = $this->service->mergeCompletableFields($user, [
            'firstname' => 'Pierre',
            'email' => 'pierre@example.com',
            'phone' => '+50930000000',
        ]);

        $this->assertArrayNotHasKey('firstname', $merged);
        $this->assertArrayNotHasKey('email', $merged);
        $this->assertSame('+50930000000', $merged['phone'] ?? null);
    }

    /** @test */
    public function merge_updates_name_when_firstname_or_lastname_completes(): void
    {
        $user = User::factory()->create([
            'firstname' => '',
            'lastname' => 'Dupont',
            'name' => 'Dupont',
        ]);
        $user->forceFill(['firstname' => null])->save();
        $user->refresh();

        $merged = $this->service->mergeCompletableFields($user, [
            'firstname' => 'Marie',
        ]);

        $this->assertSame('Marie', $merged['firstname']);
        $this->assertSame('Marie Dupont', $merged['name']);
    }
}

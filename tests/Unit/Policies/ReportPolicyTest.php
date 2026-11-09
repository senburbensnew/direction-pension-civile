<?php

namespace Tests\Unit\Policies;

use App\Models\Report;
use App\Models\User;
use App\Policies\ReportPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class ReportPolicyTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRequiredData;

    private ReportPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        $this->policy = new ReportPolicy;
    }

    /** @test */
    public function view_any_allows_admin_and_institution(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $institution = User::factory()->create();
        $institution->assignRole('institution');

        $other = User::factory()->create();

        $this->assertTrue($this->policy->viewAny($admin));
        $this->assertTrue($this->policy->viewAny($institution));
        $this->assertFalse($this->policy->viewAny($other));
    }

    /** @test */
    public function published_reports_are_publicly_viewable(): void
    {
        $viewer = User::factory()->create();
        $report = Report::factory()->create([
            'status' => 'published',
            'created_by' => User::factory()->create()->id,
        ]);

        $this->assertTrue($this->policy->view($viewer, $report));
    }

    /** @test */
    public function draft_reports_visible_to_author_or_admin_only(): void
    {
        $author = User::factory()->create();
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $stranger = User::factory()->create();

        $report = Report::factory()->create([
            'status' => 'draft',
            'created_by' => $author->id,
        ]);

        $this->assertTrue($this->policy->view($author, $report));
        $this->assertTrue($this->policy->view($admin, $report));
        $this->assertFalse($this->policy->view($stranger, $report));
    }

    /** @test */
    public function delete_and_publish_are_admin_only(): void
    {
        $author = User::factory()->create();
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $report = Report::factory()->draft()->create(['created_by' => $author->id]);

        $this->assertTrue($this->policy->delete($admin, $report));
        $this->assertFalse($this->policy->delete($author, $report));
        $this->assertTrue($this->policy->publish($admin, $report));
        $this->assertFalse($this->policy->publish($author, $report));
    }
}
